<?php
/**
 * Common library of functions used by Foursquare Services.
 *
 * @package foursquare_api
 */

/**
 * Tests if the system admin has enabled Sign-On-With-Foursquare
 *
 * @param void
 * @return bool
 */
function foursquare_api_allow_sign_on_with_foursquare() {
	if (!$consumer_key = elgg_get_plugin_setting('consumer_key', 'foursquare_api')) {
		return FALSE;
	}

	if (!$consumer_secret = elgg_get_plugin_setting('consumer_secret', 'foursquare_api')) {
		return FALSE;
	}

	return elgg_get_plugin_setting('sign_on', 'foursquare_api') == 'yes';
}

/**
 * Forwards
 *
 * @todo what is this?
 */
function foursquare_api_forward() {
	// sanity check
	if (!foursquare_api_allow_sign_on_with_foursquare()) {
		forward();
	}

	$request_link = foursquare_api_get_authorize_url();
	forward($request_link, 'foursquare_api');
}

/**
 * Log in a user with foursquare.
 */
function foursquare_api_login($token) {

	// sanity check
	if (!foursquare_api_allow_sign_on_with_foursquare()) {
		forward();
	}

	if(!$token->access_token) {         
		register_error(elgg_echo('foursquare_api:login:error'));
		forward();
	}

	elgg_load_library('EpiFoursquare');
	elgg_load_library('EpiCurl');

	$consumer_key = elgg_get_plugin_setting('consumer_key', 'foursquare_api');
	$consumer_secret = elgg_get_plugin_setting('consumer_secret', 'foursquare_api');

	$fsObj = new EpiFoursquare($clientId, $clientSecret, $token->access_token);
	$details = $fsObj->get('/users/self');


	// attempt to find user and log them in.
	// else, create a new user.
	$options = array(
		'type' => 'user',
		'plugin_user_setting_name_value_pairs' => array(
			'access_key' => $details->response->user->id,
			'access_secret' => $token->access_token,
		),
		'plugin_user_setting_name_value_pairs_operator' => 'OR',
		'limit' => 0
	);
	
	$users = elgg_get_entities_from_plugin_user_settings($options);

	if ($users) {
		if (count($users) == 1 && login($users[0])) {
			system_message(elgg_echo('foursquare_api:login:success'));
			//crude hack to allow login with foursquare
			elgg_set_plugin_user_setting('access_secret', $token->access_token, $users[0]->guid);

		} else {
			system_message(elgg_echo('foursquare_api:login:error'));
		}

		forward();
	} else {
		// need foursquare account credentials
		elgg_load_library('EpiFoursquare');
		elgg_load_library('EpiCurl');

		$consumer_key = elgg_get_plugin_setting('consumer_key', 'foursquare_api');
		$consumer_secret = elgg_get_plugin_setting('consumer_secret', 'foursquare_api');

		$fsObj = new EpiFoursquare($clientId, $clientSecret, $token->access_token);

	        $user = FALSE;
		$details = $fsObj->get('/users/self');

		// create new user
		if (!$user) {
			// check new registration allowed
			if (!foursquare_api_allow_new_users_with_foursquare()) {
				register_error(elgg_echo('registerdisabled'));
				forward();
			}

			// Elgg-ify foursquare credentials
			$username = $details->response->user->firstName;
			while (get_user_by_username($username)) {
				$username = $details->response->user->firstName . '_' . rand(1000, 9999);
			}

			$password = generate_random_cleartext_password();
			$name = $details->response->user->firstName;

			$user = new ElggUser();
			$user->username = $username;
			$user->name = $name;
			$user->access_id = ACCESS_PUBLIC;
			$user->salt = generate_random_cleartext_password();
			$user->password = generate_user_password($user, $password);
			$user->owner_guid = 0;
			$user->container_guid = 0;

			if (!$user->save()) {
				register_error(elgg_echo('registerbad'));
				forward();
			}

			// @todo require email address?

			$site_name = elgg_get_site_entity()->name;
			system_message(elgg_echo('foursquare_api:login:email', array($site_name)));

			$forward = "settings/user/{$user->username}";
		}

		// set foursquare services tokens
		elgg_set_plugin_user_setting('foursquare_name', $details->response->user->firstName, $user->guid);
		elgg_set_plugin_user_setting('access_key', $details->response->user->id, $user->guid);
		elgg_set_plugin_user_setting('access_secret', $token->access_token, $user->guid);

		// pull in foursquare icon
		foursquare_api_update_user_avatar($user, $details->response->user->photo);

		// login new user
		if (login($user)) {

			system_message(elgg_echo('foursquare_api:login:success'));

		} else {

			system_message(elgg_echo('foursquare_api:login:error'));
		}

		forward($forward, 'foursquare_api');
	}

	// register login error
	register_error(elgg_echo('foursquare_api:login:error'));
	forward();
}

/**
 * Pull in the latest avatar from foursquare.
 *
 * @param unknown_type $user
 * @param unknown_type $file_location
 */
function foursquare_api_update_user_avatar($user, $file_location) {
	// @todo Should probably check that it's an image file.
	//$file_location = str_replace('_normal.jpg', '.jpg', $file_location);

	$sizes = array(
		'topbar' => array(16, 16, TRUE),
		'tiny' => array(25, 25, TRUE),
		'small' => array(40, 40, TRUE),
		'medium' => array(100, 100, TRUE),
		'large' => array(200, 200, FALSE),
		'master' => array(550, 550, FALSE),
	);

	$filehandler = new ElggFile();
	$filehandler->owner_guid = $user->getGUID();
	foreach ($sizes as $size => $dimensions) {
		$image = get_resized_image_from_existing_file(
			$file_location,
			$dimensions[0],
			$dimensions[1],
			$dimensions[2]
		);

		$filehandler->setFilename("profile/$user->guid$size.jpg");
		$filehandler->open('write');
		$filehandler->write($image);
		$filehandler->close();
	}
	
	// update user's icontime
	$user->icontime = time();

	return TRUE;
}

/**
 * User-initiated foursquare authorization
 *
 * Callback action from foursquare registration. Registers a single Elgg user with
 * the authorization tokens. Will revoke access from previous users when a
 * conflict exists.
 *
 * Depends upon {@link foursquare_api_get_authorize_url} being called previously
 * to establish request tokens.
 */
function foursquare_api_authorize() {
	$token = foursquare_api_get_access_token($_GET['code']);
        if(!isloggedin()){
		foursquare_api_login($token);
        }
	if(!$token->access_token) {         
		register_error(elgg_echo('foursquare_api:authorize:error'));
		forward('settings/plugins', 'foursquare_api');
	}

	elgg_load_library('EpiFoursquare');
	elgg_load_library('EpiCurl');

	$consumer_key = elgg_get_plugin_setting('consumer_key', 'foursquare_api');
	$consumer_secret = elgg_get_plugin_setting('consumer_secret', 'foursquare_api');

	$fsObj = new EpiFoursquare($clientId, $clientSecret, $token->access_token);
	$details = $fsObj->get('/users/self');

	// make sure no other users are registered to this foursquare account.
	$options = array(
		'type' => 'user',
		'plugin_user_setting_name_value_pairs' => array(
			'access_key' => $details->response->id,
			'access_secret' => $token->access_token,
		),
		'limit' => 0
	);

	$users = elgg_get_entities_from_plugin_user_settings($options);

	if ($users) {
		foreach ($users as $user) {
			// revoke access
			elgg_unset_plugin_user_setting('foursquare_name', $user->getGUID());
			elgg_unset_plugin_user_setting('access_key', $user->getGUID());
			elgg_unset_plugin_user_setting('access_secret', $user->getGUID());
		}
	}

	// register user's access tokens
	elgg_set_plugin_user_setting('foursquare_name', $details->response->user->firstName);
	elgg_set_plugin_user_setting('access_key', $details->response->user->id);
	elgg_set_plugin_user_setting('access_secret', $token->access_token);
	
	system_message(elgg_echo('foursquare_api:authorize:success'));
	forward('settings/plugins', 'foursquare_api');
}

/**
 * Remove foursquare access for the currently logged in user.
 */
function foursquare_api_revoke() {
	// unregister user's access tokens
	elgg_unset_plugin_user_setting('foursquare_name');
	elgg_unset_plugin_user_setting('access_key');
	elgg_unset_plugin_user_setting('access_secret');

	system_message(elgg_echo('foursquare_api:revoke:success'));
	forward('settings/plugins', 'foursquare_api');
}

/**
 * Returns the url to authorize a user.
 *
 * @param string $callback The callback URL?
 */
function foursquare_api_get_authorize_url() {
	global $SESSION;
	$redirectUri = elgg_get_site_url().'foursquare_api/authorize';

	elgg_load_library('EpiFoursquare');
	elgg_load_library('EpiCurl');

	$consumer_key = elgg_get_plugin_setting('consumer_key', 'foursquare_api');
	$consumer_secret = elgg_get_plugin_setting('consumer_secret', 'foursquare_api');

	// get authorize url
	$api = new EpiFoursquare($consumer_key, $consumer_secret);
	$authorizeurl = $api->getAuthorizeURL($redirectUri);
	return $authorizeurl;
}

/**
 * Returns the access token to use in foursquare calls.
 *
 * @param unknown_type $code
 */
function foursquare_api_get_access_token($code) {
	global $SESSION;
	elgg_load_library('EpiFoursquare');
	elgg_load_library('EpiCurl');

	$redirectUri = elgg_get_site_url().'foursquare_api/authorize';

	$consumer_key = elgg_get_plugin_setting('consumer_key', 'foursquare_api');
	$consumer_secret = elgg_get_plugin_setting('consumer_secret', 'foursquare_api');

	$api = new EpiFoursquare($consumer_key, $consumer_secret);
        $token = $api->getAccessToken($_GET['code'], $redirectUri);

	return $token;
}

/**
 * Checks if this site is accepting new users.
 * Admins can disable manual registration, but some might want to allow
 * foursquare-only logins.
 */
function foursquare_api_allow_new_users_with_foursquare() {
	$site_reg = elgg_get_config('allow_registration');
	$foursquare_reg = elgg_get_plugin_setting('new_users');

	if ($site_reg || (!$site_reg && $foursquare_reg == 'yes')) {
		return true;
	}

	return false;
}
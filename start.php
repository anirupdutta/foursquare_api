<?php

elgg_register_event_handler('init', 'system', 'foursquare_api_init');

function foursquare_api_init() {
	global $CONFIG;

	$base = elgg_get_plugins_path() . 'foursquare_api';
	elgg_register_library('EpiFoursquare', "$base/vendors/foursquareoauth/EpiFoursquare.php");
	elgg_register_library('EpiCurl', "$base/vendors/foursquareoauth/EpiCurl.php");
	elgg_register_library('foursquare_api', "$base/lib/foursquare_api.php");

	elgg_load_library('foursquare_api');

	elgg_extend_view('css', 'foursquare_api/css');

	// sign on with foursquare
	if (foursquare_api_allow_sign_on_with_foursquare()) {
		elgg_extend_view('login/extend', 'foursquare_api/login');
	}

	// register page handler
	elgg_register_page_handler('foursquare_api', 'foursquare_api_pagehandler');

	// allow plugin authors to hook into this service
	elgg_register_plugin_hook_handler('checkins', 'foursquare_service', 'foursquare_api_checkins');
	elgg_register_plugin_hook_handler('todos', 'foursquare_service', 'foursquare_api_todos');
	elgg_register_plugin_hook_handler('venuehistory', 'foursquare_service', 'foursquare_api_venuehistory');

}

function foursquare_api_pagehandler($page) {
	if (!isset($page[0])) {
		forward();
	}

	switch ($page[0]) {
		case 'authorize':
			foursquare_api_authorize();
			break;
		case 'revoke':
			foursquare_api_revoke();
			break;
		case 'forward':
			foursquare_api_forward();
			break;
		case 'login':
			foursquare_api_login();
			break;
		default:
			forward();
			break;
	}
}



/**
 * Retrieve checkin list from foursquare.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */
function foursquare_api_checkins($hook, $entity_type, $returnvalue, $params) {

	elgg_load_library('EpiCurl');
	elgg_load_library('EpiFoursquare');
	
	/*static $plugins;
	if (!$plugins) {
		$plugins = elgg_trigger_plugin_hook('plugin_list', 'foursquare_service', NULL, array());
	}

	// ensure valid plugin
	if (!in_array($params['plugin'], $plugins)) {
		return "NULL";
	}*/

	// check admin settings
	$consumer_key = elgg_get_plugin_setting('consumer_key', 'foursquare_api');
	$consumer_secret = elgg_get_plugin_setting('consumer_secret', 'foursquare_api');
	if (!($consumer_key && $consumer_secret)) {
		return NULL;
	}

	// check user settings
	$user_id = $params['user'];
	$foursquare_id = elgg_get_plugin_user_setting('access_key', $user_id, 'foursquare_api');
	$access_token = elgg_get_plugin_user_setting('access_secret', $user_id, 'foursquare_api');
	if (!($foursquare_id && $access_token)) {
		return "NULL";
	}

	$options = array(
		'limit' => $params['limit'],
	);
	
	// pull checkins
	$api = new EpiFoursquare($consumer_key, $consumer_secret, $access_token);
	$response = $api->get("/users/{$foursquare_id}/checkins", $options);

	return $response;
}

/**
 * Retrieve todos list from foursquare.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */
function foursquare_api_todos($hook, $entity_type, $returnvalue, $params) {

	elgg_load_library('EpiCurl');
	elgg_load_library('EpiFoursquare');
	
	/*static $plugins;
	if (!$plugins) {
		$plugins = elgg_trigger_plugin_hook('plugin_list', 'foursquare_service', NULL, array());
	}

	// ensure valid plugin
	if (!in_array($params['plugin'], $plugins)) {
		return NULL;
	}*/

	// check admin settings
	$consumer_key = elgg_get_plugin_setting('consumer_key', 'foursquare_api');
	$consumer_secret = elgg_get_plugin_setting('consumer_secret', 'foursquare_api');
	if (!($consumer_key && $consumer_secret)) {
		return NULL;
	}

	// check user settings
	$user_id = $params['user'];
	$foursquare_id = elgg_get_plugin_user_setting('access_key', $user_id, 'foursquare_api');
	$access_token = elgg_get_plugin_user_setting('access_secret', $user_id, 'foursquare_api');
	if (!($foursquare_id && $access_token)) {
		return NULL;
	}

        // create an array for additional parameters
	$params = array(
	'sort' => $params['sort'],
        'll' => $params['ll'],
        );

	// pull todos
	$api = new EpiFoursquare($consumer_key, $consumer_secret, $access_token);
	$response = $api->get("/users/{$foursquare_id}/todos",$params);

	return $response;
}

/**
 * Retrieve venuehistory list from foursquare.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */
function foursquare_api_venuehistory($hook, $entity_type, $returnvalue, $params) {

	elgg_load_library('EpiCurl');
	elgg_load_library('EpiFoursquare');

	/*static $plugins;
	if (!$plugins) {
		$plugins = elgg_trigger_plugin_hook('plugin_list', 'foursquare_service', NULL, array());
	}

	// ensure valid plugin
	if (!in_array($params['plugin'], $plugins)) {
		return NULL;
	}*/

	// check admin settings
	$consumer_key = elgg_get_plugin_setting('consumer_key', 'foursquare_api');
	$consumer_secret = elgg_get_plugin_setting('consumer_secret', 'foursquare_api');
	if (!($consumer_key && $consumer_secret)) {
		return NULL;
	}

	// check user settings
	$user_id = $params['user'];
	$foursquare_id = elgg_get_plugin_user_setting('access_key', $user_id, 'foursquare_api');
	$access_token = elgg_get_plugin_user_setting('access_secret', $user_id, 'foursquare_api');
	if (!($foursquare_id && $access_token)) {
		return NULL;
	}

        // create an array for additional parameters
	$params = array(
	'beforeTimestamp' => $params['beforeTimestamp'],
        'afterTimestamp' => $params['afterTimestamp'],
        );

	// pull venuehistory
	// venuehistory endpoint at the time of writing this code allowed only self to be used.userId in place of self not allowed
	$api = new EpiFoursquare($consumer_key, $consumer_secret, $access_token);
	$response = $api->get("/users/self/venuehistory",$params);

	return $response;
}
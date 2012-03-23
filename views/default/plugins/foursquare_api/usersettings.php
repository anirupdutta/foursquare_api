<?php
/**
 * 
 */

$user_id = elgg_get_logged_in_user_guid();
$foursquare_name = elgg_get_plugin_user_setting('foursquare_name', $user_id, 'foursquare_api');
$foursquare_id = elgg_get_plugin_user_setting('access_key', $user_id, 'foursquare_api');
$access_secret = elgg_get_plugin_user_setting('access_secret', $user_id, 'foursquare_api');

$site_name = elgg_get_site_entity()->name;
echo '<div>' . elgg_echo('foursquare_api:usersettings:description', array($site_name)) . '</div>';

if (!$foursquare_id || !$access_secret) {
	// send user off to validate account
	$request_link = foursquare_api_get_authorize_url();
	echo '<div>' . elgg_echo('foursquare_api:usersettings:request', array($request_link, $site_name)) . '</div>';
} else {
	$url = elgg_get_site_url() . "foursquare_api/revoke";
	echo '<div>' . sprintf(elgg_echo('foursquare_api:usersettings:revoke'), $url) . '</div>';
}

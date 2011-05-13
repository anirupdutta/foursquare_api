<?php
/**
 * An english language definition file
 */

$english = array(
	'foursquare_api' => 'Foursquare Services',

	'foursquare_api:requires_oauth' => 'Foursquare Services requires the OAuth Libraries plugin to be enabled.',

	'foursquare_api:consumer_key' => 'Client ID',
	'foursquare_api:consumer_secret' => 'Client Secret',

	'foursquare_api:settings:instructions' => 'You must obtain a client id and secret from <a href="https://foursquare.com/oauth/" target="_blank">Foursquare</a>. Most of the fields are self explanatory, the one piece of data you will need is the callback url which takes the form http://[yoursite]/action/foursquarelogin/return - [yoursite] is the url of your Elgg network.',

	'foursquare_api:usersettings:description' => "Link your %s account with Foursquare.",
	'foursquare_api:usersettings:request' => "You must first <a href=\"%s\">authorize</a> %s to access your Foursquare account.",
	'foursquare_api:authorize:error' => 'Unable to authorize Foursquare.',
	'foursquare_api:authorize:success' => 'Foursquare access has been authorized.',

	'foursquare_api:usersettings:authorized' => "You have authorized %s to access your Foursquare account: @%s.",
	'foursquare_api:usersettings:revoke' => 'Click <a href="%s">here</a> to revoke access.',
	'foursquare_api:revoke:success' => 'Foursquare access has been revoked.',

	'foursquare_api:login' => 'Allow existing users who have connected their Foursquare account to sign in with Foursquare?',
	'foursquare_api:new_users' => 'Allow new users to sign up using their Foursquare account even if manual registration is disabled?',
	'foursquare_api:login:success' => 'You have been logged in.',
	'foursquare_api:login:error' => 'Unable to login with Foursquare.',
	'foursquare_api:login:email' => "You must enter a valid email address for your new %s account.",
);

add_translation('en', $english);

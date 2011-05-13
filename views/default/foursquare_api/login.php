<?php
/**
 * 
 */

$url = elgg_get_site_url() . 'foursquare_api/forward';
$img_url = elgg_get_site_url() . 'mod/foursquare_api/graphics/foursquare_sign_in.png';

$login = <<<__HTML
<div id="login_with_foursquare">
	<a href="$url">
		<img src="$img_url" alt="Foursquare" />
	</a>
</div>
__HTML;

echo $login;

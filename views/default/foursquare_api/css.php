<?php
$images_dir = elgg_get_site_url() . 'mod/widgets-demo/images/';
?>

.elgg-page-header h1 {
	width: 400px;
}

#demo-menu {
	position: absolute;
	left: 400px;
	top: 40px;
	z-index: 1000;
}

#demo-menu li {
	float: left;
	list-style: none;
	margin-right: 15px;
}

#demo-menu li a {
	color: white;
	font-size: 140%;
}

.elgg-sidebar .elgg-widgets-add-panel li {
	width: 150px;
}

#elgg-widget-col-0 {
	width: 100%;
}

#custom-settings-button {
	border-bottom: 1px solid #dedede;
}

#custom-settings-button span {
	border: 1px solid #dedede;
	border-bottom: none;
	padding: 5px;
	padding-bottom: 0;
	cursor: pointer;
}

#custom-settings-button span:hover {
	background-color: #dddddd;
}

#custom-settings {
	border: 1px solid #dedede;
	padding: 10px;
	display: none;
}

#custom-settings li {
	width: 200px;
	list-style: none;
	float: left;
	margin: 2px 10px 2px 0;
	background: #fafafa;
}

#custom-settings label {
	line-height: 20px;
}

#custom-settings p {
	clear: both;
	padding-top: 20px;
}

#custom-settings .elgg-submit-button {
	margin: 10px auto;
	display: block;
}

/*****************************************
Clock   http://css-tricks.com/css3-clock/
******************************************/
#clock {
	position: relative;
	width: 200px;
	height: 200px;
	margin: 20px auto 0 auto;
	background: url(<?php echo $images_dir; ?>clockface.jpg);
	list-style: none;
	margin: 0 auto;
	padding: 0;
}

#sec, #min, #hour {
	position: absolute;
	width: 10px;
	height: 200px;
	top: 0px;
	left: 90px;
}

#sec {
	background: url(<?php echo $images_dir; ?>sechand.png);
	z-index: 3;
}

#min {
	background: url(<?php echo $images_dir; ?>minhand.png);
	z-index: 2;
}

#hour {
	background: url(<?php echo $images_dir; ?>hourhand.png);
	z-index: 1;
}

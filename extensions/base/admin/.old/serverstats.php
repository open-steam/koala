<?php
include_once( "../etc/koala.conf.php" );
if (!defined("SERVERMONITOR") || !SERVERMONITOR) {
	header("location:/");
	exit;
}
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
if (!lms_steam::is_koala_admin(lms_steam::get_current_user())) {
	echo "Access denied";
	exit;
}

if (isset($_GET["img"])) {
	$file = PATH_KOALA . "scripts/serverstats/" . $_GET["img"];
	header( "Content-Type: " . "image/png" );
	header( "Content-Length: " . filesize($file));
	readfile($file);
	exit;
}

$portal->set_page_title( gettext( "Server Monitor" ) );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "serverstats.template.html" );
$content->setCurrentBlock("BLOCK_STAT");
$content->setVariable("STAT_TYPE", "steam");
$content->parse("BLOCK_STAT");
$content->setCurrentBlock("BLOCK_STAT");
$content->setVariable("STAT_TYPE", "user");
$content->parse("BLOCK_STAT");

$portal->set_page_main(
	array(
		array( "link" => ".",
			"name" => gettext( "Server Monitor" )
		)
	),
	$content->get(),
	""
);

$portal->show_html();
?>
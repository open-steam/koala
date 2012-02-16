<?php

include_once( "../etc/koala.conf.php" );
$connector = $GLOBALS[ "STEAM" ];
$portal = lms_portal::get_instance();

$portal->initialize( GUEST_ALLOWED );
$portal->set_page_title();
$lms_user = $portal->get_user();

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "version.template.html" );


if ( $webinterface = $GLOBALS[ "STEAM" ]->get_module( "package:web" ) )
$wi_version = $connector->predefined_command($webinterface, "get_version", array(), 0);
else $wi_version = "not installed";

$steam_version = $connector->get_server_version();
$pike_version = $connector->get_pike_version();
$content->setVariable( "STEAM_VERSION", $steam_version );
$content->setVariable( "PIKE_VERSION", $pike_version );
$content->setVariable( "WI_VERSION", $wi_version );
$content->setVariable( "KOALA_VERSION", KOALA_VERSION );

$portal->set_page_main("", $content->get(), "");
$portal->show_html();
?>

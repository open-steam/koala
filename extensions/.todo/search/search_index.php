<?php

include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

$path = url_parse_rewrite_path( $_GET[ "path" ] );

switch( TRUE)
{
	default:
		include( "search_persons.php" );
		exit;
	break;
}

?>

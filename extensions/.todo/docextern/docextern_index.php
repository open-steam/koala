<?php

include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

$path = url_parse_rewrite_path( $_GET[ "path" ] );

if ( ! $docextern = steam_factory::get_object( $STEAM->get_id(), $_GET[ "id" ] ) )
{
	include( "bad_link.php" );
	exit;
}

if ( ! $docextern instanceof steam_docextern )
{
	include( "bad_link.php" );
	exit;
}

if ( ! $docextern->check_access_read( $user ) )
{
	throw new Exception( "No rights to view this.", E_USER_RIGHTS );
}

switch( TRUE)
{
	case ( $path[ 0 ] == "edit"  ):
		include( "docextern_edit.php" );
		exit;
	break;
	case ( $path[ 0 ] == "delete" ):
		include( "docextern_delete.php" );
		exit;
	break;
}

?>

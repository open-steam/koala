<?php

include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();

$path = url_parse_rewrite_path( $_GET[ "path" ] );

if ( ! $weblog = steam_factory::get_object( $STEAM->get_id(), $_GET[ "id" ] ) )
{
	include( "bad_link.php" );
	exit;
}

if ( ! $weblog instanceof steam_calendar )
{
	if ( $weblog instanceof steam_container )
	{
		$category = $weblog;
		$categories = $category->get_environment();
		$weblog = new steam_weblog( $GLOBALS[ "STEAM" ], $categories->get_environment()->get_id() );
	}
	elseif ( $weblog instanceof steam_date )
	{
		$date = $weblog;
		$weblog = new steam_weblog( $GLOBALS[ "STEAM" ], $date->get_environment()->get_id() );
	}
	else
	{
		include( "bad_link.php" );
		exit;
	}
}
else
{
	$weblog = new steam_weblog( $GLOBALS[ "STEAM" ], $weblog->get_id() );
	define( "OBJ_ID",	$weblog->get_id() );
	if ( ! $weblog->check_access_read( $user ) )
	{
		throw new Exception( "No rights to view this.", E_USER_RIGHTS );
	}
}

if ( ! $weblog instanceof steam_weblog )
{
	include( "bad_link.php" );
	exit;
}

switch( TRUE)
{
	case ( ! ( stripos( $path[ 0 ], "deletecomment" ) === FALSE  ) && $date ):
		$comment_id = substr( $path[ 0 ], 13 );
		define( "OBJ_ID", $date->get_id() );
		include( "comment_delete.php" );
		exit;
	break;
	
	case ( ! ( stripos( $path[ 0 ], "editcomment" ) === FALSE ) && $date ):
		$comment_id = substr( $path[ 0 ], 11 );
		define( "OBJ_ID", $date->get_id() );
		include( "comment_edit.php" );
		exit;
	break;

	case ( $path[ 0 ] == "archive" ):
		if ( empty( $path[ 1 ] ) )
		{
			include( "bad_link.php" );
			exit;
		}
		else
		{
			$timestamp = $path[ 1 ];
			include( "weblog_archive.php" );
			exit;
		}
	break;

	case ( $path[ 0 ] == "new"  ):
		include( "weblog_post.php" );
		exit;
	break;

	case( isset($date) && $date && ( $path[ 0 ] == "delete" ) ):
		if (!defined("OBJ_ID")) define( "OBJ_ID", $weblog->get_id() );
		include( "weblog_entry_delete.php" );
		exit;
	break;

	case( isset($date) && $date && ( $path[ 0 ] == "edit" ) ):
		if (!defined("OBJ_ID")) define( "OBJ_ID", $weblog->get_id() );
		include( "weblog_entry_edit.php" );
		exit;
	breaK;
	
	case ( $path[ 0 ] == "blogroll" ):
		include( "weblog_blogroll.php" );
		exit;
	break;

	case ( $path[ 0 ] == "podcast" ):
		include( "weblog_podcast.php" );
		exit;
	break;

	case ( isset ($category) && $category && ( $path[ 0 ] == "delete" ) ):
		define( "OBJ_ID" , $category->get_id() );
		include( "weblog_category_delete.php" );
		exit;
	break;

	case ( $path[ 0 ] == "new_category" ):
	{
		if (!defined("OBJ_ID")) define( "OBJ_ID", $weblog->get_id() );
		include( "weblog_category_create.php" );
		exit;
	}

	case( isset ($category) && $category && ( empty( $path[ 0 ] ) ) ):
		if (!defined("OBJ_ID")) define( "OBJ_ID", $category->get_id() );
		include( "weblog_category.php" );
		exit;
	break;

	case ( isset ($date) && $date && ( empty( $path[ 0 ] ) ) ):
		if (!defined("OBJ_ID")) define( "OBJ_ID", $date->get_id());
		include( "weblog_comments.php" );
		exit;
	break;

	case ( isset ($category) && $category && ( $path[ 0 ] == "edit" ) ):
		if (!defined("OBJ_ID")) define( "OBJ_ID", $category->get_id() );
		include( "weblog_category_edit.php" );
	break;

	case ( isset ($weblog) && $weblog && ( $path[ 0 ] == "edit" ) ):
		if (!defined("OBJ_ID")) define( "OBJ_ID", $weblog->get_id() );
		include( "weblog_edit.php" );
		exit;
	break;

  	case ( isset ($weblog) && $weblog && ( $path[ 0 ] == "deleteblog" ) ):
		if (!defined("OBJ_ID")) define( "OBJ_ID", $weblog->get_id() );
		include( "weblog_delete.php" );
		exit;
	break;
  
	default:
		include( "weblog_entries.php" );
		exit;
	break;
}

?>

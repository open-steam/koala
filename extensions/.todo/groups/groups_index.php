<?php

include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$portal_user = $portal->get_user();
$path = url_parse_rewrite_path( (isset($_GET["path"])?$_GET[ "path" ]:"") );

try {
  $steam_group = ( ! empty( $_GET[ "id" ] ) ) ? steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "id" ]) : FALSE;
} catch (Exception $ex) {
  include( "bad_link.php" );
  exit;
}

$group_is_private = FALSE;
if ( $steam_group && is_object($steam_group) ) {
	switch( (string) $steam_group->get_attribute( "OBJ_TYPE" ) ) {
		case( "course" ):
			$group = new koala_group_course( $steam_group );
			$backlink = PATH_URL . SEMESTER_URL . "/" . $group->get_semester()->get_name() . "/" . h($group->get_name()) . "/";
		break;
		default:
			$group = new koala_group_default( $steam_group );
			$backlink = PATH_URL . "groups/" . $group->get_id() . "/";
      // Determine if group is public or private
      $parent = $group->get_parent_group();
      if ($parent->get_id() == STEAM_PRIVATE_GROUP ) $group_is_private = TRUE;
		break;
	}
}

if ($group_is_private) {
  if ( !$steam_group->is_member( $user ) && !lms_steam::is_koala_admin($user) )
    throw new Exception( gettext( "You have no rights to access this group" ), E_USER_RIGHTS );
}

switch( TRUE)
{
	case(  $steam_group && empty( $path[ 0 ] ) ):
		if ( $_GET[ "id" ] != STEAM_PUBLIC_GROUP )
			include( "groups_start.php" );
		else
			include( "groups_public.php" );
		exit;
	break;
	case( $steam_group && ( $path[ 0 ] == "members" ) && empty( $path[ 1 ] ) ):
		if ( ! $portal_user->is_logged_in() )
			throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
		include( "groups_members.php" );
		exit;
	break;

	case( $steam_group && ( $path[ 0 ] == "members" ) && $path[ 1 ] == "excel" ):
		if ( ! $portal_user->is_logged_in() )
			throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
		include( "groups_members_excel.php" );
		exit;
		break;

	case( $steam_group && ( $path[ 0 ] ==  "communication" ) ):
		$backlink .= "communication/";
		if ( ! $portal_user->is_logged_in() )
			throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
		include( "groups_communication.php" );
		exit;
	break;

	case ( $steam_group && ( $path[ 0 ] == "requests") ):
		if ( ! $portal_user->is_logged_in() )
			throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
	    include( "group_membership_requests.php" );
		exit;
	break;

	case( $steam_group && ($path[0]=="edit") ):
		include( "groups_edit.php" );
		exit;
	break;

	case( $steam_group && ($path[0]=="delete") ):
		include( "groups_delete.php" );
		exit;
	break;

	case( $steam_group && ( $path[ 0 ] == "calendar") && ( $path[ 1 ] == "new") ):
		if ( ! $portal_user->is_logged_in() )
			throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
		$backlink = array( "link" => $backlink, "name" => h($group->get_name()) );
		$calendar = $group->get_calendar();
		include( "event_edit.php" );
		exit;
	break;

	case( $steam_group && ( $path[ 0 ] == "calendar") && ( $path[ 2 ] == "details") ):
		if ( ! $portal_user->is_logged_in() )
			throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
		$backlink = array( "link" => $backlink, "name" => h($group->get_name()) );
		$calendar = $group->get_calendar();
		if ( ! $event = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 1 ] ) )
			include( "bad_link.php" );
		else
			include( "event_details.php" );
		exit;
	break;

	case( $steam_group && ( $path[ 0 ] == "calendar") && ( $path[ 2 ] == "edit") ):
		if ( ! $portal_user->is_logged_in() )
			throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
		$backlink = array( "link" => $backlink, "name" => h($group->get_name()) );
		$calendar = $group->get_calendar();
		if ( ! $event = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 1 ] ) )
			include( "bad_link.php" );
		else
			include( "event_edit.php" );
		exit;
	break;

	case( $steam_group && ( $path[ 0 ] == "calendar") && ( $path[ 2 ] == "delete") ):
		if ( ! $portal_user->is_logged_in() )
			throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
		$backlink = array( "link" => $backlink, "name" => h($group->get_name()) );
		$calendar = $group->get_calendar();
		if ( ! $event = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 1 ] ) )
		{
			include( "bad_link.php" );
		}
		else
		{
			$title = $event->get_attribute( "DATE_TITLE" );
	    	lms_steam::delete( $event );
			$_SESSION[ "confirmation" ] = str_replace( "%TITLE", htmlentities($title,ENT_QUOTES, "UTF-8"), gettext( "Event %TITLE deleted." ) );
			header( "Location: " . $backlink[ "link" ] . "calendar/");
		}
		exit;
	break;

	case( $steam_group && ( $path[ 0 ] == "calendar") && ( $path[ 1 ] ) ):
		if ( ! $portal_user->is_logged_in() )
			throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
		$backlink = array( "link" => $backlink, "name" => h($group->get_name()) );
		$calendar = $group->get_calendar();
		if ( ! $event = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 1 ] ) )
			include( "bad_link.php" );
		else
			include( "event_details.php" );
		exit;
	break;

	case( $steam_group && ( $path[ 0 ] == "calendar") ):
		if ( ! $portal_user->is_logged_in() )
			throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
		$calendar = $group->get_calendar();
		$backlink = array( "link" => $backlink, "name" => h($group->get_name()) );
		include( "calendar.php" );
		exit;
	break;

	case( ! ( empty( $_GET[ "id" ] ) ) && ( count( $path ) == 0 ) ):
		include( "groups_all.php" );
		exit;
	break;

	// Try the extensions:
	case ( $steam_group && isset($path[0]) && !empty($path[0]) ):
		$extension_manager = lms_steam::get_extensionmanager();
		$extension_manager->handle_path( $path, new koala_group_default( $steam_group ), $portal );
	break;

	default:
		include( "groups_public.php" );
		exit;
	break;
}

?>

<?php

include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user   = lms_steam::get_current_user();

$path = url_parse_rewrite_path( $_GET[ "path" ] );
$backlink = PATH_URL . "desktop/";

(!isset($path[1])) ? $path[1] = "" : "";

switch( TRUE)
{
	case( $_GET[ "show" ] == "calendar" && $path[ 0 ] == "new" ):
		$calendar = $user->get_calendar();
		$backlink = array( "link" => $backlink . "calendar/", "name" => gettext( "Your calendar" ) );
		include( "event_edit.php" );
		exit;
	break;

	case( $_GET[ "show" ] == "calendar" && $path[ 1 ] == "details" ):
		$calendar = $user->get_calendar();
		$backlink = array( "link" => $backlink . "calendar/", "name" => gettext( "Your calendar" ) );
		if ( ! $event = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 0 ] ) )
		{
			include( "bad_link.php" );
		}
		else
		{
			include( "event_details.php" );
		}
		exit;
	break;

	case( $_GET[ "show" ] == "calendar" && $path[ 1 ] == "edit" ):
		$calendar = $user->get_calendar();
		$backlink = array( "link" => $backlink . "calendar/", "name" => gettext( "Your calendar" ) );
		if ( ! $event = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 0 ] ) )
		{
			include( "bad_link.php" );
		}
		else
		{
			include( "event_edit.php" );
		}
		exit;
	break;

	case( $_GET[ "show" ] == "calendar" && $path[ 1 ] == "delete" ):
		$calendar = $user->get_calendar();
		$backlink = array( "link" => $backlink . "calendar/", "name" => gettext( "Your calendar" ) );
		if ( ! $event = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 0 ] ) )
		{
			include( "bad_link.php" );
		}
		else
		{
			$title = $event->get_attribute( "DATE_TITLE" );
	    	lms_steam::delete( $event );
			$_SESSION[ "confirmation" ] = str_replace( "%TITLE", htmlentities($title,ENT_QUOTES, "UTF-8"), gettext( "Event %TITLE deleted." ) );
			header( "Location: " . $backlink[ "link" ] );
		}
		exit;
	break;

	case( $_GET[ "show" ] == "calendar" && !empty( $path[ 0 ] ) ):
		$calendar = $user->get_calendar();
		$backlink = array( "link" => $backlink . "calendar/", "name" => gettext( "Your calendar" ) );
		if ( ! $event = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 0 ] ) )
		{
			include( "bad_link.php" );
		}
		else
		{
			include( "event_edit.php" );
		}
		exit;
	break;

	case( $_GET[ "show" ] == "calendar" ):
		$calendar = $user->get_calendar();
		$backlink = array( "link" => $backlink, "name" => gettext( "Desktop" ) );
		include( "calendar.php" );
		exit;
	break;

	case( $_GET[ "show" ] == "news" ):
		if ( $path[ 0 ] == "subscr" )
		{
			include( "rss_feeds_subscr.php" );
		}
		else
		{
			include( "rss_feeds.php" );
		}
		exit;
	break;

	case ( $_GET[ "show" ] == "clipboard" ):
		$backlink .= "clipboard/";
		$documents_root = $user;
		$documents_path = $path;
		include( "user_clipboard.php" );
		exit;
	break;

	// Try the extensions:
	case ( isset( $_GET["show"] ) && !empty( $_GET["show"] ) ):
		$extension_manager = lms_steam::get_extensionmanager();
		$extension_manager->handle_path( array_merge( array( $_GET["show"] ), $path ), new koala_user( lms_steam::get_current_user() ), $portal );
	break;
}

echo "Test";
$portal->set_problem_description("Diese Erweiterung ist in der Plattform zur Zeit nicht aktiviert.", "test");

include( "home.php" );

?>

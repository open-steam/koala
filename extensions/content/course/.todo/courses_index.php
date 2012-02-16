<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

if (!isset($portal) || !is_object($portal)) {
  $portal = lms_portal::get_instance();
  $portal->initialize( GUEST_NOT_ALLOWED );
} else $portal->set_guest_allowed( GUEST_NOT_ALLOWED );

$user = lms_steam::get_current_user();
$portal_user = $portal->get_user();

if ( !empty( $_GET[ "path" ] ) )
	$path = url_parse_rewrite_path( $_GET[ "path" ] );
else
	$path = array();


$scg = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_COURSES_GROUP, CLASS_GROUP );

// Get data for the current semester or the semester specified in the URL

if ( empty( $_GET[ "semester" ] ) )
{
        $current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $scg->get_groupname() . "." . STEAM_CURRENT_SEMESTER );
}
else
{
        if ( ! $current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $scg->get_groupname() . "." . $_GET[ "semester" ] ) )
        {
                include( "bad_link.php" );
                exit;
        }
}

if ( ! empty( $path[ 1 ] ) )
{
        if ( $path[ 0 ] == "hislsf")   // Get course data from hislsf
        {
                include( "courses_create_hislsf.php" );
                exit;
        }

        // Try to get the steam object for the courseid specified in the url. Send the user to an error page if this fails.

        if ( $group_course = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $current_semester->get_groupname() . "." . $path[ 0 ] ) )
        {
                $group = new koala_group_course( $group_course );
                $backlink = PATH_URL . SEMESTER_URL . "/" . $group->get_semester()->get_name(). "/" . $group->get_name(). "/";
        }
        else
        {
                include( "bad_link.php" );
        }

}

switch( TRUE)
{
 		// Create a new course => courses_create.php

        case( isset($path[0]) && $path[ 0 ] == "new" ):
                include( "courses_create.php" );
                exit;
        break;


		// Create a new course via PAUL
        case( isset($path[0]) && $path[ 0 ] == "paul" ):
                include( "courses_create_paul.php" );
                exit;
        break;


	case( isset($path[0]) && $path[ 0 ] == "admins" ):
          if ( lms_steam::is_steam_admin( $user ) ) {
            if ( ! $portal_user->is_logged_in() )
              throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
            $semester_admins = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $current_semester->get_groupname() . ".admins" );
            $admin_group = new koala_group_default( $semester_admins );
            include( "semester_admins.php" );
            exit;
          }
          else {
            include( "bad_link.php" );
            exit;
          }
        break;

		// Edit data for a course => courses_edit.php
  case( isset($path[1]) && $path[ 1 ] == "edit" ):
          if ( ! $portal_user->is_logged_in() ){
            throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
      }
      include( "courses_edit.php" );
      exit;
  break;

  // Overview of participants => groups_members.php
  case( isset($path[1]) && $path[ 1 ] == "learners" && empty( $path[ 2 ] ) ):
    if ( ! $portal_user->is_logged_in() ){
      throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
    }
    if ( $group->is_member( $user ) || ( $group->is_admin( $user ) )  ){
          include( "groups_members.php" );
          exit;
    } else {
          throw new Exception( "No rights to view this.", E_USER_RIGHTS  );
    }
  break;

  // Excel-table of participants => groups_members_excel.php
  case( isset($path[1]) && $path[ 1 ] == "learners" && isset($path[2]) && $path[ 2 ] == "excel" ):
          if ( ! $portal_user->is_logged_in() ){
            throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
      }
      if ( $group->is_admin( $user )  ){
            include( "groups_members_excel.php" );
            exit;
      } else {
            throw new Exception( "No rights to view this.", E_USER_RIGHTS  );
  }
  break;

  // Membership requests => group_membership_requests.php
  case( isset($path[1]) && $path[ 1 ] == "requests" ):
          if ( ! $portal_user->is_logged_in() ){
            throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
      }
      include( "group_membership_requests.php" );
      exit;
  break;

  // Overview of staff => groups_staff.php
  case( isset($path[1]) && $path[ 1 ] == "staff" && empty( $path[ 2 ] ) ):
    if ( ! $portal_user->is_logged_in() ){
      throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
    }
    if ( $group->is_admin( $user ) ){
          include( "groups_staff.php" );
          exit;
    } else {
          throw new Exception( "No rights to view this.", E_USER_RIGHTS  );
    }
  break;

	// Excel-table of staff members => groups_members_excel.php
  case( isset($path[1]) && $path[ 1 ] == "staff" && isset($path[2]) && $path[ 2 ] == "excel" ):
          if ( ! $portal_user->is_logged_in() ){
            throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
      }
      if ( $group->is_admin( $user )  ){
      		$list_staff = true;
            include( "groups_members_excel.php" );
            exit;
      } else {
            throw new Exception( "No rights to view this.", E_USER_RIGHTS  );
  }
  break;

  // Create a new calendar event and display it => event_details.php
  case( isset($path[1]) && $path[ 1 ] == "calendar" && isset($path[2]) && $path[ 2 ] == "new"  ):
          if ( ! $portal_user->is_logged_in() ){
            throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
      }
      $calendar = $group->get_calendar();
      $backlink = array( "link" => $backlink . "calendar/", "name" => str_replace( "%COURSE", $group->get_attribute( "OBJ_DESC" ), gettext( "Calendar of '%COURSE'" ) ) );
      include( "event_details.php" );
      exit;
  break;

  // Display a calendar event => event_details.php
  case( isset($path[1]) && $path[ 1 ] == "calendar" && isset($path[3]) && $path[ 3 ] == "details" ):
          if ( ! $portal_user->is_logged_in() ){
            throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
      }
      $calendar = $group->get_calendar();
      $backlink = array( "link" => $backlink . "calendar/", "name" => str_replace( "%COURSE", $group->get_attribute( "OBJ_DESC" ), gettext( "Calendar of '%COURSE'" ) ) );
      if ( ! $event = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $path[ 2 ] ) ){
            include( "bad_link.php" );
      } else {
            include( "event_details.php" );
      }
      exit;
  break;

  // Display the course calendar => calendar.php
  case( isset($path[1]) && $path[ 1 ] == "calendar" ):
          if ( ! $portal_user->is_logged_in() ){
            throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
      }
      $calendar = $group->get_calendar();
      $backlink = array( "link" => $backlink, "name" => $group->get_course_name() );
      include( "calendar.php" );
      exit;
  break;

  // Display group communication => groups_communication.php
  case( isset($path[1]) && $path[ 1 ] == "communication"  ):
          if ( ! $portal_user->is_logged_in() ){
            throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
      }
      include( "groups_communication.php" );
      exit;
  break;

  // Display reserver list => courses_reserve_list.php
  case( isset($path[1]) && $path[ 1 ] == "reserve_list"  ):
          if ( ! $portal_user->is_logged_in() ){
            throw new Exception( "Access denied. Please login.", E_USER_AUTHORIZATION );
      }
      $course = new koala_group_course( $group_course );
      include( "courses_reserve_list.php" );
      exit;
  break;

  // Try the extensions:
  case ( isset($path[1]) && !empty($path[1]) ):
    $course = $group;//new koala_group_course( $group_course );
        $extension_manager = lms_steam::get_extensionmanager();
    $extension_manager->handle_path( array_slice( $path, 1 ), $course, $portal );
  break;

  // Display the inital page of a course => courses_start.php
  case ( ! empty( $path[ 0 ] ) ):
    if ( $group_course = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $current_semester->get_groupname() . "." . $path[ 0 ] ) ){
      $course = new koala_group_course( $group_course );
      include( "courses_start.php" );
      exit;
    } else {
      include( "bad_link.php" );
      exit;
    }
  break;

  // Display the courses overview. => courses_overview.php
  default:
    include( "courses_overview.php" );
    exit;
  break;
}
?>
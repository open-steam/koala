<?php

require_once( "../etc/koala.conf.php" );
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$user = lms_steam::get_current_user();

if ( ! $steam_group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "course" ] ) )
	throw new Exception( "Course not found: " . $_GET[ "course" ] );
if ( ! $steam_group instanceof steam_group )
	throw new Exception( "Is not a group: " . $_GET[ "course" ] );
if ( ( string ) $steam_group->get_attribute( "OBJ_TYPE" ) != "course" )
	throw new Exception( "Is not a course: " . $_GET[ "course" ] );

$course = new koala_group_course( $steam_group );
$backlink = PATH_URL . SEMESTER_URL . "/" . $course->get_semester()->get_name() . "/" . $course->get_name() . "/";

$course->set_attribute( "SEM_APP_ID", NULL );
$_SESSION[ "confirmation" ] = gettext( "Reserve list removed." );

header( "Location: " . $backlink);
exit;

?>

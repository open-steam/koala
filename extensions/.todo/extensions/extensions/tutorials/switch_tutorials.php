<?php
//include_once( "../../etc/koala.conf.php" );
//require_once (PATH_CLASSES . "koala_group_course.class.php");

//$portal = lms_portal::get_instance();
//$portal->initialize( GUEST_ALLOWED );

//$course_group = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET["owner"], CLASS_GROUP );

//$course = new koala_group_course($course_group);

$backlink = PATH_URL . SEMESTER_URL . "/" . $course->get_semester()->get_name() . "/" . $course->get_name() . "/";

//$switch = $_GET["switch"];

switch($switch)
{
case "enable":
	if ( !isset( $extension_manager ) )
		$extension_manager = new extension_manager();
	$tutorials = $extension_manager->get_extension( "tutorials" );
	$tutorials->enable_for( $course );
  break;
case "disable":
	if ( !isset( $extension_manager ) )
		$extension_manager = new extension_manager();
	$tutorials = $extension_manager->get_extension( "tutorials" );
	$tutorials->disable_for( $course );
	break;
default:
	include( "bad_link.php" );
	exit;
}

header( "Location: " . $backlink );
?>

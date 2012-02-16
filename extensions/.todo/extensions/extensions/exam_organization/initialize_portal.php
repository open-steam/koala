<?php
/*
 * setup the portal object and the database
 * 
 * @author Marcel Jakoblew
 */

$eoDatabase = exam_organization_database::getInstance();
$eoDatabase->connect((string) $course->get_name());
$examObject = exam_organization_exam_object_data::getInstance($course);

//create portal
if (!isset($portal)) {
  $portal = lms_portal::get_instance();
  $portal->initialize( GUEST_ALLOWED );
} else $portal->set_guest_allowed( GUEST_ALLOWED );

//post data evaluation for headline messages
if (isset($_SESSION["examorganization_show_confirmation"])) {
	$portal->set_confirmation($_SESSION["examorganization_show_confirmation"]);
	unset($_SESSION["examorganization_show_confirmation"]);
}

if (isset($_SESSION["examorganization_show_problem_description"])) {
	$portal->set_problem_description($_SESSION["examorganization_show_problem_description"]);
	unset($_SESSION["examorganization_show_problem_description"]);
}


//workaround for including phpexcel
include_once(PATH_CLASSES . "phpexcel/Classes/PHPExcel.php");
include_once(PATH_CLASSES . "phpexcel/Classes/PHPExcel/IOFactory.php");
?>
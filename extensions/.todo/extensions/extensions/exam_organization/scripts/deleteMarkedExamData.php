<?php
include( "../../../etc/koala.conf.php" );
include("../classes/exam_organization_conf.php");

function deleteExamDataOnCourse($courseObject, $ageOfDataInDays){
	
}


echo "Script for deleting marked exam data\n";
$newline = "\n";

echo "Loggin in...$newline";
$steam_user = new lms_user( STEAM_ROOT_LOGIN, STEAM_ROOT_PW); //TODO: use phpsteam here. this fails if wrong login data for root
$steam_user->login();

$user_module = $GLOBALS[ "STEAM" ]->get_module( "users" );
$current_semester = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), SYNC_KOALA_SEMESTER, 0 );

echo "Current semester is $current_semester $newline";

//$courses_koala  = lms_steam::semester_get_courses( $current_semester->get_id() );
$courses_koala  = lms_steam::semester_get_courses( 1117 ); //id der kurs-gruppe


echo "Searching for courses...$newline";
foreach ($courses_koala as $course){
	echo "Course found $newline";
}
echo "finished searching for courses!$newline";
?>
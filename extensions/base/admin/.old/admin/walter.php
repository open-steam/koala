<?php
include_once( "../../etc/koala.conf.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
if( !lms_steam::is_koala_admin($user) )
{
	header("location:/");
	exit;
}

$mail_sum = 0;
$user_count =  0;
$users = $GLOBALS['STEAM']->predefined_command( $GLOBALS['STEAM']->get_module('users'), 'index', array(), FALSE );
foreach ($users as $user) {
	$user_count++;
	$steam_user = steam_factory::get_user($GLOBALS['STEAM']->get_id(), $user);
	$mail_sum += count($steam_user->get_annotations());
	
	if ($user_count % 100 == 0 ) {
		echo ". user_count: " . $user_count . " mail_sum: " . $mail_sum . "<br />";
	} else {
		echo ".";
	}
	flush();
}

echo "<br> #User: " . $user_count . " #Mail: " . $mail_sum;

//$semesters_group = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses");
//if ($semesters_group instanceof steam_group) {
//	echo "<h1>Prüfe alle Semester<h1>";
//	
//	$semesters = $semesters_group->get_subgroups();
//	foreach ($semesters as $semester) {
//		echo "<h2>Prüfe Semester " . $semester->get_attribute("OBJ_NAME") . " (" . $semester->get_attribute("OBJ_DESC") .")</h2>";
//		$courses = $semester->get_subgroups();
//		foreach ($courses as $course) {
//			echo $course->get_attribute("OBJ_NAME") . " (" . $course->get_attribute("OBJ_DESC") .") <br/>";
//			
//		}
//	} 
//	
//	
//}

?>
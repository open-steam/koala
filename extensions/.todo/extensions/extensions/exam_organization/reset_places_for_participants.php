<?php
/*
 * set the places for each participant
 * 
 * @author Marcel Jakoblew
 */

//get the rooms
$roomsString = "";
$eoDatabase = exam_organization_database::getInstance();
$eoDatabase->connect((string) $course->get_name());
$availableRooms = $eoDatabase->getRoomList();
foreach($availableRooms as $room){
	if ($course->get_attribute("EXAM".$examTerm."_checkbox_room_".$room)=="on"){
		$roomsString.=$room." ";
	}
}

$term = (int)($course->get_attribute("EXAM_ORGANIZATION_LAST_TERM_SELECTED"));
$resultSetPlaces = $eoDatabase->createPlaceListAndSaveToDatabase($roomsString,$term);

if ($resultSetPlaces){
	$examObject = exam_organization_exam_object_data::getInstance($course);
	$examObject->setStatus($examTerm,"places",FALSE);
	$portal->set_confirmation(str_replace("%EXAMTERM",$examTerm, gettext("Reset seating for exam term %EXAMTERM")));
} else {
	$portal->set_problem_description(gettext("There are not enough places for all participants. Please choose another room constellation."));
	$examObject = exam_organization_exam_object_data::getInstance($course);
	$examObject->setStatus($examTerm,"places",FALSE);
}
?>
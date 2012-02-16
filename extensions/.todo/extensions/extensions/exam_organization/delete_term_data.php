<?php
/*
 * delete term data
 * 
 * @author Marcel Jakoblew
 */

$eoDatabase = exam_organization_database::getInstance();
$deleteCourseResult = $eoDatabase->deleteTerm($examTerm);

if($deleteCourseResult){
	$examObject = exam_organization_exam_object_data::getInstance($course);
	$examObject->setStatus($examTerm,"places",FALSE);
	$examObject->setStatus($examTerm,"bonus",FALSE);
	$examObject->setStatus($examTerm,"time",FALSE);
	$examObject->setStatus($examTerm,"date",FALSE);
	$examObject->setStatus($examTerm,"assignments",FALSE);
	$examObject->setStatus($examTerm,"room",FALSE);
	$examObject->setStatus($examTerm,"examkey",FALSE);
	//reset date and time
	$examObject->resetDateAndTime($examTerm);
	$portal->set_confirmation(gettext("Deleted term data for exam term")." ".$examTerm);
} else {
	$portal->set_problem_description(gettext("Error while deleting data for exam term")." ".$examTerm);
}

?>
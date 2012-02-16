<?php
/*
 * delete database script
 * 
 * @author Marcel Jakoblew
 */

$eoDatabase = exam_organization_database::getInstance();
$deleteCourseResult = $eoDatabase->deleteCourse();

if($deleteCourseResult){
	$portal->set_confirmation(gettext("Deleted database for this course"));
} else {
	$portal->set_problem_description(gettext("Error while deleting database"));
}

?>
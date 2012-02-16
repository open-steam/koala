<?php
/*
 * add term script
 * 
 * @author Marcel Jakoblew
 */

$examActivated = FALSE;
$examObject = exam_organization_exam_object_data::getInstance($course);

if (!$examObject->termIsActivated("1") && !$examActivated) {$status = $examObject->activateTerm("1");$examActivated=TRUE;}
if (!$examObject->termIsActivated("2") && !$examActivated) {$status = $examObject->activateTerm("2");$examActivated=TRUE;}
if (!$examObject->termIsActivated("3") && !$examActivated) {$status = $examObject->activateTerm("3");$examActivated=TRUE;}

if($examActivated){
	$portal->set_confirmation(gettext("Created new exam term (The maximum number of exam terms is three)"));
} else {
	$portal->set_problem_description(gettext("Exam term not created, because the maximum number of exam terms (three) are already created"));
}
?>
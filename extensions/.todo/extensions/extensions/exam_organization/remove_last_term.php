<?php
/*
 * remove term script
 * 
 * @author Marcel Jakoblew
 */

$examDeActivated = FALSE;
$examObject = exam_organization_exam_object_data::getInstance($course);

if ($examObject->termIsActivated("3") && !$examDeActivated) {$status = $examObject->deactivateTerm("3");$examDeActivated=TRUE;}
if ($examObject->termIsActivated("2") && !$examDeActivated) {$status = $examObject->deactivateTerm("2");$examDeActivated=TRUE;}
if ($examObject->termIsActivated("1") && !$examDeActivated) {$status = $examObject->deactivateTerm("1");$examDeActivated=TRUE;}

if($examDeActivated){
	$portal->set_confirmation(gettext("Removed last exam term"));
} else {
	$portal->set_problem_description(gettext("Error while removing last exam term"));
}
?>
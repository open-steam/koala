<?php
/*
 * delete exam term
 * 
 * @author Marcel Jakoblew
 */

$examObject = exam_organization_exam_object_data::getInstance($course);
$status = $examObject->deactivateTerm($examTerm);

if($status){
	$portal->set_confirmation(gettext("Deleted term")." ".$examTerm);
} else {
	$portal->set_problem_description(gettext("Error while deleting term")." ".$examTerm);
}
?>
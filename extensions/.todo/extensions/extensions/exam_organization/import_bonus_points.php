<?php
/*
 * import bonus points script
 * 
 * @author Marcel Jakoblew
 */

$bonusManager = bonus_import::getInstance();
$bonusStatus = $bonusManager->importBonusSteps($course);
$examObject = exam_organization_exam_object_data::getInstance($course);

if($bonusStatus!=FALSE){
	$examObject->setStatus($examTerm,"bonus",TRUE);
	$portal->set_confirmation(gettext("Bonus points imported"));
} else {
	$examObject->setStatus($examTerm,"bonus",FALSE);
	$portal->set_problem_description(gettext("Bonus points not imported"));
}
?>
<?php
/*
 * ajax server script
 * 
 * @author Marcel Jakoblew
 */

//connect to db
$eoDatabase = exam_organization_database::getInstance();
$eoDatabase->connect((string) $course->get_name());
$examObject = exam_organization_exam_object_data::getInstance($course);

//define functions
function visibleStatusDateTime($examObject, $term, $value=-1){
	$returnValue = $examObject->setDateAndTimeVisibleStatus($term, $value);
	echo $returnValue;
	exit(0);
}

function visibleStatusRooms($examObject, $term, $value=-1){
	$returnValue = $examObject->setRoomsVisibleStatus($term, $value);
	echo $returnValue;
	exit(0);
}

function getFullName($examObject, $examTerm, $matriculationNumber){
	$examOffice = exam_office_file_handling::getInstance();
	if(!($examOffice->isMatriculationNumber($matriculationNumber))){
		//case no matriculation number
		echo "0";
	} else {
		//valid matriculation number
		$ldapManager = exam_organization_ldap_manager::getInstance();
		$fullName = $ldapManager->getFullName($matriculationNumber);
		echo $fullName;
	}
	exit(0);
}

function participantsTableChange($examObject, $term, $value=-1){
	//$returnValue = $examObject->setRoomsVisibleStatus($term, $value);
	$eoDatabase = exam_organization_database::getInstance();
	$exploded = explode(":",$value);
	
	$login=$exploded[0];
	$workcase=$exploded[1];
	$value=$exploded[2];
	$dataChanged = FALSE;
	
	switch($workcase){
		case "firstname":{
			//$dataChanged=TRUE;
			$dataChanged = $eoDatabase->updateFirstName($login, $value);
			break;
		}
		case "lastname":{
			//$dataChanged=TRUE;
			$dataChanged = $eoDatabase->updateLastName($login, $value);
			break;
		}
		case "mnr":{
			//$dataChanged=TRUE;
			$dataChanged = $eoDatabase->updateMatriculationNumber($login, $value);
			break;
		}
		case "room":{
			//$dataChanged=TRUE;
			$dataChanged = $eoDatabase->updateRoom($term, $login, $value);
			break;
		}
		case "place":{
			//$dataChanged=TRUE;
			$dataChanged = $eoDatabase->updatePlace($term, $login, $value);
			break;
		}
		case "bonus":{
			//$dataChanged=TRUE;
			$dataChanged = $eoDatabase->updateBonus($login, $value);
			break;
		}
		default:{break;}
	}
	if($dataChanged){
		echo "tableChanged";
	} else {
		echo "tableNotChanged";
	}
	exit(0);
}

function deleteParticipantFromTerm($examObject, $term, $value=-1){
	$eoDatabase = exam_organization_database::getInstance();
	$result = $eoDatabase->deleteParticipantFromTerm($term, $value);
	exit(0);
}

//end define functions



//case handling
if (!isset($_GET["case"]) || !isset($_GET["case"])){
	echo "AJAX-NO-CASE-PARAM-SET";
	exit(0);
}

if (!isset($_GET["examterm"]) || !isset($_GET["examterm"])){
	echo "AJAX-NO-EXAMTERM-PARAM-SET";
	exit(0);
}

if (!isset($_GET["value"]) || !isset($_GET["value"])){
	echo "AJAX-NO-VALUE-PARAM-SET";
	exit(0);
}

$case = $_GET["case"];
$value = $_GET["value"];
$examTerm = $_GET["examterm"];

//main switch case
switch ($case){
	case "visibleStatusDateTime":{
		visibleStatusDateTime($examObject, $examTerm, $value); break;
	}
	case "visibleStatusRooms":{
		visibleStatusRooms($examObject, $examTerm, $value); break;
	}
	case "tablechange":{
		participantsTableChange($examObject, $examTerm, $value); break;
	}
	case "deleteParticipantFromTerm":{
		deleteParticipantFromTerm($examObject, $examTerm, $value); break;
	}
	case "getFullName":{
		getFullName($examObject, $examTerm, $value); break;
	}
	case "message_delete_term":{
		echo(str_replace("%VALUE",$examTerm,gettext("Do you really want delete exam term %VALUE ?")));exit(0); break;
	}
	case "message_delete_participant":{
		$ldapManager = exam_organization_ldap_manager::getInstance();
		$fullName = $ldapManager->getFullName($value);
		echo(str_replace("%VALUE",$fullName,gettext("Do you really want to delete participant %VALUE ?")));exit(0); break;
	}
	case "message_delete_exam_data":{
		echo(str_replace("%TERM",$examTerm,gettext("Do you really want to delete the exam data for term %TERM ?")));exit(0); break;
	}
	default: echo "AJAX-CASE-NOT-FOUND";exit(0);
}
echo "AJAX-NOTHING-DONE";
exit(0);
?>
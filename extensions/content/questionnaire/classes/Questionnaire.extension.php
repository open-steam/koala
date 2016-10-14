<?php
class Questionnaire extends AbstractExtension implements IObjectExtension, IIconBarExtension {

	public function getName() {
		return "questionnaire";
	}

	public function getDesciption() {
		return "Extension for Questionnaires.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Petertonkoker", "Jan", "janp@mail.uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Fragebogen neu";
	}

	public function getObjectReadableDescription() {
		return "Mit Fragebögen können Sie mithilfe von verschiedenen Fragetypen Rückmeldungen einholen, Umfragen durchführen oder Lernerfolge messen";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/questionnaire.svg";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/728049/";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Questionnaire\Commands\NewQuestionnaireForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$QuestionnaireObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$QuestionnaireType = $QuestionnaireObject->get_attribute("OBJ_TYPE");
		if ($QuestionnaireType != "0" && $QuestionnaireType == "QUESTIONNAIRE_CONTAINER") {
			return new \Questionnaire\Commands\Index();
		}
		return null;
	}

	public function getPriority() {
		return 8;
	}

	public function getIconBarEntries() {
		$array = array();
		$path = strtolower($_SERVER["REQUEST_URI"]);
		if(strpos($path, "questionnaire") !== false){
			$pathArray = explode("/", $path);
			$currentObjectID = "";
			for ($count = 0; $count < count($pathArray); $count++) {
					if (intval($pathArray[$count]) !== 0) {
							$currentObjectID = $pathArray[$count];
							break;
					}
			}

			if($currentObjectID != ""){
				$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
				if($object instanceof \steam_room){
					$questionnaire = $object;
					$questionnaireId = $currentObjectID;
					$survey = $questionnaire->get_inventory()[0];
					$surveyId = $survey->get_id();
				}
				else{
					$survey = $object;
					$surveyId = $currentObjectID;
					$questionnaire = $survey->get_environment();
					$questionnaireId = $questionnaire->get_id();
				}

				$array = array();
				$user = lms_steam::get_current_user();
				$checkAccessAdmin = $survey->check_access(SANCTION_ALL, $user);
				if ($checkAccessAdmin) {
					if(strpos($path, "individualresults") !== false){
						$array[] = array("name" => "<div title='Export als Excel-Datei'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/application_ms-excel.svg#application_ms-excel'/></svg></div>", "onclick"=>"location.href='" . PATH_URL .  "questionnaire/export/" . $surveyId . "/'");
						$array[] = array("name" => "<div title='Anzeigen'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/questionnaire.svg#questionnaire'/></svg></div>", "onclick"=>"location.href='" . PATH_URL .  "questionnaire/index/" . $questionnaireId . "/'");
						$array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$questionnaireId}}, '', 'popup', null, null, 'explorer');return false;");
						$array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$questionnaireId}, 'dialog':true}, '', 'popup', null, null, 'questionnaire');return false;");
					}
					else if(strpos($path, "overallresults") !== false){
						$array[] = array("name" => "<div title='Anzeigen'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/questionnaire.svg#questionnaire'/></svg></div>", "onclick"=>"location.href='" . PATH_URL .  "questionnaire/index/" . $questionnaireId . "/'");
						$array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$questionnaireId}}, '', 'popup', null, null, 'explorer');return false;");
						$array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$questionnaireId}, 'dialog':true}, '', 'popup', null, null, 'questionnaire');return false;");
					}
					else if(strpos($path, "edit") !== false){
						$array[] = array("name" => "<div id='sort-icon' title='Sortieren' name='false' onclick='if($(this).attr(\"name\") == \"false\"){initiateSortable();}else{removeSortable();}'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/sort.svg#sort'/></svg></div>");
						$array[] = array("name" => "<div title='Neues Element'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/newElement.svg#newElement'/></svg></div>", "onclick"=>"sendRequest('newElement', {'id':{$questionnaireId}}, '', 'popup', null, null, 'questionnaire');return false;");
						$array[] = array("name" => "<div title='Auswertung'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/results.svg#results'/></svg></div>", "onclick"=>"location.href='" . PATH_URL .  "questionnaire/overallResults/" . $surveyId . "/'");
						$array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$questionnaireId}}, '', 'popup', null, null, 'explorer');return false;");
						$array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$questionnaireId}, 'dialog':true}, '', 'popup', null, null, 'questionnaire');return false;");
					}
					else if(strpos($path, "view") !== false){
						$array[] = array("name" => "<div title='Bearbeiten'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/edit.svg#edit'/></svg></div>", "onclick"=>"location.href='" . PATH_URL .  "questionnaire/edit/" . $surveyId . "/'");
						$array[] = array("name" => "<div title='Anzeigen'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/questionnaire.svg#questionnaire'/></svg></div>", "onclick"=>"location.href='" . PATH_URL .  "questionnaire/index/" . $questionnaireId . "/'");
						$array[] = array("name" => "<div title='Auswertung'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/results.svg#results'/></svg></div>", "onclick"=>"location.href='" . PATH_URL .  "questionnaire/overallResults/" . $surveyId . "/'");
						$array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$questionnaireId}}, '', 'popup', null, null, 'explorer');return false;");
						$array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$questionnaireId}, 'dialog':true}, '', 'popup', null, null, 'questionnaire');return false;");
					}
					else if(strpos($path, "index") !== false){
						$array[] = array("name" => "<div title='Auswertung'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/results.svg#results'/></svg></div>", "onclick"=>"location.href='" . PATH_URL .  "questionnaire/overallResults/" . $surveyId . "/'");
						$array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$questionnaireId}}, '', 'popup', null, null, 'explorer');return false;");
						$array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$questionnaireId}, 'dialog':true}, '', 'popup', null, null, 'questionnaire');return false;");
					}
				}
				$array[] = array("name" => "SEPARATOR");
				return $array;
			}
		}
	}

	public function isActive($id){
		$questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		$start = $questionnaire->get_attribute("QUESTIONNAIRE_START");
		$end = $questionnaire->get_attribute("QUESTIONNAIRE_END");

		if (!preg_match("/^\d{1,2}\.\d{1,2}\.\d{4} \d{2}:\d{2}/isU", $start)) {
			return false;
		}
		if (!preg_match("/^\d{1,2}\.\d{1,2}\.\d{4} \d{2}:\d{2}/isU", $end)) {
			return false;
		}

		//determine current date
		$now = mktime(date("H"), date("i"), 0, date("n"), date("j"), date("Y"));

		$startArray = explode(" ", $start);
		$startDate = explode(".", $startArray[0]);
		$startTime = explode(":", $startArray[1]);
		$start = mktime($startTime[0], $startTime[1], 0, $startDate[1], $startDate[0], $startDate[2]);

		$endArray = explode(" ", $end);
		$endDate = explode(".", $endArray[0]);
		$endTime = explode(":", $endArray[1]);
		$end = mktime($endTime[0], $endTime[1], 0, $endDate[1], $endDate[0], $endDate[2]);

		if ($now > $start && $now < $end) {
				return true;
		}

		return false;
	}
}
?>
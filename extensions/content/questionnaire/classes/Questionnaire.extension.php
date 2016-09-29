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
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/rapidfeedback.svg";
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
				$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
				$env = $obj->get_environment();
				$array = array();
				$user = lms_steam::get_current_user();
				$checkAccessAdmin = $obj->check_access(SANCTION_ALL, $user);
				if ($checkAccessAdmin) {
						$array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$currentObjectID}}, '', 'popup', null, null, 'explorer');return false;");
						$array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$currentObjectID}, 'dialog':true}, '', 'popup', null, null, 'questionnaire');return false;");
					}
				$array[] = array("name" => "SEPARATOR");
				return $array;
			}
		}
	}
}
?>

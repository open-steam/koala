<?php
class Rapidfeedback extends AbstractExtension implements IObjectExtension, IIconBarExtension {

	public function getName() {
		return "rapidfeedback";
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
		return "Fragebogen";
	}

	public function getObjectReadableDescription() {
		return "Mit Fragebögen können Sie mithilfe von verschiedenen Fragetypen Rückmeldungen einholen, Umfragen durchführen oder Lernerfolge messen";
	}

	public function getObjectIconUrl() {
		return $this->getAssetUrl() . "icons/rapidfeedback.png";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/728049/";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Rapidfeedback\Commands\NewRapidfeedbackForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$RapidfeedbackObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$RapidfeedbackType = $RapidfeedbackObject->get_attribute("OBJ_TYPE");
		if ($RapidfeedbackType != "0" && $RapidfeedbackType == "RAPIDFEEDBACK_CONTAINER") {
			return new \Rapidfeedback\Commands\Index();
		}
		return null;
	}

	public function getPriority() {
		return 8;
	}

	public function getIconBarEntries() {
		$array = array();
		$path = strtolower($_SERVER["REQUEST_URI"]);
		if(strpos($path, "rapidfeedback") !== false){
			$pathArray = explode("/", $path);
			$currentObjectID = "";
			for ($count = 0; $count < count($pathArray); $count++) {
					if (intval($pathArray[$count]) !== 0) {
							$currentObjectID = $pathArray[$count];
							break;
					}
			}
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
			$env = $object->get_environment();
			$array[] = array("name" => "<img title=\"Aufwärts\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/arrow_up_white.png\">", "onclick"=>"location.href='" . PATH_URL . "explorer/index/{$env->get_id()}/'");
			return $array;
		}
	}
}
?>

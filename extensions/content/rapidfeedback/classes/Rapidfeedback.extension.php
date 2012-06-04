<?php
class Rapidfeedback extends AbstractExtension implements IObjectExtension{

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
		return "Fragebogen";
	}
	
	public function getObjectIconUrl() {
		return $this->getAssetUrl() . "icons/rapidfeedback.png";
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
}
?>
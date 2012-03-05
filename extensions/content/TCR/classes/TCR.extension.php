<?php
class TCR extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "TCR";
	}

	public function getDesciption() {
		return "Extension for TCR.";
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
		return "Thesen-Kritik-Replik-Verfahren";
	}
	
	public function getObjectReadableDescription() {
		return "Darstellung des Thesen-Kritik-Replik-Verfahrens";
	}
	
	public function getObjectIconUrl() {
		return $this->getAssetUrl() . "icons/tcr.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \TCR\Commands\NewTCRForm();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$TCRObject = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		$TCRType = $TCRObject->get_attribute("OBJ_TYPE");
		if ($TCRType != "0" && $TCRType == "TCR_CONTAINER") {
			return new \TCR\Commands\Index();
		}
		return null;
	}
	
	public function getPriority() {
		return 8;
	}
}
?>
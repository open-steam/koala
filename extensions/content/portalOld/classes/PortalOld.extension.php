<?php
class PortalOld extends AbstractExtension implements IObjectExtension {
	
	public function getName() {
		return "PortalOld";
	}
	
	public function getDesciption() {
		return "Extension for old bid portal conversion.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Jan", "Petertonkoker", "janp@mail.uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "Old Portal Conversion";
	}
	
	public function getObjectReadableDescription() {
		return "";
	}
	
	public function getObjectIconUrl() {
		return "";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return "";
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portalObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
		$portalType = $portalObject->get_attribute("bid:doctype");
		if ($portalType==="portal") {
			return new \PortalOld\Commands\Index();
		}
	}
	
	public function getPriority() {
		return 5;
	}

}
?>
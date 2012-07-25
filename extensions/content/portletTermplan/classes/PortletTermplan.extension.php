<?php
class PortletTermplan extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "PortletTermplan";
	}
	
	public function getDesciption() {
		return "Extension for portlet termplan.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Marcel", "Jakoblew", "mjako@uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "Terminplaner";
	}
	
	public function getObjectReadableDescription() {
		return "Komponente Terminplaner";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portlet.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletTermplan\Commands\CreateNewForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
		
		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="termplan")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletTermplan\Commands\Index();
		}
	}
}
?>
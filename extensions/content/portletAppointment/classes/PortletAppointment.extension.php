<?php
class PortletAppointment extends AbstractExtension implements IObjectExtension {

	public function getName() {
		return "PortletAppointment";
	}
	
	public function getDesciption() {
		return "Extension for portlet appointment.";
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
		return "Termine";
	}
	
	public function getObjectReadableDescription() {
		return "Komponente Termine";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portlet.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletAppointment\Commands\CreateNewForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="appointment")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletAppointment\Commands\Index();
		}
	}
}
?>
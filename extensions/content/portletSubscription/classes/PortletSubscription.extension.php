<?php
class PortletSubscription extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "PortletSubscription";
	}
	
	public function getDesciption() {
		return "Extension for PortletSubscription.";
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
		return "Abonnements";
	}
	
	public function getObjectReadableDescription() {
		return "Auflistung der Änderungen von abonnierten Objekten.";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portlet.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletSubscription\Commands\CreateNewForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
		
		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="subscription")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletSubscription\Commands\Index();
		}
	}
        
        public function getPriority() {
		return 50;
	}
}
?>
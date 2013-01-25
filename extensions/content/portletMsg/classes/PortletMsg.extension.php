<?php
class PortletMsg extends AbstractExtension implements IObjectExtension{
	
	public function getName() {
		return "PortletMsg";
	}
	
	public function getDesciption() {
		return "Extension for portlet msg.";
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
		return "Meldungen";
	}
	
	public function getObjectReadableDescription() {
		return "... für das Verfassen von Meldungen";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portlet.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletMsg\Commands\CreateNewForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		//check if the portlet is valid for this object
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="msg")) return false;
		
		//return command
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletMsg\Commands\Index();
		}
		if ($idRequestObject->getMethod() == "properties") {
			return new \PortletMsg\Commands\Properties();
		}
	}
        
                
        public function getPriority() {
		return 59;
	}
}
?>
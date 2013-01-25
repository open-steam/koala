<?php
class PortletTopic extends AbstractExtension implements IObjectExtension{
	
	public function getName() {
		return "PortletTopic";
	}
	
	public function getDesciption() {
		return "Extension for portlet topic.";
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
		return "Kategorien";
	}
	
	public function getObjectReadableDescription() {
		return "...zum Anlegen von strukturierten Aufzählungen";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portlet.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletTopic\Commands\CreateNewForm();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
		
		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="topic")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletTopic\Commands\Index();
		}
	}
        
                
        public function getPriority() {
		return 57;
	}
}
?>
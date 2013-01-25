<?php
class PortletRss extends AbstractExtension implements IObjectExtension{
	
	public function getName() {
		return "PortletRss";
	}
	
	public function getDesciption() {
		return "Extension for portlet poll.";
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
		return "RSS-Feed-Reader";
	}
	
	public function getObjectReadableDescription() {
		return "Komponente RSS-Feed";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portlet.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletRss\Commands\CreateNewForm();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
		
		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="rss")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletRss\Commands\Index();
		}
	}
        
                
        public function getPriority() {
		return 54;
	}
}
?>
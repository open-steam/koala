<?php
class PortletPoll extends AbstractExtension implements IObjectExtension{
	
	public function getName() {
		return "PortletPoll";
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
		return "Abstimmung";
	}
	
	public function getObjectReadableDescription() {
		return "... zur Abstimmung über Stichpunkte";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portlet.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletPoll\Commands\CreateNewForm();
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
		
		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="poll")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletPoll\Commands\Index();
		}
	}
        
                
        public function getPriority() {
		return 53;
	}
}
?>
<?php
class PortletSubscriptionOld extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "PortletSubscription";
	}
	
	public function getDesciption() {
		return "Extension for portlet subscription.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Christoph", "Sens", "mjako@uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "Termine abonnieren";
	}
	
	public function getObjectReadableDescription() {
		return "Komponente Terminsubscription";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portlet.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletSubscription\Commands\CreateNewForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
                //var_dump($portletObject);
                //echo "------------------<br><br>";
		$portletType = $portletObject->get_attribute("bid:portlet");
		
                //var_dump("PT: ".$portletType);
                if (!($portletType==="subscription")) return false;
                
                if ($idRequestObject->getMethod() == "view") {
                        
			return new PortletSubscription\Commands\Index();
		}
	}
}
?>
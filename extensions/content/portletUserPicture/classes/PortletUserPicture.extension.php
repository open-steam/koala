<?php
class PortletUserPicture extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "PortletUserPicture";
	}
	
	public function getDesciption() {
		return "Extension for PortletUserPicture.";
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
		return "Eigenes Benutzerbild";
	}
	
	public function getObjectReadableDescription() {
		return "Benutzerbild für den persönlichen Schreibtisch.";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/portlet.png";
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletUserPicture\Commands\CreateNewForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
		
		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="userpicture")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletUserPicture\Commands\Index();
		}
	}
        
                
        public function getPriority() {
		return 43;
	}
}
?>
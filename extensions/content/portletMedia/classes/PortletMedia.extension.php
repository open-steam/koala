<?php
class PortletMedia extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "PortletMedia";
	}

	public function getDesciption() {
		return "Extension for portlet media.";
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
		return "Medien";
	}

	public function getObjectReadableDescription() {
		return "Zum Einbinden von Fotos, Audio oder Video";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/play.svg";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/729003/";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletMedia\Commands\CreateNewForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());

		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="media")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletMedia\Commands\Index();
		}
	}


        public function getPriority() {
		return 55;
	}
}
?>

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
		return "Linkliste";
	}

	public function getObjectReadableDescription() {
		return "Dient dem Anlegen einer Liste von externen und/oder internen Links";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/explorer.svg";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/640966/";
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

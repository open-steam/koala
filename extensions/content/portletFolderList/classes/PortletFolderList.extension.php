<?php
class PortletFolderList extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "PortletFolderList";
	}

	public function getDesciption() {
		return "Extension for PortletFolderList.";
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
		return "Ordnerinhalt";
	}

	public function getObjectReadableDescription() {
		return "Listet alle Objekte innerhalb eines bestimmten Ordners auf";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/folder.svg";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/1204992/";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletFolderList\Commands\CreateNewForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());

		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="folderlist")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletFolderList\Commands\Index();
		}
	}

  public function getPriority() {
		return 50;
	}
}
?>

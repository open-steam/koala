<?php
class PortletBookmarks extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "PortletBookmarks";
	}

	public function getDesciption() {
		return "Extension for PortletBookmarks.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Christoph", "Sens", "csens@mail.uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Eigene Lesezeichen";
	}

	public function getObjectReadableDescription() {
		return "Zeigt die eigenen Lesezeichen an";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/bookmark.png";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletBookmarks\Commands\CreateNewForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());

		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="bookmarks")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletBookmarks\Commands\Index();
		}
	}

	public function getPriority() {
		return 41;
	}
}
?>

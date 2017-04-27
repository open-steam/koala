<?php
class PortletSlideShow extends AbstractExtension implements IObjectExtension{

	public function getName() {
		return "PortletSlideShow";
	}

	public function getDesciption() {
		return "Extension for PortletSlideShow.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Andreas", "Schultz", "schultza@mail.uni-paderborn.de");
		return $result;
	}

	public function getObjectReadableName() {
		return "Diashow - Hilfelink noch anpassen";
	}

	public function getObjectReadableDescription() {
		return "Zeigt den Inhalt einer Galerie im Portlet als Diashow an";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/gallery.svg";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/1204992/";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \PortletSlideShow\Commands\CreateNewForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$portletObject = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());

		$portletType = $portletObject->get_attribute("bid:portlet");
		if (!($portletType==="slideshow")) return false;
		if ($idRequestObject->getMethod() == "view") {
			return new \PortletSlideShow\Commands\Index();
		}
	}

  public function getPriority() {
		return 50;
	}
}
?>

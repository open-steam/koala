<?php
class WebLinkObject extends AbstractExtension implements IObjectExtension {

	public function getName() {
		return "WebLinkObject";
	}

	public function getDesciption() {
		return "Extension for web link object.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}

	public function getId() {
		return "Explorer";
	}

	public function getObjectReadableName() {
		return "Weblink";
	}

	public function getObjectReadableDescription() {
		return "Weblinks ermöglichen es Ihnen, auf beliebige Webseiten zu verweisen";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/www.svg";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/640380/";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Explorer\Commands\NewWebLinkForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		if ($object instanceof steam_docextern) {
			return new \Explorer\Commands\ViewDocument();
		}
	}

	public function getPriority() {
		return 8;
	}
}
?>

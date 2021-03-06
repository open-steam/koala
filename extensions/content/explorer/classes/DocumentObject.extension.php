<?php
class DocumentObject extends AbstractExtension implements IObjectExtension {

	public function getName() {
		return "DocumentObject";
	}

	public function getDesciption() {
		return "Extension for document object.";
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
		return "Datei hochladen";
	}

	public function getObjectReadableDescription() {
		return "Sie können beliebige Dateien von Ihrem lokalen Computer auf den Server übertragen";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/generic.svg";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/640341/";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Explorer\Commands\NewDocumentForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		$object = steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $idRequestObject->getId() );
		if ($object instanceof steam_document) {
			return new \Explorer\Commands\ViewDocument();
		}
	}

	public function getPriority() {
		return 8;
	}
}
?>

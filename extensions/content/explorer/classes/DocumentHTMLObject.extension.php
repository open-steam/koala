<?php
class DocumentHTMLObject extends AbstractExtension implements IObjectExtension {

	public function getName() {
		return "DocumentHTMLObject";
	}

	public function getDesciption() {
		return "Extension for html document object.";
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
		return "Text";
	}

	public function getObjectReadableDescription() {
		return "Erstellen und Bearbeiten Sie Texte direkt im Browser";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/text.svg";
	}

	public function getHelpUrl(){
		return "https://bid.lspb.de/explorer/ViewDocument/640373/";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Explorer\Commands\NewDocumentHTMLForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		return null;
	}

	public function getPriority() {
		return 10;
	}
}
?>

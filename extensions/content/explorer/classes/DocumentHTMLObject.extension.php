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
		return "Sie können Texte mit einer einfachen Textverarbeitung auf dem Server erstellen und bearbeiten.";
	}
	
	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/text.png";
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
<?php
class DocumentPlainObject extends AbstractExtension implements IObjectExtension {

	public function getName() {
		return "DocumentPlainObject";
	}

	public function getDesciption() {
		return "Extension for plain document object.";
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
		return "Text(deprecated)";
	}

	public function getObjectReadableDescription() {
		return "Sie können Texte eingeben und ähnlich wie in der Wikipedia formatieren.";
	}

	public function getObjectIconUrl() {
		return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/text.svg";
	}

	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return new \Explorer\Commands\NewDocumentPlainForm();
	}

	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		return null;
	}

	public function getPriority() {
		return 9;
	}
}
?>

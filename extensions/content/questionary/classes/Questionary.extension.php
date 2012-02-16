<?php
class Questionary extends AbstractExtension implements IObjectExtension {
	
	public function getName() {
		return "Questionary";
	}
	
	public function getDesciption() {
		return "Extension for questionary view.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Niroshan", "Thillainathan", "n.thillainathan@campus.uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return null;
	}
	
	public function getObjectReadableDescription() {
		return null;
	}
	
	public function getObjectIconUrl() {
		return null;
	}
	
	public function getCreateNewCommand(IdRequestObject $idEnvironment) {
		return null;
	}
	
	public function getCommandByObjectId(IdRequestObject $idRequestObject){
		
	}
}
?>
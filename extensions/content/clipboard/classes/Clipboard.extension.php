<?php 
class Clipboard extends AbstractExtension implements IObjectModelExtension {
	
	public function getName() {
		return "Clipboard.";
	}
	
	public function getDesciption() {
		return "Extension for Clipboard.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Christoph", "Sens", "csens@mail.uni-paderborn.de");
		return $result;
	}
	
	public function getObjectModels() {
		$objectModels = array();
		$objectModels[] = "\Clipboard\Model\Clipboard";
		return $objectModels;
	}
}
?>
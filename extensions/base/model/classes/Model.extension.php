<?php
class Model extends AbstractExtension {
	
	public function getName() {
		return "Model";
	}
	
	public function getDesciption() {
		return "Extension for model view.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
}
?>
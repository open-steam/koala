<?php
class Upload extends AbstractExtension {
	
	public function getName() {
		return "Upload";
	}
	
	public function getDesciption() {
		return "Extension for upload handling.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getDefaultCommandName($urlNamespace) {
		return "To";
	}
}
?>
<?php
class Download extends AbstractExtension {
	
	public function getName() {
		return "Download";
	}
	
	public function getDesciption() {
		return "Extension for download handling.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getPriority() {
		return -10;
	}
}
?>
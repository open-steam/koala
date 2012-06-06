<?php
class Rest extends AbstractExtension {
	
	public function getName() {
		return "Rest";
	}
	
	public function getDesciption() {
		return "Extension for the Rest API.";
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
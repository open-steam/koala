<?php
class Debug extends AbstractExtension {
	
	public function getName() {
		return "Debug";
	}
	
	public function getDesciption() {
		return "Extension for debugging.";
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
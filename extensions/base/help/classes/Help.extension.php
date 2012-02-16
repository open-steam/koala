<?php
class Help extends AbstractExtension {
	
	public function getName() {
		return "Help";
	}
	
	public function getDesciption() {
		return "Extension for help view.";
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
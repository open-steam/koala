<?php
class Imprint extends AbstractExtension {
	
	public function getName() {
		return "Imprint";
	}
	
	public function getDesciption() {
		return "Extension for imprint view.";
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
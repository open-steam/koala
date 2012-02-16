<?php
class Frame extends AbstractExtension {
	
	public function getName() {
		return "Frame";
	}
	
	public function getDesciption() {
		return "Extension for frame handling.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getUrlNamespaces() {
		return array("frame", "asset");
	}
}
?>
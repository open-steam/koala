<?php
class Startpage extends AbstractExtension {
	
	public function getName() {
		return "Startpage";
	}
	
	public function getDesciption() {
		return "Extension for startpage.";
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
		return array("");
	}
}
?>
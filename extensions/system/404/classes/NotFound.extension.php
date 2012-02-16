<?php
class NotFound extends AbstractExtension {
	
	public function getName() {
		return "NotFound";
	}
	
	public function getDesciption() {
		return "Extension for 404 handling.";
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
		return array(strtolower($this->getName()), "404");
	}
}
?>
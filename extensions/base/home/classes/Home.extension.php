<?php 
class Home extends AbstractExtension {
	
	public function getName() {
		return "Home";
	}
	
	public function getDesciption() {
		return "Extension for home.";
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
		return array(strtolower($this->getName()));
	}
}
?>
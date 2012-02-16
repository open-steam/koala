<?php
class Admin extends AbstractExtension {
	
	public function getName() {
		return "Admin";
	}
	
	public function getDesciption() {
		return "Extension for admin view.";
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
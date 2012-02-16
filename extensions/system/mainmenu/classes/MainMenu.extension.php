<?php
class MainMenu extends AbstractExtension implements IUiExtension {
	
	public function getName() {
		return "MainMenu";
	}
	
	public function getDesciption() {
		return "Extension for the main menu.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getDepanding() {
		$depanding = array();
		$depanding[] = "Portal";
		$depandung[] = "Application";
		return $depanding;
	}
}
?>
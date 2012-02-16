<?php
class Widgets extends AbstractExtension{
	
	public function getName() {
		return "Widgets";
	}
	
	public function getDesciption() {
		return "Extension for platform wide widgets.";
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
<?php
class Semester extends Extension implements IStructureExtension {
	
	public function getName() {
		return "Semester";
	}
	
	public function getDesciption() {
		return "Extension for the semester context.";
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
<?php

class Semester extends AbstractExtension  {
	
	public function getName() {
		return "Semester";
	}
	
	public function getDesciption() {
		return "Extension for semester view.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Niroshan", "Thillainathan", "n.thillainathan@campus.uni-paderborn.de");
		return $result;
	}
}
?>
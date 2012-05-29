<?php

class Map extends AbstractExtension {
	
	public function getName() {
		return "Map";
	}
	
	public function getDesciption() {
		return "";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Christoph", "Sens", "csens@mail.upb.de");
		return $result;
	}
}
?>
<?php

class Favorite extends AbstractExtension {
	
	public function getName() {
		return "Favorite";
	}
	
	public function getDesciption() {
		return "Extension to manage your favorites";
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
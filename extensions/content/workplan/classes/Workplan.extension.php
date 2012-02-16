<?php
class Workplan extends AbstractExtension {
	public function getName() {
		return "Workplan";
	}
	
	public function getDesciption() {
		return "Extension for workplan view.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Jan", "Petertonkoker", "janp@mail.uni-paderborn.de");
		return $result;
	}
}
?>
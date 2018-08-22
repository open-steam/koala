<?php
class SystemError extends AbstractExtension {
	
	public function getName() {
		return "Error";
	}
	
	public function getDesciption() {
		return "Extension for error handling.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function getDefaultCommandName($urlNamespace) {
		return "Report";
	}
}
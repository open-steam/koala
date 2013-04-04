<?php
class Terms extends AbstractExtension {

	public function getName() {
		return "Terms";
	}

	public function getDesciption() {
		return "Extension for Terms of Use.";
	}

	public function getVersion() {
		return "v1.0.0";
	}

	public function getAuthors() {
		$result = array();
		$result[] = new Person("Petertonkoker", "Jan", "janp@mail.uni-paderborn.de");
		return $result;
	}
	
	public function getUrlNamespaces() {
		return array(strtolower($this->getName()));
	}
	
	public function getPriority() {
		return 8;
	}
}
?>
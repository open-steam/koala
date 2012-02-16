<?php
class Weblog extends AbstractExtension {
	
	public function getName() {
		return "Weblog";
	}
	
	public function getDesciption() {
		return "Extension for weblog view.";
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
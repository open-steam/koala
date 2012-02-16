<?php
class Webdav extends AbstractExtension {
	
	public function getName() {
		return "Webdav";
	}
	
	public function getDesciption() {
		return "Extension for webdav.";
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
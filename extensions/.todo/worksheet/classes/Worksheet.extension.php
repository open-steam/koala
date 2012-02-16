<?php
class Worksheet extends Extension implements IUrlExtension {
	
	public function getName() {
		return "Worksheet";
	}
	
	public function getDesciption() {
		return "Extension for worksheets.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Tobias", "Kempkensteffen", "tobias.kempkensteffen@gmail.com");
		return $result;
	}
	
	public function canHandleUrl(UrlRequestObject $urlRequestObject) {
		return strtolower($urlRequestObject->getFirstPathElement()) == "worksheet";
	}
	
	public function getHtmlForUrl(UrlRequestObject $urlRequestObject) {
		$obj = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), 82);
		var_dump($obj->get_name());
		return "God mode on";
	}
}
?>
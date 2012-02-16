<?php
class Workplan extends Extension implements IUrlExtension {
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
	
	public function canHandleUrl(UrlRequestObject $urlRequestObject) {
		return strtolower($urlRequestObject->getFirstPathElement()) == "workplan";
	}
	
	public function getHtmlForUrl(UrlRequestObject $urlRequestObject) {
				
		$this->addCSS();
		$this->addJS();
		
		$path = $urlRequestObject->getPath();
		if (isset($path[1])) {
			return $path[1];
		}
		return "Hund";//"<img src=\"" . $this->getAssetUrl() . "icons/bullet_arrow_down.png\" />";
	}
}
?>
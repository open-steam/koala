<?php
class SspLoopTag extends AbstractExtension implements ITagExtension{
	
	public function getName() {
		return "Wave";
	}
	
	public function getDesciption() {
		return "Extension for wave-cms.";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
		return $result;
	}
	
	public function processContent($html, $page) {
		return $html;
	}
	
}
?>
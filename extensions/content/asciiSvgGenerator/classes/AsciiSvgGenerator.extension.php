<?php
class AsciiSvgGenerator extends AbstractExtension{
	
	public function getName() {
		return "AsciiSvgGenerator";
	}
	
	public function getDesciption() {
		return "AsciiSvgGenerator description";
	}
	
	public function getVersion() {
		return "v1.0.0";
	}
	
	public function getAuthors() {
		$result = array();
		$result[] = new Person("Marcel", "Jakoblew", "mjako@uni-paderborn.de");
		return $result;
	}
	
	public function getObjectReadableName() {
		return "AsciiSvgGenerator";
	}
	
	public function getObjectReadableDescription() {
		return "AsciiSvgGenerator";
	}
	
	public function getIconBarEntries() {

	}
	
	public function getPriority() {
		return 42;
	}

}
?>
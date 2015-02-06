<?php
namespace Widgets;

class StaticDataProvider implements IDataProvider {
	
	private $data;
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	public function getData($object) {
		return $this->data;
	}
	
	public function getUpdateCode($object, $elementId, $successMethod = "") {
		return "";
	}
	
	public function isChangeable($object) {
		return false;
	}
}
?>
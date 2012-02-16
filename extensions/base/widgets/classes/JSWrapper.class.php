<?php
namespace Widgets;

class JSWrapper extends Widget {
	
	private $jsCode;
	
	public function setJs($jsCode) {
		$this->jsCode = $jsCode;
	}
	
	public function getJsCode() {
		$result = array();
		$result[get_class($this)] = $this->jsCode;
		$result = array_merge($result, parent::getJsCode());
		return $result;
	}
	
	public function getHtml() {
		return "";
	}
}
?>
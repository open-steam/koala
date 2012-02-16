<?php
namespace Widgets;

class RawHtml extends Widget {
	
	private $html = "";
	private $js = "";
	private $css = "";
	
	public function setHtml($html) {
		$this->html= $html;
	}
	
	public function setJs($js) {
		$this->js = $js;
	}
	
	public function setCss($css) {
		$this->css = $css;
	}
	
	public function getJsCode() {
		$result = parent::getJsCode();
		if ($this->js != "") {
			if (is_array($this->js)) {
				$result = array_merge($result, $this->js);
			} else {
				$result[] = $this->js;
			}
		}
		return $result;
	}
	
	public function getCssStyle() {
		$result = parent::getCssStyle();
		if ($this->css != "") {
			if (is_array($this->css)) {
				$result = array_merge($result, $this->css);
			} else {
				$result[] = $this->css;
			}
		}
		return $result;
	}
	
	public function getHtml() {
		$this->getContent()->setVariable("CONTENT", $this->html);
		return $this->getContent()->get();
	}
}
?>
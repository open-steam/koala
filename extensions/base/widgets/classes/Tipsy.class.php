<?php
namespace Widgets;

class Tipsy extends Widget {
	private $elementId;
	private $html;
	
	public function setElementId($elementId) {
		$this->elementId = $elementId;
	}
	
	public function setHtml($html) {
		$this->html = $html;
	}
	
	
	public function getHtml() {
		$this->getContent()->setVariable("ELEMENT_ID", $this->elementId);
		$this->getContent()->setVariable("TIPSY_TEXT", $this->html);
		
		return $this->getContent()->get();
	}
}
?>
<?php
namespace Widgets;

class Label extends Widget {
	private $value;
	

	public function setLabel($label) {
		$this->value = $value;
	}
	
	
	
	
	public function getHtml() {
		$this->getContent()->setVariable("LABEL", $this->value);
		
		return $this->getContent()->get();
	}
}

?>
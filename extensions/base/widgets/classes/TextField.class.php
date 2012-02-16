<?php
namespace Widgets;

class TextField extends Widget {
	private $label;
	private $id;
	private $value = "";
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function getHtml() {
		$this->id = rand();
		if (isset($this->label)) {
			$this->getContent()->setVariable("LABEL", $this->label);
		}
		$this->getContent()->setVariable("ID", $this->id);
		$this->getContent()->setVariable("VALUE", $this->value);
		return $this->getContent()->get();
	}
}
?>
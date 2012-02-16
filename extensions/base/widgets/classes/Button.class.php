<?php
namespace Widgets;

class Button extends Widget {
	private $contentProvider;
	private $id;
	private $objectId;
	
	private $label="unnamed button";
	
		
	public function setLabel($label) {
		$this->label = $label;
	}
	
	
	public function setContentProvider($contentProvider) {
		$this->contentProvider = $contentProvider;
	}
	
	
	public function getHtml() {
		$this->id = rand();
		$this->getContent()->setVariable("ID", $this->id);
		$this->getContent()->setVariable("VALUE", $this->label);
		$this->getContent()->setVariable("NAME", $this->id);
		
		return $this->getContent()->get();
	}
}
?>
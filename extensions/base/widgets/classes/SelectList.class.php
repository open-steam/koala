<?php
namespace Widgets;

class SelectList extends Widget {
	private $contentProvider;
	private $id;
	private $objectId;
	
	private $label;
	
	private $options = array();

	
	public function addOption($option, $description, $defaultValue=false) {
		$optionArray =  array();
		$optionArray["option"] = $option;
		$optionArray["description"] = $description;
		$optionArray["defaultValue"] = $defaultValue;
		$this->options[] = $optionArray;
	}
	
	public function setLabel($label){
		$this->label = $label;
	}
	
	public function setContentProvider($contentProvider) {
		$this->contentProvider = $contentProvider;
	}
	
	
	public function getHtml() {
		$this->id = rand();
		$this->getContent()->setVariable("ID", $this->id);
		$this->getContent()->setVariable("NAME", $this->id);
		$this->getContent()->setVariable("LABEL", $this->label);
		
		foreach ($this->options as $option) {
			$this->getContent()->setCurrentBlock("BLOCK_LIST_OPTIONS");
			$this->getContent()->setVariable("REFERENCE", $option["option"]);
			$this->getContent()->setVariable("DESCRIPTION", $option["description"]);
			$this->getContent()->parseCurrentBlock();
		}
		return $this->getContent()->get();
	}
}
?>
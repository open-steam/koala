<?php
namespace Widgets;

class RadioButton extends Widget {
	private $contentProvider;
	private $id;
	private $objectId;
	
	private $options;
	private $data;
	private $label;
	private $defaultChecked;
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function setData($data) {
		$this->data = $data;
		if (is_int($data)) {
			$this->objectId = $data;
		} else {
			$this->objectId = $data->get_id();
		}
	}
	
	public function setOptions($options) {
		$this->options = $options;
	}
	
	
	public function setContentProvider($contentProvider) {
		$this->contentProvider = $contentProvider;
	}
	
	public function setDefaultChecked($defaultChecked) {
		$this->defaultChecked = $defaultChecked;
	}
	
	public function getHtml() {
		$this->id = rand();
		if (isset($this->label)) {
			$this->getContent()->setVariable("LABEL", $this->label);
		}
		$currentValue = $this->contentProvider->getData($this->data);
		$isValidValue = false;
		foreach ($this->options as $option) {
			if ($currentValue === $option["value"]) {
				$isValidValue = true;
				break;
			}
		}
		if (!$isValidValue) {
			if (isset($this->defaultChecked)) {
				$currentValue = $this->defaultChecked;
			} else {
				$currentValue = $this->options[0]["value"];
			}
		}
                
               
                
                
		$this->getContent()->setVariable("ID", $this->id);
		foreach ($this->options as $option) {
			$this->getContent()->setCurrentBlock("BLOCK_RADIOFIELD");
                        
                        //write sanction
                        if ($this->contentProvider && !$this->contentProvider->isChangeable($this->data)) {
                                $this->getContent()->setVariable("READONLY", "disabled");
                        }
                        
                        
			$this->getContent()->setVariable("ID", $this->id);
			$this->getContent()->setVariable("RADIONAME", $this->id);
			$this->getContent()->setVariable("RADIOVALUE", $option["value"]);
			$this->getContent()->setVariable("RADIOLABEL", $option["name"]);
			$this->getContent()->setVariable("ONCHANGE", $this->contentProvider->getUpdateCode($this->data, $this->id . "_" . $option["value"]));
			if ($currentValue === $option["value"]) {
				$this->getContent()->setVariable("CHECKED", "checked");
			}
			$this->getContent()->parseCurrentBlock();
		}
		
		return $this->getContent()->get();
	}
}
?>
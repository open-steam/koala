<?php
namespace Widgets;

class ComboBox extends Widget {
	private $contentProvider;
	private $id;
	private $objectId;
	
	private $options;
	private $defaultValue;
	private $data;
	private $label;
        private $labelWidth;
	private $size = 1;
        private $onchange;
	
	public function setLabel($label) {
		$this->label = $label;
	}
        
        public function setLabelWidth($width) {
		$this->labelWidth = $width;
	}
	
	public function setSize($size) {
		$this->size = $size;
	}
	
	public function setData($data) {
		$this->data = $data;
		if (is_int($data)) {
			$this->objectId = $data;
		} else {
			$this->objectId = $data->get_id();
		}
	}
        
        public function setOnChange($js) {
            $this->onchange = $js;
        }
	
	public function setOptions($options) {
		$this->options = $options;
	}
	
	
	public function setContentProvider($contentProvider) {
		$this->contentProvider = $contentProvider;
	}
	
	public function getHtml() {
		$this->id = rand();
		if (isset($this->label)) {
			$this->getContent()->setVariable("LABEL", $this->label);
		}
                if (isset($this->labelWidth)) {
			$this->getContent()->setVariable("LABEL_STYLE", "style=\"width:{$this->labelWidth}px\"");
		}
		if ($this->data) {
			$currentValue = $this->contentProvider->getData($this->data);
		}
                
		$this->getContent()->setVariable("ID", $this->id);
		$this->getContent()->setVariable("ID2", $this->id);
		$this->getContent()->setVariable("ID3", $this->id);
		$this->getContent()->setVariable("SIZE", $this->size);
		$this->getContent()->setVariable("COMBONAME", $this->id);
		if ($this->data) {
			$this->getContent()->setVariable("ONCHANGE", $this->contentProvider->getUpdateCode($this->data, $this->id . "_select"));
		}
                
                if ($this->onchange) {
                    $this->getContent()->setVariable("ONCHANGE", $this->onchange);
                }
               
		if (isset($this->options)) {
			foreach ($this->options as $option) {
				$this->getContent()->setCurrentBlock("BLOCK_OPTION");
				$this->getContent()->setVariable("ID", $this->id);
				$this->getContent()->setVariable("OPTIONVALUE", $option["value"]);
				$this->getContent()->setVariable("OPTIONLABEL", $option["name"]);
				if (isset($currentValue) && $currentValue === $option["value"]) {
					$this->getContent()->setVariable("SELECTED", "selected");
				}
				$this->getContent()->parseCurrentBlock();
			}
		}
		
		return $this->getContent()->get();
	}
}
?>
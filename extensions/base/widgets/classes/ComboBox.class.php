<?php
namespace Widgets;

class ComboBox extends Widget {

    private $id;
    private $label;
    private $data;
    private $contentProvider;
    private $options;
    private $labelWidth;
    private $size = 1;
    private $onchange="$(this).addClass('changed');";

    public function setId($id){
        $this->id = "id_".$id."_combobox";
    }

    public function setLabel($label) {
			$this->label = $label . ":";
    }

    /**
     * provedes the current calue to be preselected
     * @param type $data
     */
    public function setData($data) {
			$this->data = $data;
    }

    public function setContentProvider($contentProvider) {
			$this->contentProvider = $contentProvider;
    }

    public function setOptions($options) {
			$this->options = $options;
    }

    public function setLabelWidth($width) {
			$this->labelWidth = $width;
    }

    public function setSize($size) {
			$this->size = $size;
    }

    public function setOnChange($onChange) {
      $this->onchange = $onChange;
    }

    public function getHtml() {
			if(!isset($this->id)){
        $this->setId(rand());
      }

			if (isset($this->label)) {
				$this->getContent()->setVariable("LABEL", $this->label);
			}

			if (isset($this->labelWidth)) {
				$this->getContent()->setVariable("LABEL_STYLE", "style=\"width:{$this->labelWidth}px\"");
			}

			if ($this->data) {
      	$currentValue = $this->contentProvider->getData($this->data);
			}

      //write sanction
      if ($this->contentProvider && !$this->contentProvider->isChangeable($this->data)) {
        $this->getContent()->setVariable("READONLY", "disabled");
      }

      if ($this->contentProvider) {
        $this->getContent()->setVariable("SAVE_FUNCTION", $this->contentProvider->getUpdateCode($this->data, $this->id));
      }


			$this->getContent()->setVariable("ID", $this->id);
			$this->getContent()->setVariable("ID2", $this->id);
			$this->getContent()->setVariable("ID3", $this->id);
			$this->getContent()->setVariable("SIZE", $this->size);
			$this->getContent()->setVariable("COMBONAME", $this->id);


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

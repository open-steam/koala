<?php

namespace Widgets;

class RadioButton extends Widget {

    private $id;
    private $data;
    private $contentProvider;
    private $readOnly;
    private $label;
    private $options;
    private $defaultChecked;
    private $type = "vertical";
    private $currentValue;
    private $objectId;
    private $autosave = false;

    public function setId($id){
        $this->id = "id_".$id."_radioButton";
    }

    public function setData($data) {
        $this->data = $data;
        if (is_int($data)) {
            $this->objectId = $data;
        } else {
            $this->objectId = $data->get_id();
        }
    }

    public function setContentProvider($contentProvider) {
        $this->contentProvider = $contentProvider;
    }

    public function setReadOnly($ro) {
        $this->readOnly = $ro;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function setOptions($options) {
        $this->options = $options;
    }

    public function setDefaultChecked($defaultChecked) {
        $this->defaultChecked = $defaultChecked;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function setCurrentValue($cv) {
        $this->currentValue = $cv;
    }

    public function setObjectId($objectId) {
        $this->objectId = $objectId;
    }

    public function setAutosave($autosave) {
        $this->autosave = $autosave;
    }

    public function getHtml() {
        if(!isset($this->id)){
            $this->setId(rand());
        }

        $this->getContent()->setVariable("ID2", $this->id);
        if ($this->type == "vertical") {
            $this->getContent()->setCurrentBlock("BLOCK_RADIOBUTTON_VERTICAL");

            if (isset($this->label)) {
                $this->getContent()->setVariable("LABEL", $this->label);
            }
            if (isset($this->contentProvider)) {
                $currentValue = $this->contentProvider->getData($this->data);
            } else {
                $currentValue = $this->currentValue;
            }
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
                }  else if(isset($this->readOnly) && $this->readOnly){
                    $this->getContent()->setVariable("READONLY", "disabled");
                }

                $this->getContent()->setVariable("ID", $this->id);
                $this->getContent()->setVariable("RADIONAME", $this->id);
                $this->getContent()->setVariable("RADIOVALUE", (isset($option["value"])? $option["value"] : ""));
                $this->getContent()->setVariable("RADIOLABEL", (isset($option["name"])? $option["name"] : ""));
                $this->getContent()->setVariable("RADIOCLASS", (isset($option["class"])? $option["class"] : ""));
                if (isset($this->contentProvider)) {
                    $this->getContent()->setCurrentBlock();
                    $this->getContent()->setVariable("ONCHANGE", "$(this).addClass('changed');".$this->contentProvider->getUpdateCode($this->data, $this->id));
                    $this->getContent()->setCurrentBlock("BLOCK_RADIOFIELD");
                }
                if ($currentValue === $option["value"]) {
                    $this->getContent()->setVariable("CHECKED", "checked");
                }

                if ($this->autosave){
                  $this->getContent()->setVariable("AUTOSAVE", 1);
                }
                else{
                  $this->getContent()->setVariable("AUTOSAVE", 0);
                }

                $this->getContent()->parseCurrentBlock();
            }
        } else {
            $this->getContent()->setCurrentBlock("BLOCK_RADIOBUTTON_HORIZONTAL");

            if (isset($this->label)) {
                $this->getContent()->setVariable("LABEL_HORIZONTAL", $this->label);
            }
            if (isset($this->contentProvider)) {
                $currentValue = $this->contentProvider->getData($this->data);
            } else {
                $currentValue = $this->currentValue;
            }
            $isValidValue = false;
            foreach ($this->options as $option) {
                if ($currentValue == $option["value"]) {
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

            $this->getContent()->setVariable("ID_HORIZONTAL", $this->id);
            foreach ($this->options as $option) {
                $this->getContent()->setCurrentBlock("BLOCK_RADIOFIELD_HORIZONTAL");

                //write sanction
                if ($this->contentProvider && !$this->contentProvider->isChangeable($this->data)) {
                    $this->getContent()->setVariable("READONLY_HORIZONTAL", "disabled");
                }else if(isset($this->readOnly) && $this->readOnly){
                    $this->getContent()->setVariable("READONLY_HORIZONTAL", "disabled");
                }

                $this->getContent()->setVariable("ID_HORIZONTAL", $this->id);
                $this->getContent()->setVariable("RADIONAME_HORIZONTAL", $this->id);
                $this->getContent()->setVariable("RADIOVALUE_HORIZONTAL", $option["value"]);
                $this->getContent()->setVariable("RADIOLABEL_HORIZONTAL", $option["name"]);
                $this->getContent()->setVariable("RADIOCLASS_HORIZONTAL", $option["class"]);
                if (isset($this->contentProvider)) {
                    $this->getContent()->setCurrentBlock();
                    $this->getContent()->setVariable("ONCHANGE", $this->contentProvider->getUpdateCode($this->data, $this->id));
                    $this->getContent()->setCurrentBlock("BLOCK_RADIOFIELD");
                }
                if ($currentValue == $option["value"]) {
                    $this->getContent()->setCurrentBlock();
                    $this->getContent()->setVariable("CHECKED_HORIZONTAL", "checked");
                    $this->getContent()->setCurrentBlock("BLOCK_RADIOFIELD");
                }

                if ($this->autosave){
                  $this->getContent()->setVariable("AUTOSAVE", 1);
                }
                else{
                  $this->getContent()->setVariable("AUTOSAVE", 0);
                }

                $this->getContent()->parse("BLOCK_RADIOFIELD_HORIZONTAL");
            }
            $this->getContent()->parse("BLOCK_RADIOBUTTON_HORIZONTAL");
        }
        return $this->getContent()->get();
    }

}

?>

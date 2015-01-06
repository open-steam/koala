<?php

namespace Widgets;

class Checkbox extends Widget {

    private $id;
    private $data;
    private $contentProvider;
    private $readOnly;
    private $name = "";
    private $label;
    
    private $checkedValue = true;
    private $uncheckedValue = false;
    

    public function setId($id){
        $this->id = "id_".$id."_checkbox";
    }
    
    public function setData($data) {
        $this->data = $data;
    }
    
    public function setContentProvider($contentProvider) {
        $this->contentProvider = $contentProvider;
    }
    

    public function setReadOnly($readOnly) {
        $this->readOnly = $readOnly;
    }
    public function setName($name) {
        $this->name = $name;
    }
    
    public function setLabel($label) {
        $this->label = $label;
    }

    public function setCheckedValue($checkedValue) {
        $this->checkedValue = $checkedValue;
    }

    public function setUncheckedValue($uncheckedValue) {
        $this->uncheckedValue = $uncheckedValue;
    }

    public function getHtml() {
        if(!isset($this->id)){
            $this->setId(rand());
        }
        $this->getContent()->setVariable("ID", $this->id);
        
        
        if (isset($this->label)) {
            $this->getContent()->setVariable("LABEL", $this->label);
        }
        
        
        if (isset($this->data) && isset($this->contentProvider)) {
            $currentValue = $this->contentProvider->getData($this->data);
            $this->getContent()->setVariable("SAVE_FUNCTION", $this->contentProvider->getUpdateCode($this->data, $this->id));
        } else {
            $currentValue = "";
        }
        
        
        if ($currentValue === $this->checkedValue) {
            $this->getContent()->setVariable("CHECKED", "checked");
        } else {
            $this->getContent()->setVariable("CHECKED", "");
        }


        if ($this->contentProvider && !$this->contentProvider->isChangeable($this->data)) {
            $this->getContent()->setVariable("READONLY", "disabled");
        }else if(isset($this->readOnly) && $this->readOnly){
            $this->getContent()->setVariable("READONLY", "disabled");
        }

        $this->getContent()->setVariable("CHECKEDVALUE", $this->checkedValue);
        $this->getContent()->setVariable("UNCHECKEDVALUE", $this->uncheckedValue);

        $this->getContent()->setVariable("NAME", $this->name);
        return $this->getContent()->get();
    }
}
?>
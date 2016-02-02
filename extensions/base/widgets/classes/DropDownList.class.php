<?php

namespace Widgets;

class DropDownList extends Widget {
    
    private $id;
    private $name = "";
    private $label;
    private $data = array();
    private $focus = false;
    private $readOnly = false;
    private $labelWidth;
    private $inputWidth;
    private $startValue = "";
    private $size = 1;
    private $onChange = "";
    private $saveFunction = "";
    private $customClass;
    
    public function setId($id){
        $this->id = $id;
    }
    
    public function getId(){
        if(!isset($this->id)){
            $this->setId("id_".rand()."_dropdown");
        }
        return $this->id;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function setLabel($label) {
        $this->label = $label . ":";
    }

    public function addDataEntry($label, $value="") {
        $this->data[] = array($label, $value);
    }
    
    /**
     * processes arrays for the dropdown list elemtnts
     * @param type $dataEntries array with key=>value pairs for the dropfown list
     */
    public function addDataEntries($dataEntries) {
        foreach($dataEntries as $key => $value){
            $this->addDataEntry($key, $value);
        }
    }
    
    /**
     * 
     * @param type $focus set to true to focus this DropDownList field
     */
    public function setFocus($focus) {
        $this->focus = $focus;
    }

    public function setReadOnly($readOnly) {
        $this->readOnly = $readOnly;
    }

    /**
     * @param type $width width in pixels (without px at the end)
     */
    public function setLabelWidth($labelWidth) {
        $this->labelWidth = $labelWidth."px";
    }

    /**
     * @param type $width width in pixels (without px at the end)
     */
    public function setInputWidth($inputWidth) {
        $this->inputWidth = $inputWidth;
    }
    
    public function setStartValue($startValue){
        $this->startValue = $startValue;
    }

    public function setSize($size) {
        $this->size = $size;
    }

    public function setOnChange($onChange) {
        $this->onChange = $onChange;
    }
    
    public function setSaveFunction($saveFunction) {
        $this->saveFunction = $saveFunction;
    }
    
    public function setCustomClass($customClass) {
        $this->customClass = $customClass;
    }
    

    public function getHtml() {
        
        if(!isset($this->id)){
            $this->setId(rand());
        }
        $this->getContent()->setVariable("ID", $this->id);
        
        if (isset($this->labelWidth)) {
            $this->getContent()->setVariable("LABEL_STYLE", "style=\"width:{$this->labelWidth}\"");
        }
        
        if (isset($this->label) && trim($this->label) !== "") {
            $this->getContent()->setVariable("LABEL", $this->label);
        } else {
            $this->getContent()->setVariable("LABEL", "");
        }
        
        $this->getContent()->setVariable("DROPDOWN_LIST_NAME", $this->name);
        
        $this->getContent()->setVariable("SIZE", $this->size);
        $this->getContent()->setVariable("ONCHANGE", $this->onChange);
        $this->getContent()->setVariable("DATA_OLD_VALUE", $this->startValue);
        
        $this->getContent()->setVariable("READ_ONLY", ($this->readOnly)?"disabled=\"disabled\"":"");
        $this->getContent()->setVariable("CUSTOM_CLASS", $this->customClass);
        $this->getContent()->setVariable("SAVE_FUNCTION", $this->saveFunction);

        foreach ($this->data as $element) {
            $this->getContent()->setCurrentBlock("OPTION_VALUES");
            $this->getContent()->setVariable("VALUE", $element[0]);
            $this->getContent()->setVariable("LABEL", $element[1]);
            if($element[0] == $this->startValue) {$this->getContent()->setVariable("SELECTED", "selected=\"selected\"");} else {$this->getContent()->setVariable("SELECTED", "");}
            $this->getContent()->parse("OPTION_VALUES");
        }
        if($this->startValue !== ""){
            $this->getContent()->setVariable("STARTVALUE", "$('#{$this->id}').val('{$this->startValue}');");
        }
        return $this->getContent()->get();
    }

}

?>
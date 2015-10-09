<?php

namespace Widgets;

class TextInput extends Widget {

    private $id;
    private $name = "";
    private $label;
    private $placeholder = "";
    private $data;
    private $contentProvider;
    private $value = "";
    private $focus = false;
    private $readOnly = false; 
    private $labelWidth;
    private $inputWidth;
    private $inputBackgroundColor;
    private $customSuccessCode = "";
    
    
    public function setId($id){
        $this->id = "id_".$id."_textinput";
    }
    
    public function getId(){
        if(!isset($this->id)){
            $this->setId(rand());
        }
        return $this->id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setLabel($label) {
        $this->label = $label . ":";
    }

    public function setPlaceholder($placeholder) {
        $this->placeholder = $placeholder;
    }

    /**
     * 
     * @param type $data a reference to an object or the id of an object
     */
    public function setData($data) {
        $this->data = $data;
    }

    public function setContentProvider($contentProvider) {
        $this->contentProvider = $contentProvider;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * 
     * @param type $focus set to true to focus this TextInput field
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

    /**
     * @param type $color a value for the css attribute background-color
     */
    public function setInputBackgroundColor($color) {
        $this->inputBackgroundColor = $color;
    }

    /**
     * This method is deprecated
     */
    public function setAutoSave($autosave) {
        $last=next(debug_backtrace());
        \logging::write_log( LOG_MESSAGES, "The function setAutoSave is deprecated. Called in Class: ". $last['class']. " function: ". $last['function']);
    }

    /**
     * 
     * @param type $customSuccessCode code forwarded to the successMethod of the DataProvider
     */
    public function setSuccessMethodForDataProvider($customSuccessCode) {
        $this->customSuccessCode = $customSuccessCode;
    }

    public function getHtml() {
        if(!isset($this->id)){
            $this->setId(rand());
        }
        $this->getContent()->setVariable("ID", $this->id);
        
        
        $reverseSpecialHtmlWidget = new \Widgets\JSWrapper();
        $reverseSpecialHtmlWidget->setJs("function rSHW(value){" .
                "value.replace(/&amp;/g,'&');" .
                "value.replace(/&amp;/g,'&');" .
                "value.replace(/&quot;/g,'\"');" .
                "value.replace(/&#039;/g,'\'');" .
                "value.replace(/&lt;/g,'<');" .
                "value.replace(/&gt;/g,'>');}");
        $this->addWidget($reverseSpecialHtmlWidget);
        
        
        if (isset($this->label) && trim($this->label) !== "") {
            $this->getContent()->setVariable("LABEL", $this->label);
        } else {
            $this->getContent()->setVariable("LABEL", "");
        }
        
        $this->getContent()->setVariable("INPUT_FIELD_NAME", $this->name);
        
        if (isset($this->labelWidth)) {
            $this->getContent()->setVariable("LABEL_STYLE", "style=\"width:{$this->labelWidth}\"");
        }
        
        $this->getContent()->setVariable("PLACEHOLDER", $this->placeholder);
        
        
        
                
        if ($this->focus) {
            $this->getContent()->setCurrentBlock("BLOCK_FOCUS");
            //unfortunately this double assignment of $this->id to the template
            //is necessacy, because the templatengine can't work with a
            //variable within and outside a block
            $this->getContent()->setVariable("FOCUS_ID", $this->id);
            $this->getContent()->parse("BLOCK_FOCUS");
        }

        if ($this->readOnly) {
            $this->getContent()->setVariable("READONLY", "readonly");
        }

        if (isset($this->inputWidth) || isset($this->inputBackgroundColor)) {
            $style = "";
            if (isset($this->inputWidth)) {
                $style .= "width:{$this->inputWidth};";
            }
            if (isset($this->inputBackgroundColor)) {
                $style .= "background-color:{$this->inputBackgroundColor}";
            }
            $style = "style=\"{$style}\"";
            $this->getContent()->setVariable("INPUT_STYLE", $style);
        }

        if ($this->contentProvider) {
            
            if (!$this->contentProvider->isChangeable($this->data)) {
                $this->getContent()->setVariable("READONLY", "readonly");
            }
            
            $valueString = $this->contentProvider->getData($this->data);
            $valueString = ($valueString === "0") ? "" : htmlspecialchars($valueString);
            $this->getContent()->setVariable("VALUE", $valueString);
            
            $this->getContent()->setVariable("SAVE_FUNCTION", $this->contentProvider->getUpdateCode($this->data, $this->id, $this->customSuccessCode));
            
        } else {
            $valueString = htmlspecialchars($this->value);
            $this->getContent()->setVariable("VALUE", $valueString);
        }
        
        return $this->getContent()->get();
    }

}

?>
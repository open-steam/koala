<?php

namespace Widgets;

class DropDownListSanction extends DropDownList {
    
    protected $id;
    protected $name = "";
    protected $label;
    protected $data = array();
    protected $focus = false;
    protected $readOnly = false;
    protected $labelWidth;
    protected $inputWidth;
    protected $startValue = "";
    protected $size = 1;
    protected $onChange = "";
    protected $saveFunction = "";
    protected $customClass;
    protected $steamId;
    protected $members = "";
    protected $subGroups;
    protected $type;
    
    
    
    public function setSteamId($steamId){
        $this->steamId = $steamId;
    }
    
    public function setMembers($members){
        $this->members = $members;
    }
    
    public function setType($type){
        $this->type = $type;
    }
    
    public function setSubGroups($subGroups){
        $this->subGroups = $subGroups;
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
        $this->getContent()->setVariable("STEAM_ID", $this->steamId);
        $this->getContent()->setVariable("MEMBERS", $this->members);
        $this->getContent()->setVariable("TYPE", $this->type);
        $this->getContent()->setVariable("SUB_GROUPS", $this->subGroups);
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
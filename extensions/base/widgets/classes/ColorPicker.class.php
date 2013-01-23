<?php

namespace Widgets;

class ColorPicker extends Widget {

    private $id = "uniqueId";
    private $value = "#FFFFFE";
    private $label = "";
    private $onChange = "";
    
    public function setOnChange($o){
        $this->onChange=$o;
    }

    public function setId($i) {
        $this->id = $i;
    }

    public function setValue($v) {
        $this->value = $v;
    }

    public function setLabel($l) {
        $this->label = $l;
    }

    public function getHtml() {
        $this->getContent()->setVariable("ID", $this->id);
        $this->getContent()->setVariable("VALUE", $this->value);
        $this->getContent()->setVariable("LABEL", $this->label);
        $this->getContent()->setVariable("ONCHANGE", $this->onChange);
        

        $id = "#" . $this->id;
        $js = "<script>";
        $js.= '$("' . $id . '").simpleColor();';
        $js.= "</script>";
        $clearer = new \Widgets\Clearer();
        return $this->getContent()->get() . $clearer->getHtml() . $js;
    }

}

?>
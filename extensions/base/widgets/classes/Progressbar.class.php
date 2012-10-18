<?php

namespace Widgets;

class Progressbar extends Widget {
    private $id = "";
    private $value = 0;
    private $width = "200px";
    
    public function setId($id) {
        $this->id = $id;
    }
    public function setValue($value){
        $this->value = $value;
    }
    public function setWidth($width){
        $this->width = $width;
    }

    public function getHtml() {
        $this->getContent()->setVariable("ID", $this->id);
        $this->getContent()->setVariable("WIDTH", $this->width);
        $this->getContent()->setVariable("VALUE", $this->value);
        
        return $this->getContent()->get();
    }

}

?>

<?php

namespace Widgets;

class Slider extends Widget {

    private $id = "";
    private $max = 100;
    private $min = 0;
    private $range = "true";
    private $value = 0;
    private $change = "function(){}";
    private $width = "130px";

    public function setId($id) {
        $this->id = $id;
    }

    public function setMax($max) {
        $this->max = $max;
    }

    public function setMin($min) {
        $this->min = $min;
    }

    public function setRange($range) {
        $this->step = $range;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function setChange($change) {
        $this->change = $change;
    }

    public function getHtml() {
        $this->getContent()->setVariable("ID", $this->id);
        $this->getContent()->setVariable("IDR", "#" . $this->id);
        $this->getContent()->setVariable("MIN", $this->min);
        $this->getContent()->setVariable("MAX", $this->max);
        $this->getContent()->setVariable("VALUE", $this->value);
        $this->getContent()->setVariable("RANGE", $this->range);
        $this->getContent()->setVariable("EVENT", $this->change);
        $this->getContent()->setVariable("WIDTH", $this->width);

        return $this->getContent()->get();
    }

}

?>

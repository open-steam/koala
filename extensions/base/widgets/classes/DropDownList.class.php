<?php

namespace Widgets;

class DropDownList extends Widget {

    private $name = '';
    private $id = '';
    private $size = 1;
    private $onChange = "";
    private $class = "";
    private $optionValues = array();
    private $disabled = false;

    public function setName($name) {
        $this->name = $name;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setSize($size) {
        $this->size = $size;
    }

    public function setOnChange($onChange) {
        $this->onChange = $onChange;
    }

    public function setDisabled($disabled) {
        $this->disabled = $disabled;
    }

    public function setOptionValues($optionValues) {
        if (is_array($optionValues)) {
            $this->optionValues = $optionValues;
        }
    }

    public function getHtml() {
        $this->getContent()->setVariable("NAME", $this->name);
        $this->getContent()->setVariable("ID", $this->id);
        $this->getContent()->setVariable("SIZE", $this->size);
        $this->getContent()->setVariable("ONCHANGE", $this->onChange);
        if ($this->disabled) {
            $this->getContent()->setVariable("DISABLED", "disabled");
        } else {
            $this->getContent()->setVariable("DISABLED", "");
        }

        foreach ($this->optionValues as $index => $value) {
            $this->getContent()->setCurrentBlock("OPTION_VALUES");
            $this->getContent()->setVariable("INDEX", $index);
            $this->getContent()->setVariable("VALUE", $value);
            $this->getContent()->parse("OPTION_VALUES");
        }
        return $this->getContent()->get();
    }

}

?>
<?php

namespace Widgets;

class RadioButton extends Widget {

    private $contentProvider;
    private $id;
    private $objectId;
    private $options;
    private $data;
    private $label;
    private $defaultChecked;
    private $type = "vertical";
    private $currentValue;

    public function setCurrentValue($cv) {
        $this->currentValue = $cv;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function setData($data) {
        $this->data = $data;
        if (is_int($data)) {
            $this->objectId = $data;
        } else {
            $this->objectId = $data->get_id();
        }
    }

    public function setOptions($options) {
        $this->options = $options;
    }

    public function setContentProvider($contentProvider) {
        $this->contentProvider = $contentProvider;
    }

    public function setDefaultChecked($defaultChecked) {
        $this->defaultChecked = $defaultChecked;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getHtml() {
        $this->id = rand();
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
                }

                $this->getContent()->setVariable("ID", $this->id);
                $this->getContent()->setVariable("RADIONAME", $this->id);
                $this->getContent()->setVariable("RADIOVALUE", $option["value"]);
                $this->getContent()->setVariable("RADIOLABEL", $option["name"]);
                if (isset($this->contentProvider)) {
                    $this->getContent()->setVariable("ONCHANGE", $this->contentProvider->getUpdateCode($this->data, $this->id . "_" . $option["value"]));
                }
                if ($currentValue === $option["value"]) {
                    $this->getContent()->setVariable("CHECKED", "checked");
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
                }

                $this->getContent()->setVariable("ID_HORIZONTAL", $this->id);
                $this->getContent()->setVariable("RADIONAME_HORIZONTAL", $this->id);
                $this->getContent()->setVariable("RADIOVALUE_HORIZONTAL", $option["value"]);
                $this->getContent()->setVariable("RADIOLABEL_HORIZONTAL", $option["name"]);
                if (isset($this->contentProvider)) {
                    $this->getContent()->setVariable("ONCHANGE_HORIZONTAL", $this->contentProvider->getUpdateCode($this->data, $this->id . "_" . $option["value"]));
                }
                if ($currentValue == $option["value"]) {
                    $this->getContent()->setVariable("CHECKED_HORIZONTAL", "checked");
                }
                $this->getContent()->parse("BLOCK_RADIOFIELD_HORIZONTAL");
            }
            $this->getContent()->parse("BLOCK_RADIOBUTTON_HORIZONTAL");
        }

        return $this->getContent()->get();
    }

}

?>
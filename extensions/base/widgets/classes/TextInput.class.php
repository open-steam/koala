<?php

namespace Widgets;

class TextInput extends Widget {

    private $label;
    private $data;
    private $contentProvider;
    private $id;
    private $objectId;
    private $focus = false;
    private $readOnly = false;
    private $value = "";
    private $placeholder = "";
    private $labelWidth;
    private $inputWidth;
    private $inputBackgroundColor;
    private $autosave = true;
    private $customSaveCode = "";
    private $name = "";

    public function setName($name) {
        $this->name = $name;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function setPlaceholder($placeholder) {
        $this->placeholder = $placeholder;
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

    public function setValue($value) {
        $this->value = $value;
    }

    public function setFocus($focus) {
        $this->focus = $focus;
    }

    public function setReadOnly($readOnly) {
        $this->readOnly = $readOnly;
    }

    public function setLabelWidth($width) {
        $this->labelWidth = $width;
    }

    public function setInputWidth($width) {
        $this->inputWidth = $width;
    }

    public function setInputBackgroundColor($color) {
        $this->inputBackgroundColor = $color;
    }

    public function setAutoSave($autosave) {
        $this->autosave = $autosave;
    }

    public function setCustomSaveCode($js) {
        $this->customSaveCode = $js;
    }

    public function getHtml() {
        $reverseSpecialHtmlWidget = new \Widgets\JSWrapper();
        $reverseSpecialHtmlWidget->setJs("function rSHW(value){" .
                "value.replace(/&amp;/g,'&');" .
                "value.replace(/&amp;/g,'&');" .
                "value.replace(/&quot;/g,'\"');" .
                "value.replace(/&#039;/g,'\'');" .
                "value.replace(/&lt;/g,'<');" .
                "value.replace(/&gt;/g,'>');}");

        $this->id = rand();
        $this->addWidget($reverseSpecialHtmlWidget);
        if (isset($this->label) && trim($this->label) !== "") {
            $this->getContent()->setVariable("LABEL", $this->label . ":");
        } else {
            $this->getContent()->setVariable("LABEL", "");
        }
        $this->getContent()->setVariable("PLACEHOLDER", $this->placeholder);
        if (isset($this->labelWidth)) {
            $this->getContent()->setVariable("LABEL_STYLE", "style=\"width:{$this->labelWidth}px\"");
        }
        $this->getContent()->setVariable("ID", $this->id);
        if ($this->focus) {
            $this->getContent()->setVariable("FOCUS_ID", $this->id);
        }
        if ($this->readOnly) {
            $this->getContent()->setVariable("READONLY", "readonly");
            $this->getContent()->setVariable("ADD_CLASS_LABEL", "readonly");
            $this->getContent()->setVariable("ADD_CLASS_INPUT", "readonly");
        }
        if (isset($this->inputWidth) || isset($this->inputBackgroundColor)) {
            $style = "";
            if (isset($this->inputWidth)) {
                $style .= "width:{$this->inputWidth}px;";
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
                $this->getContent()->setVariable("ADD_CLASS_LABEL", "readonly");
                $this->getContent()->setVariable("ADD_CLASS_INPUT", "readonly");
            }
            $valueString = $this->contentProvider->getData($this->data);
            if (is_array($valueString)) {
                echo $this->id;die;
            }
            $valueString = (($valueString === "0") || ($valueString === "")) ? "" : $valueString;
            $valueString = htmlspecialchars($valueString);
            $this->getContent()->setVariable("VALUE", $valueString);
            if (!$this->autosave) {
                $this->getContent()->setVariable("CHANGE_FUNCTION", "onClick=\"event.stopPropagation();" .
                        "\"onKeyup=\"if (event.keyCode==13)" .
                        "{value = getElementById({$this->id}).value;rSHW(value);" .
                        "widgets_textinput_save({$this->id});" .
                        "{$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_textinput_save_success")};" .
                        "{$this->customSaveCode} } " .
                        "else { widgets_textinput_changed({$this->id});}\"");

                $this->getContent()->setVariable("SAVE_FUNCTION", "onClick=\"event.stopPropagation();" .
                        "value = jQuery('#{$this->id}').val();" .
                        "rSHW(value);" .
                        "widgets_textinput_save({$this->id});" .
                        "{$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_textinput_save_success")}\"");
            } else {
                $this->getContent()->setVariable("CHANGE_FUNCTION", "onBlur=\"if (jQuery('#{$this->id}').hasClass('changed'))" .
                        "{value = getElementById({$this->id}).value;" .
                        "widgets_textinput_save({$this->id});" .
                        "{$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_textinput_save_success")}} " .
                        "\"onClick=\"event.stopPropagation();" .
                        "\"onKeyup=\"if (event.keyCode==13)" .
                        "{value = jQuery(getElementById({$this->id})).attr('pre') + getElementById({$this->id}).value + jQuery(getElementById({$this->id})).attr('post');" .
                        "widgets_textinput_save({$this->id});{$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_textinput_save_success")};" .
                        "{$this->customSaveCode} }" .
                        "else { widgets_textinput_changed_autosave({$this->id});}\"");
            }
            $this->getContent()->setVariable("UNDO_FUNCTION", "onClick=\"event.stopPropagation();" .
                    "value = jQuery('#{$this->id}').attr('oldValue');" .
                    "widgets_textinput_save({$this->id});" .
                    "{$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_textinput_undo_success")}\"");
        } else {
            $valueString = htmlspecialchars($this->value);
            $this->getContent()->setVariable("VALUE", $valueString);
        }
        $this->getContent()->setVariable("NAME", $this->name);
        return $this->getContent()->get();
    }

}

?>
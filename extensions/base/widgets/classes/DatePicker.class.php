<?php
namespace Widgets;

class DatePicker extends Widget {

    private $id;
    private $name = "";
    private $label;
    private $placeholder = "";
    private $data;
    private $value;
    private $contentProvider;
    private $readOnly = false;
    private $labelWidth;
    private $inputWidth;
    private $inputBackgroundColor;
    private $customSaveCode = "";

    private $datePicker = true;
    private $timePicker = false;

    public function setId($id){
        $this->id = "id_".$id."_datepicker";
    }

    public function getId(){
        if(!isset($this->id)){
            $this->setId(rand());
        }
        return $this->id;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function setLabel($label) {
				$this->label = $label;
    }

    public function setValue($value) {
				$this->value = $value;
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

    public function setCustomSaveCode($customSaveCode) {
        $this->customSaveCode = $customSaveCode;
    }

    public function setDatePicker($datePicker) {
				$this->datePicker = $datePicker;
    }

    public function setTimePicker($timePicker) {
				$this->timePicker = $timePicker;
    }

    public function getHtml() {
				if(!isset($this->id)){
            $this->setId(rand());
        }

        $this->getContent()->setVariable("ID", $this->id);

        if (isset($this->label) && trim($this->label) !== "") {
            $this->getContent()->setVariable("LABEL", $this->label);
        } else {
            $this->getContent()->setVariable("LABEL", "");
        }

				$this->getContent()->setVariable("PLACEHOLDER", $this->placeholder);

        if($this->name !== ""){
            $this->getContent()->setVariable("INPUT_FIELD_NAME", $this->name);
        }

        if (isset($this->labelWidth)) {
            $this->getContent()->setVariable("LABEL_STYLE", "style=\"width:{$this->labelWidth}\"");
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

        $this->getContent()->setVariable("PLACEHOLDER", $this->placeholder);

        //write sanction
        if ($this->readOnly) {
            $this->getContent()->setVariable("READONLY", "readonly");
        }

        if ($this->contentProvider) {

            if (!$this->contentProvider->isChangeable($this->data)) {
                $this->getContent()->setVariable("READONLY", "readonly");
            }

            $currentDateValue = $this->contentProvider->getData($this->data);
            $currentDateValue = ($currentDateValue == 0) ? "" : $currentDateValue;
            $this->getContent()->setVariable("VALUE", $currentDateValue);

            $this->getContent()->setVariable("SAVE_FUNCTION", $this->contentProvider->getUpdateCode($this->data, $this->id));
				} else {
            $this->getContent()->setVariable("VALUE", "");
				}

        if ($this->value) {
            $this->getContent()->setVariable("VALUE", $this->value);
        }

				if ($this->datePicker && $this->timePicker) {
            $this->getContent()->setVariable("PICKER", "$(\"#{$this->id}\").datetimepicker({dateFormat: \"dd.mm.yy\", hourGrid: 4, minuteGrid: 10});");
				} else if ($this->datePicker) {
            $this->getContent()->setVariable("PICKER", "$(\"#{$this->id}\").datepicker({dateFormat: \"dd.mm.yy\", showButtonPanel: true});");
				} else if ($this->timePicker) {
            $this->getContent()->setVariable("PICKER", "$(\"#{$this->id}\").timepicker({hourGrid: 4, minuteGrid: 10});");
				}

				return $this->getContent()->get();
		}
}
?>

<?php
namespace Widgets;

class DatePicker extends Widget {
	private $label;
	private $data;
	private $contentProvider;
	private $id;
	private $objectId;
	private $datePicker = true;
	private $timePicker = false;
	private $placeholder = "";
	
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
	
	public function setDatePicker($datePicker) {
		$this->datePicker = $datePicker;
	}
	
	public function setTimePicker($timePicker) {
		$this->timePicker = $timePicker;
	}
	
	public function setContentProvider($contentProvider) {
		$this->contentProvider = $contentProvider;
	}
	
	public function setPlaceholder($placeholder) {
		$this->placeholder = $placeholder;
	}
	
	public function getHtml() {
		$this->id = rand();
		if (isset($this->label)) {
			$this->getContent()->setVariable("LABEL", $this->label);
		}
		$this->getContent()->setVariable("PLACEHOLDER", $this->placeholder);
		$this->getContent()->setVariable("ID", $this->id);
		if ($this->contentProvider) {
			$currentDateValue = $this->contentProvider->getData($this->data);
			$currentDateValue = ($currentDateValue == 0) ? "" : $currentDateValue;
			$this->getContent()->setVariable("VALUE", $currentDateValue);
			$this->getContent()->setVariable("CHANGE_FUNCTION", "onChange=\"widgets_datepicker_changed({$this->id}); value = getElementById({$this->id}).value; widgets_datepicker_save({$this->id});{$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_datepicker_save_success")}\"");
			$this->getContent()->setVariable("SAVE_FUNCTION", "onClick=\"value = getElementById({$this->id}).value; widgets_datepicker_save({$this->id});{$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_datepicker_save_success")}\"");
			$this->getContent()->setVariable("UNDO_FUNCTION", "onClick=\"value = getElementById({$this->id}).oldValue; widgets_datepicker_save({$this->id});{$this->contentProvider->getUpdateCode($this->data, $this->id, "widgets_datepicker_undo_success")}\"");
		} else {
			$this->getContent()->setVariable("VALUE", "");
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
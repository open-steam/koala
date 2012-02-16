<?php
namespace Widgets;

class Checkbox extends Widget {
	private $data;
	private $contentProvider;
	private $id;
	private $objectId;
	private $label;
	private $checkedValue = true;
	private $uncheckedValue = false;
	
	public function setData($data) {
		$this->data = $data;
		if (is_int($data)) {
			$this->objectId = $data;
		} else {
			$this->objectId = $data->get_id();
		}
	}
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function setContentProvider($contentProvider) {
		$this->contentProvider = $contentProvider;
	}
	
	public function setCheckedValue($checkedValue) {
		$this->checkedValue = $checkedValue;
	}
	
	public function setUncheckedValue($uncheckedValue) {
		$this->uncheckedValue = $uncheckedValue;
	}
	
	public function getHtml() {
		$this->id = rand();
		if (isset($this->label)) {
			$this->getContent()->setVariable("LABEL", $this->label);
		}
		$currentValue = $this->contentProvider->getData($this->data);
		$this->getContent()->setVariable("ID", $this->id);
		$this->getContent()->setVariable("ID2", $this->id);
		$this->getContent()->setVariable("ID3", $this->id);
		if ($currentValue === $this->checkedValue) {
			$this->getContent()->setVariable("CHECKED", "checked");
		}
		$this->getContent()->setVariable("CHECKEDVALUE", $this->checkedValue);
		$this->getContent()->setVariable("UNCHECKEDVALUE", $this->uncheckedValue);
		$this->getContent()->setVariable("ONCHANGE", $this->contentProvider->getUpdateCode($this->data, $this->id . "_checkbox"));
		return $this->getContent()->get();
	}
}
?>
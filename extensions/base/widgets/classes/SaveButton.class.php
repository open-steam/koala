<?php
namespace Widgets;

class SaveButton extends Button {

	private $label="Speichern";
	private $beforeSaveJS = "";
	private $saveReload="location.reload();";

	public function setLabel($label) {
		$this->label = $label;
	}

	public function setBeforeSaveJS($beforeSaveJS){
		$this->beforeSaveJS = $beforeSaveJS;
	}

	public function setSaveReload($saveReload) {
		$this->saveReload = $saveReload;
	}

	public function getHtml() {
		$this->id = rand();
		$this->getContent()->setVariable("BUTTON_ID", $this->id);
		$this->getContent()->setVariable("BUTTON_LABEL", $this->label);
		$this->getContent()->setVariable("BEFORE_SAVE_JS", $this->beforeSaveJS);
		$this->getContent()->setVariable("SAVE_RELOAD", $this->saveReload);

		return $this->getContent()->get();
	}
}
?>

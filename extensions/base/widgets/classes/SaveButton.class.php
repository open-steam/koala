<?php

namespace Widgets;

class SaveButton extends Button {

    private $label = "Speichern";
    private $beforeSaveJS = "";
    private $saveReload = "location.reload();";
    private $progressbar = false;

    public function setLabel($label) {
        $this->label = $label;
    }

    public function setBeforeSaveJS($beforeSaveJS) {
        $this->beforeSaveJS = $beforeSaveJS;
    }

    public function setSaveReload($saveReload) {
        $this->saveReload = $saveReload;
    }

    /**
     * 
     * @param type $progressbar set to true to display a progress bar while saving
     */
    public function setProgessbar($progressbar) {
        $this->progressbar = $progressbar;
    }

    public function getHtml() {
        $this->getContent()->setVariable("BUTTON_ID", $this->getId());
        $this->getContent()->setVariable("BUTTON_LABEL", $this->label);
        $this->getContent()->setVariable("BEFORE_SAVE_JS", $this->beforeSaveJS);
        $this->getContent()->setVariable("SAVE_RELOAD", $this->saveReload);
        $this->getContent()->setVariable("PROGRESSBAR", ($this->progressbar)? "true": "false");

        return $this->getContent()->get();
    }

}

?>

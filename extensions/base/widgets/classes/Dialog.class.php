<?php
//textareas müssten noch genau überprüft werden, ob da das speichern überall geht
//bei tinymce muss ein klick auf einen der buttons ebenfalls ein changed bewirken... sonst kann man nicht nur ein wort fett machen und dann speichern

namespace Widgets;

class Dialog extends Widget{

    private $title = "";
    private $description ="";
    private $width = "500px";
    private $autoSaveDialog = false;
    private $autoSaveDialogCloseButtonLabel = "Speichern & Schließen";
    private $saveAndCloseButtonLabel = "Speichern & Schließen";
    private $saveAndCloseButtonJs = "";
    private $saveAndCloseButtonForceReload = false;
    private $cancelButtonLabel = "Abbrechen";
    private $cancelButtonJs = "";
    private $customButtons;

    public function setTitle($title){
        $this->title = $title;
    }

    public function setDescription($description){
        $this->description = $description;
    }

    //TODO: den aufruf dieser methode aus dem projekt durch setSaveAndCloseButtonLabel ersetzen
    public function setCloseButtonLabel($closeButtonLabel){
        //if someone calls the old function write a log entry
        $last=next(debug_backtrace());
        \logging::write_log( LOG_MESSAGES, "The function setCloseButtonLabel is deprecated. Called in Class: ". $last['class']. " function: ". $last['function']);

        //forward the medthodcall to the new method
        $this->setSaveAndCloseButtonLabel($closeButtonLabel);
    }

    public function setCancelButtonLabel($cancelButtonLabel){
        $this->cancelButtonLabel = $cancelButtonLabel;
    }

    //TODO: den aufruf dieser methode aus dem projekt entfernen
    public function setPositionX($positionX){
        $this->positionX = $positionX;
    }

    //TODO: den aufruf dieser methode aus dem projekt entfernen
    public function setPositionY($positionY){
        $this->positionY = $positionY;
    }

    /**
     *
     * @param int $width dialodwidth in pixels without 'px' at the end
     */
    public function setWidth($width){
        $this->width = $width . "px";
    }

    /**
     *
     * @param boolean $autoSaveDialog
     * Some dialogs still save their data on every change.
     * If Autosave is on, the abort button is not displayed and the savebutton just closes the dialog.
     * The autosavemechanism is not implemented in this class.
     * saveAndCloseButtonJs is still executed
     */
    public function setAutoSaveDialog($autoSaveDialog){
        $this->autoSaveDialog = $autoSaveDialog;
    }

    public function setAutoSaveDialogCloseButtonLabel($autoSaveDialogCloseButtonLabel){
        $this->autoSaveDialogCloseButtonLabel = $autoSaveDialogCloseButtonLabel;
    }

    public function setSaveAndCloseButtonLabel($saveAndCloseButtonLabel){
        $this->saveAndCloseButtonLabel = $saveAndCloseButtonLabel;
    }

    /**
     * This code is always executed to provide an alternative for the cound of objcts with a 'changed' class
     * 
     * @param type $saveAndCloseButtonJs
     */
    public function setSaveAndCloseButtonJs($saveAndCloseButtonJs){
        $this->saveAndCloseButtonJs = $saveAndCloseButtonJs;
    }

    /**
     * former method name: setForceReload($forceReload)
     * set to true to enable the autoreload e.g. with fileuploaders
     * @param boolean $saveAndCloseButtonForceReload
     */
    public function setSaveAndCloseButtonForceReload($saveAndCloseButtonForceReload){
        $this->saveAndCloseButtonForceReload = $saveAndCloseButtonForceReload;
    }

    public function setForceReload($forceReload){
        //if someone calls the old function write a log entry
        $last=next(debug_backtrace());
        \logging::write_log( LOG_MESSAGES, "The function setForceReload is deprecated. Called in Class: ". $last['class']. " function: ". $last['function']);

        //forward the medthodcall to the new method
        $this->setSaveAndCloseButtonForceReload($forceReload);
    }

    /**
     * this method is deprecated!
     * use setCustomButtons instead
     * @param type $customButtons custom buttons
     */
    public function setButtons($customButtons){
        //if someone calls the old function write a log entry
        $last=next(debug_backtrace());
        \logging::write_log( LOG_MESSAGES, "The function setButtons is deprecated. Called in Class: ". $last['class']. " function: ". $last['function']);

        $this->setCustomButtons($customButtons);
    }

    /**
     * former method name: setButtons
     * @param Array $customButtons custom buttons the user wants to use
     */
    public function setCustomButtons($customButtons){
        $this->customButtons = $customButtons;
    }

    public function getHtml() {
        $this->getContent()->setVariable("DIALOG_WIDTH", $this->width);
        $this->getContent()->setVariable("DIALOG_TITLE", $this->title);
        $this->getContent()->setVariable("DIALOG_DESCRIPTION", $this->description);

        $content = "";
        foreach ($this->getWidgets() as $widget) {
            $content .= $widget->getHtml();
        }

        $this->getContent()->setVariable("DIALOG_CONTENT", $content);

        if ($this->customButtons && is_array($this->customButtons)) {
            $this->customButtons = array_reverse($this->customButtons);
            foreach ($this->customButtons as $button) {
                $this->getContent()->setCurrentBlock("BLOCK_CUSTOM_BUTTONS");
                $this->getContent()->setVariable("BUTTON_CLASS", (isset($button["class"])? $button["class"] : ""));
                $this->getContent()->setVariable("BUTTON_JS", (isset($button["js"])? $button["js"] : ""));
                $this->getContent()->setVariable("BUTTON_LABEL", (isset($button["label"])? $button["label"] : ""));
                $this->getContent()->parse("BLOCK_CUSTOM_BUTTONS");
            }
        }

        if (isset($this->cancelButtonLabel) && $this->cancelButtonLabel != "" && !$this->autoSaveDialog) {
            $this->getContent()->setCurrentBlock("CANCEL_BUTTON");
            $this->getContent()->setVariable("CANCEL_BUTTON_LABEL", $this->cancelButtonLabel);
            $this->getContent()->setVariable("CANCEL_BUTTON_JS", $this->cancelButtonJs);
            $this->getContent()->parse("CANCEL_BUTTON");
        }

        //button to save the changes and close the dialog
        if (isset($this->saveAndCloseButtonLabel)) {
            $this->getContent()->setCurrentBlock("SAVE_AND_CLOSE_BUTTON");

            if($this->autoSaveDialog){
                $this->getContent()->setVariable("SAVE_AND_CLOSE_BUTTON_LABEL", $this->autoSaveDialogCloseButtonLabel);
            } else {
                $this->getContent()->setVariable("SAVE_AND_CLOSE_BUTTON_LABEL", $this->saveAndCloseButtonLabel);
            }

            $this->getContent()->setVariable("SAVE_AND_CLOSE_BUTTON_JS", $this->saveAndCloseButtonJs);

            if ($this->saveAndCloseButtonForceReload || $this->autoSaveDialog) {
                $this->getContent()->setVariable("SAVE_AND_CLOSE_BUTTON_RELOAD", "location.reload();");
            } else {
                $this->getContent()->setVariable("SAVE_AND_CLOSE_BUTTON_RELOAD", "");
            }
            $this->getContent()->parse("SAVE_AND_CLOSE_BUTTON");
        }
        return $this->getContent()->get();
    }
}

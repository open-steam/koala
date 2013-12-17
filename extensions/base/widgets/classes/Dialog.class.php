<?php
namespace Widgets;

class Dialog.class extends Widget
{
    private $title = "";
    private $description ="";
    private $positionX = 0;
    private $positionY = 0;
    private $width = "500px";
    private $closeJs = "location.reload();";
    private $showCloseIcon = false;
    private $closeButtonLabel = "Speichern & Schließen";
    private $forceReload = false;
    private $buttons;

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setCloseButtonLabel($closeButtonLabel)
    {
        $this->closeButtonLabel = $closeButtonLabel;
    }

    public function setPositionX($positionX)
    {
        $this->positionX = $positionX;
    }

    public function setPositionY($positionY)
    {
        $this->positionY = $positionY;
    }

    public function setWidth($width)
    {
        $this->width = $width . "px";
    }

    public function setCloseJs($code)
    {
        $this->closeJs = $code;
    }

    public function setButtons($buttons)
    {
        $this->buttons = $buttons;
    }

    public function setForceReload($forceReload)
    {
        $this->forceReload = $forceReload;
    }

    public function getHtml()
    {
        $html = "";
        foreach ($this->getWidgets() as $widget) {
            $html .= $widget->getHtml();
        }
        $this->getContent()->setVariable("DIALOG_TITLE", $this->title);
        if ($this->description !== "") {
            $this->getContent()->setVariable("DIALOG_DESCRIPTION", $this->description);
        }
        $this->getContent()->setVariable("DIALOG_CONTENT", $html);
        $this->getContent()->setVariable("DIALOG_POSITION_X", $this->positionX);
        $this->getContent()->setVariable("DIALOG_POSITION_Y", $this->positionY);
        $this->getContent()->setVariable("DIALOG_WIDTH", $this->width);
        if ($this->buttons && is_array($this->buttons)) {
            $this->buttons = array_reverse($this->buttons);
            foreach ($this->buttons as $button) {
                $this->getContent()->setCurrentBlock("BLOCK_BUTTON_CANCEL");
                $this->getContent()->setVariable("BUTTON_CLASS", $button["class"]);
                $this->getContent()->setVariable("BUTTON_JS", $button["js"]);
                $this->getContent()->setVariable("BUTTON_LABEL", $button["label"]);
                $this->getContent()->parse("BLOCK_BUTTON_CANCEL");
            }
        }

                //close button
                if (isset($this->closeButtonLabel)) {
                    $this->getContent()->setCurrentBlock("BLOCK_BUTTON_CLOSE");

                    $this->getContent()->setVariable("CLOSE_BUTTON_LABEL", $this->closeButtonLabel);

                    if ($this->closeJs) {
                        $this->getContent()->setVariable("DIALOG_CLOSE_JS", $this->closeJs);
                    }

                    if ($this->forceReload) {
                        $this->getContent()->setVariable("RELOAD_CODE", "true");
                    } else {
            $this->getContent()->setVariable("RELOAD_CODE", "jQuery('#dialog_wrapper').find('.changed, .saved').length > 0");
                    }
                    $this->getContent()->parse("BLOCK_BUTTON_CLOSE");
                }

        return $this->getContent()->get();
    }
}

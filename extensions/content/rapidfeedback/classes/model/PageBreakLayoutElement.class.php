<?php

namespace Rapidfeedback\Model;

class PageBreakLayoutElement extends AbstractLayoutElement {

    function __construct() {
        
    }

    public function saveXML($layoutElement) {
        $layoutElement->addChild("type", 9);
        return $layoutElement;
    }

    public function getEditHTML($id, $number = -1) {
        $RapidfeedbackExtension = \Rapidfeedback::getInstance();
        $content = $RapidfeedbackExtension->loadTemplate("layoutelements/pagebreak.template.html");
        $content->setCurrentBlock("BLOCK_EDIT");
        if ($number != -1) {
            $content->setVariable("NUMBER", $number);
        }
        $content->setVariable("ELEMENT_ID", $id);
        $content->setVariable("ASSETURL", $RapidfeedbackExtension->getAssetUrl() . "icons/");
        $content->setVariable("EDIT_LABEL", "Bearbeiten");
        $content->setVariable("COPY_LABEL", "Kopieren");
        $content->setVariable("DELETE_LABEL", "Löschen");
        $content->setVariable("PAGEBREAK_LABEL", "Seitenumbruch");
        $data = "9";
        $content->setVariable("ELEMENT_DATA", $data);
        $content->parse("BLOCK_EDIT");
        return $content->get();
    }

    public function getViewHTML() {
        return "";
    }

    public function getResultHTML() {
        return "";
    }

}

?>
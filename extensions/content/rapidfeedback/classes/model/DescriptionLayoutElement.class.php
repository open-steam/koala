<?php

namespace Rapidfeedback\Model;

class DescriptionLayoutElement extends AbstractLayoutElement {

    private $description = "";

    function __construct($layoutElement = null) {
        if ($layoutElement != null) {
            $this->description = $layoutElement->description;
        }
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function saveXML($layoutElement) {
        $layoutElement->addChild("type", 7);
        $layoutElement->addChild("description", $this->description);
        return $layoutElement;
    }

    public function getEditHTML($id, $number = -1) {
        $RapidfeedbackExtension = \Rapidfeedback::getInstance();
        $content = $RapidfeedbackExtension->loadTemplate("layoutelements/description.template.html");
        $content->setCurrentBlock("BLOCK_EDIT");
        if ($number != -1) {
            $content->setVariable("NUMBER", $number);
        }
        $content->setVariable("ELEMENT_ID", $id);
        $content->setVariable("ASSETURL", $RapidfeedbackExtension->getAssetUrl() . "icons/");
        $content->setVariable("EDIT_LABEL", "Bearbeiten");
        $content->setVariable("COPY_LABEL", "Kopieren");
        $content->setVariable("DELETE_LABEL", "Löschen");
        $content->setVariable("DESCRIPTION_LABEL", "Beschreibung");
        $content->setVariable("DESCRIPTION_CONTENT", $this->description);
        $data = "7," . rawurlencode($this->description);
        $content->setVariable("ELEMENT_DATA", $data);
        $content->parse("BLOCK_EDIT");
        return $content->get();
    }

    public function getViewHTML($number = -1) {
        $RapidfeedbackExtension = \Rapidfeedback::getInstance();
        $content = $RapidfeedbackExtension->loadTemplate("layoutelements/description.template.html");
        $content->setCurrentBlock("BLOCK_VIEW");
        $content->setVariable("DESCRIPTION_CONTENT", ($number).". ".$this->description);
        $content->parse("BLOCK_VIEW");
        return $content->get();
    }

    public function getResultHTML() {
        return "";
    }

}

?>
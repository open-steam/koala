<?php

namespace Rapidfeedback\Model;

class JumpLabel extends AbstractLayoutElement {

    private $from = "";
    private $to = "";

    function __construct($layoutElement = null) {
        if ($layoutElement != null) {
            $this->from = $layoutElement->from;
             $this->to = $layoutElement->to;
        }
    }

    public function setFrom($f) {
        $this->from = $f;
    }

    public function setTo($t) {
        $this->to = $t;
    }

    public function saveXML($layoutElement) {
        $layoutElement->addChild("type", 10);
        $layoutElement->addChild("from", $this->from);
        $layoutElement->addChild("to", $this->to);
        return $layoutElement;
    }

    public function getEditHTML($id, $number = -1) {
        $RapidfeedbackExtension = \Rapidfeedback::getInstance();
        $content = $RapidfeedbackExtension->loadTemplate("layoutelements/jumpLabel.template.html");
        $content->setCurrentBlock("BLOCK_EDIT");
        if ($number !== -1) {
            $content->setVariable("NUMBER", $number);
        }
        $content->setVariable("ELEMENT_ID", $id);
        $content->setVariable("ASSETURL", $RapidfeedbackExtension->getAssetUrl() . "icons/");
        $content->setVariable("EDIT_LABEL", "Bearbeiten");
        $content->setVariable("COPY_LABEL", "Kopieren");
        $content->setVariable("DELETE_LABEL", "Löschen");

        $content->setVariable("QUESTION_FROM", $this->from);
        $content->setVariable("QUESTION_TO", $this->to);

        $data = "10," . rawurlencode($this->from) . "," . rawurlencode($this->to);
        $content->setVariable("ELEMENT_DATA", $data);
        $content->parse("BLOCK_EDIT");
        return $content->get();
    }

    public function getViewHTML($number = -1) {
        $RapidfeedbackExtension = \Rapidfeedback::getInstance();
        $content = $RapidfeedbackExtension->loadTemplate("layoutelements/jumpLabel.template.html");
        $content->setCurrentBlock("BLOCK_VIEW");
        if ($number !== -1) {
            $content->setVariable("NUMBER", $number);
        }
        $content->setVariable("QUESTION_FROM", $this->from);
        $content->setVariable("QUESTION_TO", $this->to);

        $content->parse("BLOCK_VIEW");
        return $content->get();
    }

    public function getResultHTML() {
        return "";
    }

}
?>
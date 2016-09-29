<?php

namespace Questionnaire\Model;

class JumpLabel extends AbstractLayoutElement {

    private $text = "";
    private $to = "";

    function __construct($layoutElement = null) {
        if ($layoutElement != null) {
            $this->text = $layoutElement->text;
            $this->to = $layoutElement->to;
        }
    }

    public function setText($f) {
        $this->text = $f;
    }

    public function setTo($t) {
        $this->to = $t;
    }

    public function saveXML($layoutElement) {
        $layoutElement->addChild("type", 10);
        $layoutElement->addChild("text", $this->text);
        $layoutElement->addChild("to", $this->to);
        return $layoutElement;
    }

    public function getEditHTML($id, $number = -1) {
        $QuestionnaireExtension = \Questionnaire::getInstance();
        $content = $QuestionnaireExtension->loadTemplate("layoutelements/jumpLabel.template.html");
        $content->setCurrentBlock("BLOCK_EDIT");
        if ($number !== -1) {
            $content->setVariable("NUMBER", $number);
        }
        $content->setVariable("ELEMENT_ID", $id);
        $content->setVariable("ASSETURL", $QuestionnaireExtension->getAssetUrl() . "icons/");
        $content->setVariable("EDIT_LABEL", "Bearbeiten");
        $content->setVariable("COPY_LABEL", "Kopieren");
        $content->setVariable("DELETE_LABEL", "Löschen");

        $content->setVariable("QUESTION_TEXT", rawurldecode($this->text));
        $content->setVariable("QUESTION_TO", $this->to);

        $data = "10," . rawurlencode($this->text) . "," . rawurlencode($this->to);
        $content->setVariable("ELEMENT_DATA", $data);
        $content->parse("BLOCK_EDIT");
        return $content->get();
    }

    public function getViewHTML($number = -1, $questions, $objId) {

        $QuestionnaireExtension = \Questionnaire::getInstance();
        $content = $QuestionnaireExtension->loadTemplate("layoutelements/jumpLabel.template.html");
        $content->setCurrentBlock("BLOCK_VIEW");
        if ($number !== -1) {
            $content->setVariable("NUMBER", $number);
        }
        $content->setVariable("QUESTION_TEXT", rawurldecode($this->text));
        $content->setVariable("QUESTION_TO", $this->to);

        $pageNumber = self::getPage($questions, $this->to);
        if (intval($pageNumber) === -1) {

            $url = "#";
        } else {
            if (strpos($_SERVER["REQUEST_URI"], "preview") !== false) {
                $url = PATH_URL . 'questionnaire/view/' . $objId . "/" . $pageNumber . '/preview/#' . ($this->to);
            } else {
                $url = PATH_URL . 'questionnaire/view/' . $objId . "/" . $pageNumber . '/#' . ($this->to);
            }
        }
        $content->setVariable("JUMP_TO", $url);
        $content->parse("BLOCK_VIEW");
        return $content->get();
    }

    public function getResultHTML() {
        return "";
    }

    /*
     * determines the page which include question _to
     */

    private static function getPage($questions, $to) {

        if ($to <= 0 || $to >= count($questions)) {
            return -1;
        }
        $questionCounter = 0;
        $pageCounter = 1;
        foreach ($questions as $q) {
            if ($q instanceof AbstractQuestion) {
                $questionCounter++;
                if (intval($questionCounter) === intval($to)) {

                    return $pageCounter;
                }
            } else if ($q instanceof PageBreakLayoutElement) {
                $pageCounter++;
            }
        }


        return -1;
    }

}

?>

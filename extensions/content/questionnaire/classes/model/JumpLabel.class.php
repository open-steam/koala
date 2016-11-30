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

    public function getEditHTML($questionnaireId, $id, $number = -1) {
        $QuestionnaireExtension = \Questionnaire::getInstance();
        $content = $QuestionnaireExtension->loadTemplate("layoutelements/jumpLabel.template.html");
        $content->setCurrentBlock("BLOCK_EDIT");
        if ($number !== -1) {
            $content->setVariable("NUMBER", $number);
        }
        $content->setVariable("ELEMENT_ID", $id);
        $content->setVariable("QUESTION_TEXT", rawurldecode($this->text));
        $content->setVariable("QUESTION_TO", $this->to);

        $data = "10," . rawurlencode($this->text) . "," . rawurlencode($this->to);
        $content->setVariable("ELEMENT_DATA", $data);

        $popupMenu = new \Widgets\PopupMenu();
    		$popupMenu->setCommand("GetPopupMenuEdit");
    		$popupMenu->setNamespace("Questionnaire");
    		$popupMenu->setData(\steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $questionnaireId));
    		$popupMenu->setElementId("edit-overlay");
    		$popupMenu->setParams(array(array("key" => "questionId", "value" => $id), array("key" => "layoutElement", "value" => true)));
    		$content->setVariable("POPUPMENUANKER", $popupMenu->getHtml());

        $content->parse("BLOCK_EDIT");
        return $content->get();
    }

    public function getViewHTML($number = -1, $questions, $objId, $params) {

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

            // check if displaying preview or result
            $showPreview = 0;
            $showResult = 0;
            $disabled = 0;
            if (isset($params[2])){
              if($params[2] == "preview") {
                $showPreview = 1;
              }else{
                $showResult = 1;
              }
            }

            if (isset($params[3])){
              $disabled = 1;
            }

            if ($showPreview) {
                $url = PATH_URL . 'questionnaire/view/' . $objId . "/" . $pageNumber . '/preview/#' . ($this->to);
            } else if($showResult){
              if($disabled){
                $url = PATH_URL . 'questionnaire/view/' . $objId . "/" . $pageNumber . "/" . $params[2] . "/" . $params[3] . "/#" . ($this->to);
              }
              else{
                $url = PATH_URL . 'questionnaire/view/' . $objId . "/" . $pageNumber . "/" . $params[2] . "/#" . ($this->to);
              }
            }
            else{
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

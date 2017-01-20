<?php

namespace Questionnaire\Model;

class PageBreakLayoutElement extends AbstractLayoutElement {

    function __construct() {

    }

    public function saveXML($layoutElement) {
        $layoutElement->addChild("type", 9);
        return $layoutElement;
    }

    public function getEditHTML($questionnaireId, $id, $number = -1) {
        $QuestionnaireExtension = \Questionnaire::getInstance();
        $content = $QuestionnaireExtension->loadTemplate("layoutelements/pagebreak.template.html");
        $content->setCurrentBlock("BLOCK_EDIT");
        if ($number != -1) {
            $content->setVariable("NUMBER", $number);
        }
        $content->setVariable("ELEMENT_ID", $id);
        $content->setVariable("PAGEBREAK_LABEL", "Seitenumbruch");
        $data = "9";
        $content->setVariable("ELEMENT_DATA", $data);

        $popupMenu = new \Widgets\PopupMenu();
    		$popupMenu->setCommand("GetPopupMenuEdit");
    		$popupMenu->setNamespace("Questionnaire");
    		$popupMenu->setData(\steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $questionnaireId));
    		$popupMenu->setElementId("edit-overlay");
    		$popupMenu->setParams(array(array("key" => "questionId", "value" => $id), array("key" => "layoutElement", "value" => "pagebreak")));
    		$content->setVariable("POPUPMENUANKER", $popupMenu->getHtml());

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

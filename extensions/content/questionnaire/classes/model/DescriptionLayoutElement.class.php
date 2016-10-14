<?php

namespace Questionnaire\Model;

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

    public function getEditHTML($questionnaireId, $id, $number = -1) {
        $QuestionnaireExtension = \Questionnaire::getInstance();
        $content = $QuestionnaireExtension->loadTemplate("layoutelements/description.template.html");
        $content->setCurrentBlock("BLOCK_EDIT");
        if ($number != -1) {
            $content->setVariable("NUMBER", $number);
        }
        $content->setVariable("ELEMENT_ID", $id);
        $content->setVariable("DESCRIPTION_LABEL", "Beschreibung");
        $content->setVariable("DESCRIPTION_CONTENT", $this->description);
        $data = "7," . rawurlencode($this->description);
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

    public function getViewHTML($number = -1) {
        $QuestionnaireExtension = \Questionnaire::getInstance();
        $content = $QuestionnaireExtension->loadTemplate("layoutelements/description.template.html");
        $content->setCurrentBlock("BLOCK_VIEW");
        $content->setVariable("DESCRIPTION_CONTENT", $this->description);
        $content->parse("BLOCK_VIEW");
        return $content->get();
    }

    public function getResultHTML() {
        return "";
    }

}

?>

<?php
namespace Questionnaire\Model;
class HeadlineLayoutElement extends AbstractLayoutElement {

	private $headline = "";

	function __construct($layoutElement = null) {
		if ($layoutElement != null) {
			$this->headline = $layoutElement->headline;
		}
	}

	public function setHeadline($headline) {
		$this->headline = $headline;
	}

	public function saveXML($layoutElement) {
		$layoutElement->addChild("type", 8);
		$layoutElement->addChild("headline", $this->headline);
		return $layoutElement;
	}

	public function getEditHTML($questionnaireId, $id, $number = -1) {
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("layoutelements/headline.template.html");
		$content->setCurrentBlock("BLOCK_EDIT");
    if($number != -1){
      $content->setVariable("NUMBER", $number);
    }
		$content->setVariable("ELEMENT_ID", $id);
		$content->setVariable("HEADLINE_LABEL", "Überschrift");
		$content->setVariable("HEADLINE_CONTENT", $this->headline);
		$data = "8," . rawurlencode($this->headline);
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
		$content = $QuestionnaireExtension->loadTemplate("layoutelements/headline.template.html");
		$content->setCurrentBlock("BLOCK_VIEW");
                if($number !== -1){
                    $content->setVariable("HEADLINE_CONTENT", ($number).". " .$this->headline);
                }else{
                    $content->setVariable("HEADLINE_CONTENT", $this->headline);
                }
		$content->parse("BLOCK_VIEW");
		return $content->get();
	}

	public function getResultHTML() {
		return "";
	}
}
?>

<?php
namespace Rapidfeedback\Model;
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
	
	public function getEditHTML($id, $number = -1) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("layoutelements/headline.template.html");
		$content->setCurrentBlock("BLOCK_EDIT");
                 if($number != -1){
                    $content->setVariable("NUMBER", $number);
                }
		$content->setVariable("ELEMENT_ID", $id);
		$content->setVariable("ASSETURL", $RapidfeedbackExtension->getAssetUrl() . "icons/");
		$content->setVariable("EDIT_LABEL", "Bearbeiten");
		$content->setVariable("COPY_LABEL", "Kopieren");
		$content->setVariable("DELETE_LABEL", "Löschen");
		$content->setVariable("HEADLINE_LABEL", "Überschrift");
		$content->setVariable("HEADLINE_CONTENT", $this->headline);
		$data = "8," . rawurlencode($this->headline);
		$content->setVariable("ELEMENT_DATA", $data);
		$content->parse("BLOCK_EDIT");
		return $content->get();
	}
	
	public function getViewHTML() {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("layoutelements/headline.template.html");
		$content->setCurrentBlock("BLOCK_VIEW");
		$content->setVariable("HEADLINE_CONTENT", $this->headline);
		$content->parse("BLOCK_VIEW");
		return $content->get();
	}
	
	public function getResultHTML() {
		return "";
	}
}
?>
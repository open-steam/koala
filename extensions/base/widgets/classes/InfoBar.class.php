<?php
namespace Widgets;

class InfoBar extends Widget {
	
	private $headline;
	private $paragraphs;
	
	public function setHeadline($headline) {
		$this->headline = $headline;
	}
	
	public function addParagraph($text) {
		if (!isset($this->paragraphs)) {
			$this->paragraphs = array();
		}
		$this->paragraphs[] = $text;
	}
	
	public function getHtml() {
		$this->getContent()->setVariable("INFOBAR_HEADLINE", $this->headline);
		if (isset($this->paragraphs)) {
			foreach($this->paragraphs as $paragraph) {
				//$this->getContent()->setCurrentBlock("INFOBAR_PARAGRAPH");
				$this->getContent()->setVariable("INFOBAR_PARAGRAPH", $paragraph);
				//$this->getContent()->parseBlock("INFOBAR_PARAGRAPH");
			}	
		}	
		return $this->getContent()->get();
	}
}
?>
<?php
namespace Widgets;

class PortfolioViewBox extends Widget {
	
	private $id;
	private $title = "";
	private $titleLink = "";
	private $content = "";
	private $contentMore = "";
	private $contentMoreLink = "";
	private $buttons = "";
	
	
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function setTitleLink($titleLink) {
		$this->titleLink = $titleLink;
	}
	
	public function setContent($content) {
		$this->content = $content;
	}
	
	public function setContentMore($contentMore) {
		$this->contentMore = $contentMore;
	}
	
	public function setContentMoreLink($contentMoreLink) {
		$this->contentMoreLink = $contentMoreLink;
	}
	
	
	public function setButtons($buttons) {
		$this->buttons = $buttons;
	}

	
	
	
	
	
	public function getHtml() {
		if (isset($this->id)) {
			$this->getContent()->setVariable("BOX_ID", $this->id);
			$this->getContent()->setVariable("BOX_ID_CLOSE", "li");
		}
		
		$this->getContent()->setVariable("BOX_TITLE", $this->title);
		$this->getContent()->setVariable("BOX_TITLE_LINK_URL", $this->titleLink);
		$this->getContent()->setVariable("BOX_CONTENT_HTML", $this->content);
		
		$this->getContent()->setVariable("BOX_CONTENT_MORE_LINK_TEXT", $this->contentMore);
		$this->getContent()->setVariable("BOX_CONTENT_MORE_LINK_URL", $this->contentMoreLink);
		
		if ($this->buttons)
		foreach ($this->buttons as $button) {
			$this->getContent()->setCurrentBlock("BLOCK_BOX_BUTTON");
			$this->getContent()->setVariable("BOX_BUTTON_TEXT", $button["name"]);
			$this->getContent()->setVariable("BOX_BUTTON_LINK_URL", $button["link"]);
			$this->getContent()->parse("BLOCK_BOX_BUTTON");
		}
	
		return $this->getContent()->get();
	}
}
?>
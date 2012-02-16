<?php
namespace Widgets;

class Box extends Widget {
	
	private $id;
	private $title = "";
	private $titleLink = "";
	private $content = "";
	private $contentMore = "";
	private $contentMoreLink = "";
	private $movable = false;
	private $customStyle = "";
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function setTitleLink($titleLink) {
		$this->titleLink = $titleLink;
	}
	
	public function setMovable($movable) {
		$this->movable = $movable;
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
	
	public function setCustomStyle($customStyle) {
		$this->customStyle = $customStyle;
	}
	
	public function getHtml() {
		if (isset($this->id)) {
			$this->getContent()->setVariable("BOX_ID", $this->id);
			$this->getContent()->setVariable("BOX_ID_CLOSE", "li");
		}
		
		$this->getContent()->setVariable("CUSTOM_STYLE", $this->customStyle);
		
		if ($this->movable) {
			$this->getContent()->setVariable("BOX_MOVABLE", "movable");
		}
		
		$this->getContent()->setVariable("BOX_TITLE", $this->title);
		$this->getContent()->setVariable("BOX_TITLE_LINK_URL", $this->titleLink);
		$this->getContent()->setVariable("BOX_CONTENT_HTML", $this->content);
		
		$this->getContent()->setVariable("BOX_CONTENT_MORE_LINK_TEXT", $this->contentMore);
		$this->getContent()->setVariable("BOX_CONTENT_MORE_LINK_URL", $this->contentMoreLink);
	
		return $this->getContent()->get();
	}
}
?>
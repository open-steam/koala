<?php
namespace Widgets;

class CaptionImage extends Widget {
	
	private $link;
	private $linkText;
	private $imageSrc;
	private $imageAlt;
	private $imageTitle;
	public function setLink($link) {
		$this->link = $link;
	}
	
	public function setLinkText($linkText) {
		$this->linkText = $linkText;
	}
	
	public function setImageSrc($imageSrc) {
		$this->imageSrc = $imageSrc;
	}
	
	public function setImageAlt($imageAlt) {
		$this->imageAlt = $imageAlt;
	}
	
	public function setImageTitle($imageTitle) {
		$this->imageTitle = $imageTitle;
	}
	
	public function getHtml() {
		$this->getContent()->setVariable("CAPTIONIMAGE_LINK", $this->link);
		$this->getContent()->setVariable("CAPTIONIMAGE_LINK_TEXT", $this->linkText);
		$this->getContent()->setVariable("CAPTIONIMAGE_SRC", $this->imageSrc);
		$this->getContent()->setVariable("CAPTIONIMAGE_ALT", $this->imageAlt);
		$this->getContent()->setVariable("CAPTIONIMAGE_TITLE", $this->imageTitle);
		return $this->getContent()->get();
	}
}
?>
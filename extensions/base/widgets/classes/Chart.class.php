<?php
namespace Widgets;

class Chart extends Widget {

	private $id;
	private $title = "";
	private $titleLink = "";
	private $data = "";
	private $urlData = "";
	
	private $contentMore = "";
	private $contentMoreLink = "";
	private $movable = false;
	private $customStyle = "";
	private $hAxisTitle = "";
	private $vAxisTitle = "";
	private $description = "";
	private $seriesType = "bars";

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

	public function setData($array) {
		$this->data = $array;
	}

	public function setUrlData($array) {
		$this->urlData = $array;
	}

	public function setVAxisTitle($title){
		$this->vAxisTitle = $title;
	}

	public function setHAxisTitle($title){
		$this->hAxisTitle = $title;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function setSeriesType($seriesType){
		$this->seriesType = $seriesType;
	}

	public function setContentMoreLink($contentMoreLink) {
		$this->contentMoreLink = $contentMoreLink;
	}


	public function getHtml() {
		if (isset($this->id)) {
			$this->getContent()->setVariable("BOX_ID", $this->id);
			$this->getContent()->setVariable("BOX_ID_CLOSE", "li");
		}

		$this->getContent()->setVariable("CHART_TITLE", $this->title);
		$this->getContent()->setVariable("CHART_VAXIS_TITLE", $this->hAxisTitle);
		$this->getContent()->setVariable("CHART_HAXIS_TITLE", $this->vAxisTitle);
		$this->getContent()->setVariable("CHART_SERIES_TYPE", $this->seriesType);
		$this->getContent()->setVariable("CHART_ID", $this->id);
		$this->getContent()->setVariable("CHART_DATA_JSON", $this->data);
		$this->getContent()->setVariable("URL_DATA_JSON", $this->urlData);
		#		$this->getContent()->setVariable("", $this->);

		if ($this->movable) {
			$this->getContent()->setVariable("BOX_MOVABLE", "movable");
		}

		$this->getContent()->setVariable("BOX_TITLE", $this->title);
		$this->getContent()->setVariable("BOX_TITLE_LINK_URL", $this->titleLink);
		$this->getContent()->setVariable("BOX_CONTENT_HTML", "lala");

		$this->getContent()->setVariable("BOX_CONTENT_MORE_LINK_TEXT", $this->contentMore);
		$this->getContent()->setVariable("BOX_CONTENT_MORE_LINK_URL", $this->contentMoreLink);

		return $this->getContent()->get();
	}
}
?>
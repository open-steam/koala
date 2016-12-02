<?php
namespace Widgets;

class ListViewer extends Widget {

	private $headlineProvider;
	private $contentProvider;
	private $colorProvider;
	private $contentFilter;
	private $content;
	private $userObject = NULL;
	private $length = -1;
	private $filterHidden = TRUE;

	public function setFilterHidden($boolean) {
		$this->filterHidden = $boolean;
	}

	public function setLength($l){
		$this->length = $l;
	}

	public function setHeadlineProvider(IHeadlineProvider $headlineProvider) {
		$this->headlineProvider = $headlineProvider;
	}

	public function setContentProvider(IContentProvider $contentProvider) {
		$this->contentProvider = $contentProvider;
	}

	public function setColorProvider(IColorProvider $colorProvider) {
		$this->colorProvider = $colorProvider;
	}

	public function setContentFilter(IContentFilter $contentFilter) {
		$this->contentFilter = $contentFilter;
	}

	public function setContent($content) {
		$this->content = $content;
	}

	public function getHtml() {
		if (!is_object($this->headlineProvider) || !($this->headlineProvider instanceof IHeadlineProvider)) {
			throw new \Exception("no headlineprovider defined!!");
		}
		if (!is_object($this->contentProvider) || !($this->contentProvider instanceof IContentProvider)) {
			throw new \Exception("no contentprovider defined!!");
		}
		if (!is_array($this->content)) {
			$this->content = array();
		} else {
			foreach ($this->content as $pos => $object) {
				if ($this->contentFilter && $this->contentFilter->filterObject($object)) {
					unset($this->content[$pos]);
				}
			}
		}

		foreach($this->headlineProvider->getHeadlines() as $key => $headline) {
			$this->getContent()->setCurrentBlock("LISTVIEWER_HEAD_ITEM");
			$this->getContent()->setVariable("LISTVIEWER_HEAD_ITEM_NAME", ($headline != "")?$headline:"");
			if(method_exists($this->headlineProvider, "getOnClickHandler")) $this->getContent()->setVariable("LISTVIEWER_ITEM_HEAD_ONCLICK", $this->headlineProvider->getOnClickHandler($headline));
			if(method_exists($this->headlineProvider, "getOnMouseOverHandler")) $this->getContent()->setVariable("LISTVIEWER_ITEM_HEAD_ONMOUSEOVER", $this->headlineProvider->getOnMouseOverHandler($headline));
			if(method_exists($this->headlineProvider, "getOnMouseOutHandler")) $this->getContent()->setVariable("LISTVIEWER_ITEM_HEAD_ONMOUSEOUT", $this->headlineProvider->getOnMouseOutHandler($headline));
			$widths = $this->headlineProvider->getHeadLineWidths();
			$this->getContent()->setVariable("LISTVIEWER_HEAD_ITEM_WIDTH", $widths[$key]);
			$aligns = $this->headlineProvider->getHeadLineAligns();
			$this->getContent()->setVariable("LISTVIEWER_HEAD_ITEM_ALIGN", $aligns[$key]);
			$this->getContent()->parse("LISTVIEWER_HEAD_ITEM");
		}

		if (count($this->content) == 0) {
			$this->getContent()->setCurrentBlock("LISTVIEWER_NOITEMS");
			$this->getContent()->setVariable("LISTVIEWER_NOITEMS_TEXT", $this->contentProvider->getNoContentText());
			$this->getContent()->parse("LISTVIEWER_NOITEMS");
		} else {
			$itemCount = 1;
			foreach($this->content as $contentItem) {
				if ($this->isHiddenItem($contentItem,$itemCount)){$itemCount++;continue;}
				$this->getContent()->setCurrentBlock("LISTVIEWER_ITEM");
				$contentItemId =  $this->contentProvider->getId($contentItem);
				$this->getContent()->setVariable("LISTVIEWER_DATA_ID", $contentItemId);
				$this->getContent()->setVariable("LISTVIEWER_ITEM_ID", $contentItemId);
				$this->getContent()->setVariable("LISTVIEWER_ITEM_ONCLICK", $this->contentProvider->getOnClickHandler($contentItem));
				($this->colorProvider) ? $this->getContent()->setVariable("LISTVIEWER_ITEM_COLOR_LABEL", $this->colorProvider->getColor($contentItem)) : "";
				if ($this->filterHidden && $contentItem->get_attribute("bid:hidden") === "1") {
					$this->getContent()->setVariable("LISTVIEWER_ITEM_HIDDEN", "hiddenObject");
				}
				for ($i = 0; $i < count($this->headlineProvider->getHeadlines()); $i++) {
					$this->getContent()->setCurrentBlock("LISTVIEWER_ITEM_CELL");
					$this->getContent()->setVariable("LISTVIEWER_ITEM_CELL_ID", $contentItemId . "_" . $i);
					$contentItemData = $this->contentProvider->getCellData($i, $contentItem);
					if ($contentItemData instanceof Widget) {
						$this->getContent()->setVariable("LISTVIEWER_ITEM_CELL_DATA", $contentItemData->getHtml());
						$this->addWidget($contentItemData);
					} else {
						$this->getContent()->setVariable("LISTVIEWER_ITEM_CELL_DATA", ($contentItemData != "")?$contentItemData:"");
					}
					$widths = $this->headlineProvider->getHeadLineWidths();
					$this->getContent()->setVariable("LISTVIEWER_ITEM_CELL_WIDTH", $widths[$i]);
					$aligns = $this->headlineProvider->getHeadLineAligns();
					$this->getContent()->setVariable("LISTVIEWER_ITEM_CELL_ALIGN", $aligns[$i]);
					$this->getContent()->parse("LISTVIEWER_ITEM_CELL");
				}
				$this->getContent()->parse("LISTVIEWER_ITEM");
			}
		}
		return $this->getContent()->get();
	}


  private function isHiddenItem($steamObject,$itemCount=0) {
      if (!$this->filterHidden) return false;

      //cache user object
      if ($this->userObject === NULL){
          $this->userObject = \lms_steam::get_current_user();
      }
      $userObject = $this->userObject;


      //head document
      if(1===$itemCount){
          $env = $steamObject->get_environment();
          $presentation = $env->get_attribute("bid:presentation");
          if($presentation==="head") return true;
      }

      //other
      $userHiddenAttribute = $userObject->get_attribute("EXPLORER_SHOW_HIDDEN_DOCUMENTS");
      $userShowHiddenObjects = false;
      if ($userHiddenAttribute==="TRUE") $userShowHiddenObjects = true;
      if ($userHiddenAttribute==="FALSE") $userShowHiddenObjects = false;
      if($userShowHiddenObjects) return false;

      //hidden item
      $steamObjectHiddenAttribute = $steamObject->get_attribute("bid:hidden");
      if($steamObjectHiddenAttribute==="1"){
          return true;
      }

      return false;
  }
}
?>

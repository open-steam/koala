<?php
namespace Wiki\Commands;
class GetPopupMenuVersion extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $docId;
	private $wikiDocId;
	private $docVersion;
	private $markedfordiff;
	private $prevVersionId;
	private $x, $y, $height, $width;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->docId = $this->params["docId"];
		$this->docVersion = $this->params["docVersion"];
		$this->wikiDocId = $this->params["wikiDocId"];
		$this->markedfordiff = $this->params["markedfordiff"];
		$this->prevVersionId = $this->params["prevVersionId"];
		$this->x = $this->params["x"];
		$this->y = $this->params["y"];
		$this->height = $this->params["height"];
		$this->width = $this->params["width"];
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$explorerAssetUrl = \Explorer::getInstance()->getAssetUrl();
		$restoreIcon = $explorerAssetUrl . "icons/menu/svg/restore.svg";
		$markIcon = $explorerAssetUrl . "icons/unsubscribe.svg";
		$compareIcon = $explorerAssetUrl . "icons/menu/svg/sort_horizontal.svg";
		$compareMarkedIcon = $explorerAssetUrl . "icons/menu/svg/sort_favorites.svg";
		$trashIcon = $explorerAssetUrl . "icons/menu/svg/trash.svg";
		$popupMenu =  new \Widgets\PopupMenu();

		$wiki_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

		//currently markiert

		$currentVersion = $this->docId == $this->id;

		$adminAction = false;
		if(!$currentVersion && $wiki_container->check_access_write()){
			$adminAction = true;
		}

		$items = array();

		if($adminAction){
			array_push($items, array("raw" => "<a href=\"#\" onclick=\"if(confirm('Wollen Sie diese Version wirklich wiederherstellen?')){window.open('" . PATH_URL . "wiki/recover/" . $this->wikiDocId . "/" . $this->docId . "/', '_self');}\";><div><svg><use xlink:href=\"{$restoreIcon}#restore\"/></svg> Wiederherstellen</div></a>"));
			array_push($items, array("raw" => "<a href=\"#\" onclick=\"if(confirm('Wollen Sie diese Version wirklich löschen?')){window.open('" . PATH_URL . "wiki/delete/version/" . $this->docId . "/', '_self');}\";><div><svg><use xlink:href=\"{$trashIcon}#trash\"/></svg> Löschen</div></a>"));
		}

		if($this->markedfordiff != $docId){
			array_push($items, array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "wiki/versions/" . $this->wikiDocId . "/?markedfordiff=" . $this->docId . "', '_self');\";><div><svg><use xlink:href=\"{$markIcon}#unsubscribe\"/></svg> Für Vergleich markieren</div></a>"));
		}

		if($this->markedfordiff != "" && $this->markedfordiff != $docId){
			array_push($items, array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "wiki/compare/" . $this->wikiDocId . "/" . $this->docId . "/" . $this->markedfordiff . "', '_self');\";><div><svg><use xlink:href=\"{$compareMarkedIcon}#sort_favorites\"/></svg> Mit markierter Version vergleichen</div></a>"));
		}

		if($this->docVersion != 1){
			array_push($items, array("raw" => "<a href=\"#\" onclick=\"window.open('" . PATH_URL . "wiki/compare/" . $this->wikiDocId . "/" . $this->docId . "/" . $this->prevVersionId . "');\";><div><svg><use xlink:href=\"{$compareIcon}#sort_horizontal\"/></svg> Mit vorheriger Version vergleichen</div></a>"));
		}

		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 110) . "px", round($this->y + $this->height + 4) . "px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);

		return $ajaxResponseObject;
	}
}
?>

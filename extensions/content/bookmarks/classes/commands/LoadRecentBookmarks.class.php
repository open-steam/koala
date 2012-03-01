<?php
namespace Bookmarks\Commands;
class LoadRecentBookmarks extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $objects;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		
		$this->id = $this->params["id"];
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		if ($object && $object instanceof \steam_container) {
			$this->objects = array_splice($object->get_inventory(CLASS_LINK), 0, 5);
		} else {
			$this->objects = array();
		}
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$listViewer = new \Widgets\ListViewer();
		
		$listViewer->setHeadlineProvider(new HeadlineProvider());
		$listViewer->setContentProvider(new ContentProvider());
		$listViewer->setColorProvider(new ColorProvider());
		$listViewer->setContentFilter(new ContentFilter());
		$listViewer->setContent($this->objects);
		$ajaxResponseObject->setStatus("ok");
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->addWidget($listViewer);
		$rawHtml->setHtml($listViewer->getHtml() . "<br><a class=\"pill button\" href=\"" . PATH_URL . "bookmarks/\">Alle Lesezeichen anzeigen</a>");
		
		$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;
	}
}

class HeadlineProvider implements \Widgets\IHeadlineProvider {
	public function getHeadlines() {
		//return array("", "Name", "Marker","Änderungsdatum", "Größe");
		return array("", "", "");
	}
	
	public function getHeadLineWidths() {
		return array(22, 580, 150);
		//return array(22, 200, 130);
	}
	
	public function getHeadLineAligns() {
		return array("left", "left", "right");
	}
}

class ContentProvider implements \Widgets\IContentProvider {
	
	private $rawImage = 0;
	private $rawName = 1;
	private $rawChangeDate = 2;
	
	public function getId($contentItem) {
		return $contentItem->get_id();
	}
	
	public function getCellData($cell, $contentItem) {
		if (!is_int($cell)) {
			throw new \Exception("cell must be an integer!!");
		}
		
		if ($cell == $this->rawImage) {
			return "<img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($contentItem->get_source_object())."\"></img>";
		} else if ($cell == $this->rawName) {
			$url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->get_source_object()->get_id(), "view");
			$desc = $contentItem->get_source_object()->get_attribute("OBJ_DESC");
			$name = getCleanName($contentItem, 50);
			if (isset($url) && $url != "") {
				return "<a href=\"".$url."\" title=\"$desc\"> " . $name ."</a>";
			} else {
				return $name;
			}
		} else if ($cell == $this->rawChangeDate) {
			return getReadableDate($contentItem->get_source_object()->get_attribute("OBJ_LAST_CHANGED"));
		}
	}
	
	public function getNoContentText() {
		return "Hier können Sie Lesezeichen zu beliebigen Ordnern und Dokumenten anlegen. Wenn Sie den Inhalt eines Ordners betrachten, können Sie Lesezeichen anlegen.";
	}
	
	public function getOnClickHandler($contentItem) {
		return "";
	}
}

class ColorProvider implements \Widgets\IColorProvider {
	
	public function getColor($contentItem) {
		$color = $contentItem->get_attribute("OBJ_COLOR_LABEL");
		return ($color === 0) ? "" : $color;
	}
	
}

class ContentFilter implements \Widgets\IContentFilter {

	public function filterObject($object) {
		if ($object instanceof \steam_user) {
			return true;
		} else {
			return false;
		}
	}
	
}
?>
<?php
namespace School\Commands;
class LoadRecentSchoolBookmarks extends \AbstractCommand implements \IAjaxCommand {
	
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
		$rawHtml->setHtml($listViewer->getHtml() . "<br><a class=\"pill button\" href=\"" . PATH_URL . "school/\">Alle Schul-Lesezeichen anzeigen</a>");
		
		$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;
	}
}

class HeadlineProvider implements \Widgets\IHeadlineProvider {
	public function getHeadlines() {
		//return array("", "Name", "Marker","Änderungsdatum", "Größe");
		return array("", "", "", "");
	}
	
	public function getHeadLineWidths() {
		return array(22, 140, 50, 150);
	}
	
	public function getHeadLineAligns() {
		return array("left", "left", "right", "right");
	}
}

class ContentProvider implements \Widgets\IContentProvider {
	
	private $rawImage = 0;
	private $rawName = 1;
	private $rawMarker = 2;
	private $rawChangeDate = 3;
	
	public function getId($contentItem) {
		return $contentItem->get_id();
	}
	
	public function getCellData($cell, $contentItem) {
		if (!is_int($cell)) {
			throw new \Exception("cell must be an integer!!");
		}
		
		if ($cell == $this->rawImage) {
			return "<img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($contentItem->get_link_object())."\"></img>";
		} else if ($cell == $this->rawName) {
			$url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->get_link_object()->get_id(), "view");
			$desc = $contentItem->get_link_object()->get_attribute("OBJ_DESC");
			if (!($desc === false || $desc === 0 || $desc === "")) {
				$name = $desc;
			} else {
				$name = str_replace("%20", " ", $contentItem->get_link_object()->get_name());
			}
			if ($name == "Trashbin") {
				$name = "Papierkorb";
			}
			if (isset($url) && $url != "") {
				return "<a href=\"".$url."\" title=\"$desc\"> " . $name ."</a>";
			} else {
				return $name;
			}
		} else if ($cell == $this->rawMarker) {
			$html = "";
			if ($contentItem instanceof \steam_exit) {
				$obj = $contentItem->get_exit();
			} else if ($contentItem instanceof \steam_link) {
				$obj = $contentItem->get_link_object();
			} else {
				$obj = $contentItem;
			}
			$html .= "<div class=\"marker\">" . \Explorer\Model\Sanction::getMarkerHtml($obj) . "</div>";
			return $html;
		} else if ($cell == $this->rawChangeDate) {
			return getReadableDate($contentItem->get_link_object()->get_attribute("OBJ_LAST_CHANGED"));
		}
	}
	
	public function getNoContentText() {
		return "Hier können Sie Lesezeichen zu Ordnern und Dokumenten Ihrer Schule anlegen. Sie finden Ihre Schule über das Menü »Übergreifendes«. In der Ordneransicht können Sie dann Schullesezeichen anlegen.";
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
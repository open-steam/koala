<?php
namespace Explorer\Commands;
class LoadRecentDocuments extends \AbstractCommand implements \IAjaxCommand {
	
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
		
		$docs = $this->getDocumentsRecursive($object);
		usort($docs, array($this, "lastChanged"));
		$this->objects = array_splice($docs, 0, 5);
	}
	
	private function lastChanged($a, $b) {
		if ($a->get_attribute(OBJ_LAST_CHANGED) < $b->get_attribute(OBJ_LAST_CHANGED)) {
			return 1;
		} else {
			return -1;
		}
	}
	
	private function getDocumentsRecursive($object) {
		$result = array();
		$type = getObjectType($object);
		if ($type === "container" || $type === "room" || $type === "userHome") {
			$result = $object->get_inventory();
			foreach ($result as $key => $doc) {
				$typeDoc = getObjectType($doc);
				if (!($typeDoc === "document" || $typeDoc === "forum" || $typeDoc === "portal" || $typeDoc === "gallery")) {
					unset($result[$key]);
				}
			}
			$containers = $object->get_inventory(CLASS_CONTAINER | CLASS_ROOM);
			foreach ($containers as $container) {
				$result = array_merge($result, $this->getDocumentsRecursive($container));
			}
		}
		return $result;
	}
	
	private function lastObjects($objects, $attribute, $count) {
		$array = array();
		foreach ($objects as $object) {
			$array[$object->get_attribute($attribute)] = $object;
		}
		$keys = array_keys($array);
		sort($keys);
		$result = array();
		foreach ($keys as $key) {
			$result[] = $array[$key];
		}
		$value = array();
		for ($i = 0; $i < $count && !empty($result); $i++) {
			$value[] = array_pop($result);
		}
		return $value;
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
		$rawHtml->setHtml($listViewer->getHtml() . "<br><a class=\"pill button\" href=\"" . PATH_URL . "explorer/\">Alle Dokumente anzeigen</a>");
		
		$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;
	}
}

class HeadlineProvider implements \Widgets\IHeadlineProvider {
	public function getHeadlines() {
		//return array("", "Name", "Marker","Änderungsdatum", "Größe");
		return array("", "", "","", "");
	}
	
	public function getHeadLineWidths() {
		return array(22, 450, 50, 150, 80);
	}
	
	public function getHeadLineAligns() {
		return array("left", "left", "right", "right", "right");
	}
}

class ContentProvider implements \Widgets\IContentProvider {
	
	private $rawImage = 0;
	private $rawName = 1;
	private $rawMarker = 2;
	private $rawChangeDate = 3;
	private $rawSize = 4;
	
	public function getId($contentItem) {
		return $contentItem->get_id();
	}
	
	public function getCellData($cell, $contentItem) {
		if (!is_int($cell)) {
			throw new \Exception("cell must be an integer!!");
		}
		
		if ($cell == $this->rawImage) {
                        $url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->get_id(), "view");
			return "<a href=\"" . $url . "\"><img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($contentItem)."\"></img></a>";
		} else if ($cell == $this->rawName) {
			$url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->get_id(), "view");
			$desc = $contentItem->get_attribute("OBJ_DESC");
			$name = getCleanName($contentItem, 50);
			if (isset($url) && $url != "") {
				return "<a href=\"".$url."\" title=\"$desc\"> " . $name ."</a>";
			} else {
				return $name;
			}
		} else if ($cell == $this->rawMarker) {
			return "<div></div>";//speed test //TODO: fix
			$html = "";
			$html .= "<div class=\"marker\">" . \Explorer\Model\Sanction::getMarkerHtml($contentItem) . "</div>";
			$html .= "<div class=\"marker\" id=\"{$contentItem->get_id()}_BookmarkMarkerWrapper\">";
			$linkError = false;
			if ($contentItem instanceof \steam_exit) {
				$exitObject = $contentItem->get_exit();
				if ($exitObject instanceof \steam_object) { 
					$id = $exitObject->get_id();
				} else {
					$linkError = true;
					$html .= "<div style=\"color:red\">Referenz defekt</div>";
				}
			} else if ($contentItem instanceof \steam_link) {
				$linkObject = $contentItem->get_link_object();
				if ($linkObject instanceof \steam_object) {
					$id = $linkObject->get_id();
				} else {
					$linkError = true;
					$html .= "<div style=\"color:red\">Referenz defekt</div>";
				}
			} else {
				$id = $contentItem->get_id();
			}
			if (!$linkError && \Bookmarks\Model\Bookmark::isBookmark($id)) {
				$html .= \Bookmarks\Model\Bookmark::getMarkerHtml($id);
			}
			$html .= "</div>";
			return $html;
		} else if ($cell == $this->rawChangeDate) {
			return getReadableDate($contentItem->get_attribute("OBJ_LAST_CHANGED"));
		}  else if ($cell == $this->rawSize) {
			return getObjectReadableSize($contentItem);
		}
	}
	
	public function getNoContentText() {
		return "Hier werden die zuletzt von Ihnen innerhalb Ihres persönlichen Arbeitsbereichs bearbeiteten Dokumente angezeigt. Dieser Bereich füllt sich automatisch.";
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
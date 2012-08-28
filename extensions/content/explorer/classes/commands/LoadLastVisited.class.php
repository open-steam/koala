<?php
namespace Explorer\Commands;
class LoadLastVisited extends \AbstractCommand implements \IAjaxCommand {
	
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
			$this->objects = $object->get_inventory();
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
		//$ajaxResponseObject->addWidget($listViewer);
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml("Im Moment ...sinnvoller Text Ut nulla. Vivamus bibendum, nulla ut congue fringilla, lorem ipsum ultricies risus, ut rutrum velit tortor vel purus. In hac habitasse platea dictumst. Duis fermentum, metus sed congue gravida, arcu dui ornare urna, ut imperdiet enim odio dignissim ipsum. Nulla facilisi. Cras magna ante, bibendum sit amet, porta vitae, laoreet ut, justo. Nam tortor sapien, pulvinar nec, malesuada in, ultrices in, tortor. Cras ultricies placerat eros. Quisque odio eros, feugiat non, iaculis nec, lobortis sed, arcu. Pellentesque sit amet sem et purus pretium consectetuer.");
		$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;
	}
}

class HeadlineProvider implements \Widgets\IHeadlineProvider {
	public function getHeadlines() {
		return array("", "Name", "Marker","Änderungsdatum", "Größe");
	}
	
	public function getHeadLineWidths() {
		return array(20, 315, 100, 150, 80);
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
			$name = getCleanName($contentItem);
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
			if ($contentItem instanceof \steam_document) {
				return getReadableSize($contentItem->get_content_size());
			} else if ($contentItem instanceof \steam_container) {
				try {
					$html = "<div style=\"color: #ccc\">" . count($contentItem->get_inventory()) . " Objekte</div>";
				} catch (\steam_exception $e) {
					$html = "keine Berechtigung";
				}
				return $html;
			}
		}
	}
	
	public function getNoContentText() {
		return "Dieser Ordner enthält keine Objekte.";
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
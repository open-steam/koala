<?php
namespace School\Commands;
class LoadSchoolBookmarks extends \AbstractCommand implements \IAjaxCommand {
	
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
			$this->objects = $object->get_inventory(CLASS_LINK);
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
		$ajaxResponseObject->addWidget($listViewer);
		return $ajaxResponseObject;
	}
}

class HeadlineProvider implements \Widgets\IHeadlineProvider {
	public function getHeadlines() {
		return array("", "", "Name", "Marker","Änderungsdatum", "Größe", "");
	}
	
	public function getHeadLineWidths() {
		return array(20, 20, 315, 100, 150, 80, 203);
	}
	
	public function getHeadLineAligns() {
		return array("left", "left", "left", "right", "right", "right", "right");
	}
}

class ContentProvider implements \Widgets\IContentProvider {
	
	private $rawCheckbox = 0;
	private $rawImage = 1;
	private $rawName = 2;
	private $rawMarker = 3;
	private $rawChangeDate = 4;
	private $rawSize = 5;
	private $rawMenu = 6;
	
	public function getId($contentItem) {
		return $contentItem->get_id();
	}
	
	public function getCellData($cell, $contentItem) {
		if (!is_int($cell)) {
			throw new \Exception("cell must be an integer!!");
		}
		
		if ($cell == $this->rawCheckbox) {
			return "<input style=\"margin-top:-4px\" type=\"checkbox\" onclick=\"event.stopPropagation(); if(this.checked) { jQuery('#{$contentItem->get_id()}').removeClass('listviewer-item-unhover').addClass('listviewer-item-selected') } else { jQuery('#{$contentItem->get_id()}').removeClass('listviewer-item-selected').addClass('listviewer-item-unhover') }\"></input>";
		} else if ($cell == $this->rawImage) {
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
			if ($contentItem->get_link_object()->check_access_read(\steam_factory::get_user($GLOBALS["STEAM"]->get_id(), STEAM_GUEST_LOGIN)) || $contentItem->get_link_object()->check_access_read(\steam_factory::get_group($GLOBALS["STEAM"]->get_id(),"steam"))) {
				$html .= "<img onclick=\"event.stopPropagation(); this.src='".PATH_URL."bookmarks/asset/icons/star_inactive_16.png' \" style=\"cursor: pointer\" title=\"Öffentlich lesbar.\" src=\"".PATH_URL."explorer/asset/icons/public_16.png\"></img>";
			}
			//$html .= "<img onclick=\"event.stopPropagation(); this.src='".PATH_URL."bookmarks/asset/icons/star_inactive_16.png' \" style=\"cursor: pointer\" title=\"Als Lesezeichen markiert.\" src=\"".PATH_URL."bookmarks/asset/icons/star_16.png\"></img>";
			return $html;
		} else if ($cell == $this->rawChangeDate) {
			return getReadableDate($contentItem->get_link_object()->get_attribute("OBJ_LAST_CHANGED"));
		}  else if ($cell == $this->rawSize) {
			if ($contentItem->get_link_object() instanceof \steam_document) {
				return getReadableSize($contentItem->get_link_object()->get_content_size());
			} else if ($contentItem->get_link_object() instanceof \steam_container) {
				try {
					$html = "<div style=\"color: #ccc\">" . count($contentItem->get_link_object()->get_inventory()) . " Objekte</div>";
				} catch (\steam_exception $e) {
					$html = "keine Berechtigung";
				}
				return $html;
			}
		} else if ($cell == $this->rawMenu) {
			$popupMenu = new \Widgets\PopupMenu();
			$popupMenu->setData($contentItem);
			$popupMenu->setElementId("listviewer-overlay");
			return $popupMenu;
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
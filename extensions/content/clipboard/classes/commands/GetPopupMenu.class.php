<?php
namespace Clipboard\Commands;
class GetPopupMenu extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $selection;
	private $x, $y, $height, $width;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->selection = json_decode($this->params["selection"]);
		$this->x = $this->params["x"];
		$this->y = $this->params["y"];
		$this->height = $this->params["height"];
		$this->width = $this->params["width"];
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$count = count($this->selection);
		$explorerUrl = \Explorer::getInstance()->getAssetUrl();
		if (!in_array($this->id, $this->selection) ||(in_array($this->id, $this->selection) && $count == 1)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			$env = $object->get_environment();

			$inventory = $env->get_inventory();
			foreach ($inventory as $key => $element) {
				if ($element->get_id() == $this->id) {
					$index = $key;
				}
			}

			$popupMenu =  new \Widgets\PopupMenu();

			if ($object instanceof \steam_trashbin) {
				$items = array(array("name" => "Papierkorb leeren", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"));
			} else if ($env instanceof \steam_trashbin) {
				$restoreIcon = $explorerUrl . "icons/menu/restore.png";
				$items = array(
					array("name" => "<img src=\"{$restoreIcon}\">Wiederherstellen", "command" => "Restore", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'env':document.getElementById('environment').value}", "type" => "nonModalUpdater"));
			} else {
				$copyIcon = $explorerUrl . "icons/menu/copy.png";
				$cutIcon = $explorerUrl . "icons/menu/cut.png";
				$referIcon = $explorerUrl . "icons/menu/refer.png";
				$trashIcon = $explorerUrl . "icons/menu/trash.png";
				$hideIcon = $explorerUrl . "icons/menu/hide.png";
				$bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
				$colorpickerIcon = \Portal::getInstance()->getAssetUrl() . "icons/colorpicker.png";
				$sortIcon = $explorerUrl . "icons/menu/sort.png";
				$upIcon = $explorerUrl . "icons/menu/up.png";
				$downIcon = $explorerUrl . "icons/menu/down.png";
				$topIcon = $explorerUrl . "icons/menu/top.png";
				$bottomIcon = $explorerUrl . "icons/menu/bottom.png";
				$renameIcon = $explorerUrl . "icons/menu/rename.png";
				$editIcon = $explorerUrl . "icons/menu/edit.png";
				$propertiesIcon = $explorerUrl . "icons/menu/properties.png";
				$rightsIcon = $explorerUrl . "icons/menu/rights.png";
				$blankIcon = $explorerUrl . "icons/menu/blank.png";
				$items = array(
					array("name" => "<img src=\"{$trashIcon}\">Löschen", "command" => "Delete", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "nonModalUpdater"),
					(!\Bookmarks\Model\Bookmark::isBookmark($this->id)) ? array("name" => "<img src=\"{$bookmarkIcon}\">Lesezeichen anlegen", "command" => "AddBookmark", "namespace" => "bookmarks", "elementId" => "{$this->id}_BookmarkMarkerWrapper", "params" => "{'id':'{$this->id}'}", "type" => "inform") : "",
					array("name" => "<img src=\"{$colorpickerIcon}\">Einfärben", "direction" => "right", "menu" => array (
						array("raw" => " <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'transparent'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$explorerUrl}icons/transparent.png\"></a>
														 <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'red'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$explorerUrl}icons/red.png\"></a>
														 <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'orange'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$explorerUrl}icons/orange.png\"></a>
														 <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'yellow'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$explorerUrl}icons/yellow.png\"></a>
														 <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'green'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$explorerUrl}icons/green.png\"></a>
														 <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'blue'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$explorerUrl}icons/blue.png\"></a>
														 <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'purple'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$explorerUrl}icons/purple.png\"></a>
														 <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'grey'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$explorerUrl}icons/grey.png\"></a>"),
					)),
					(count($inventory) >=2 ) ? array("name" => "<img src=\"{$sortIcon}\">Umsortieren", "direction" => "right", "menu" => array(
						($index != 0) ? array("name" => "<img src=\"{$topIcon}\">Ganz nach oben", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'top'}", "type" => "nonModalUpdater") : "",
						($index != 0) ? array("name" => "<img src=\"{$upIcon}\">Eins nach oben", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'up'}", "type" => "nonModalUpdater") : "",
						($index < count($inventory)-1) ? array("name" => "<img src=\"{$downIcon}\">Eins nach unten", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'down'}", "type" => "nonModalUpdater") : "",
						($index < count($inventory)-1) ? array("name" => "<img src=\"{$bottomIcon}\">Ganz nach unten", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'bottom'}", "type" => "nonModalUpdater") : ""
					)) : "",
					array("name" => "SEPARATOR"),
					array("raw" => "<a href=\"#\" onclick=\"event.stopPropagation(); removeAllDirectEditors();if (!jQuery('#{$this->id}_1').hasClass('directEditor')) { jQuery('#{$this->id}_1').addClass('directEditor').html(''); var obj = new Object; obj.id = '{$this->id}'; sendRequest('GetDirectEditor', obj, '{$this->id}_1', 'nonModalUpdater',null,null,'explorer'); } jQuery('.popupmenuwrapper').parent().html('');jQuery('.open').removeClass('open'); return false;\"><img src=\"{$renameIcon}\">Umbenennen</a>"),
					(($object instanceof \steam_container) && ($object->get_attribute("bid:presentation") === "index")) ? array("name" => "<img src=\"{$blankIcon}\">Listenansicht", "link" => PATH_URL . "Explorer/Index/" . $this->id . "/?view=list") : "",
					(($object instanceof \steam_document) && (strstr($object->get_attribute(DOC_MIME_TYPE), "text"))) ? array("name" => "<img src=\"{$editIcon}\">Bearbeiten", "link" => PATH_URL . "Explorer/EditDocument/" . $this->id . "/") : "",
					array("name" => "<img src=\"{$propertiesIcon}\">Eigenschaften", "command" => "Properties", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"),
					array("name" => "<img src=\"{$rightsIcon}\">Rechte", "command" => "Sanctions", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"));
			}
			$popupMenu->setItems($items);
			$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		} else {
			$copyIcon = $explorerUrl . "icons/menu/copy.png";
			$cutIcon = $explorerUrl . "icons/menu/cut.png";
			$referIcon = $explorerUrl . "icons/menu/refer.png";
			$trashIcon = $explorerUrl . "icons/menu/trash.png";
			$hideIcon = $explorerUrl . "icons/menu/hide.png";
			$blankIcon = $explorerUrl . "icons/menu/blank.png";
			$colorpickerIcon = \Portal::getInstance()->getAssetUrl() . "icons/colorpicker.png";
			$popupMenu =  new \Widgets\PopupMenu();
			$items = array(
				array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Delete', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Lösche Objekte ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$trashIcon}\">{$count} Objekte löschen</a>"),
				array("name" => "<img src=\"{$colorpickerIcon}\">{$count} Objekte einfärben", "direction" => "right", "menu" => array (
					array("raw" => " <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'transparent'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$explorerUrl}icons/transparent.png\"></a>
							   					 <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'red'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$explorerUrl}icons/red.png\"></a>
							   					 <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'orange'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$explorerUrl}icons/orange.png\"></a>
							   					 <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'yellow'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$explorerUrl}icons/yellow.png\"></a>
							   					 <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'green'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$explorerUrl}icons/green.png\"></a>
							   					 <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'blue'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$explorerUrl}icons/blue.png\"></a>
							   					 <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'purple'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$explorerUrl}icons/purple.png\"></a>
							   					 <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'grey'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$explorerUrl}icons/grey.png\"></a>"),
					)
				),
			);
			$popupMenu->setItems($items);
			$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
			$popupMenu->setWidth("160px");
		}
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

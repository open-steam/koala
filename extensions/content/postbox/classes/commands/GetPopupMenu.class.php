<?php
namespace Postbox\Commands;
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
		$this->id = $this->params["id"];$this->selection = json_decode($this->params["selection"]);
		$this->x = $this->params["x"];
		$this->y = $this->params["y"];
		$this->height = $this->params["height"];
		$this->width = $this->params["width"];
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$count = count($this->selection);
		$explorerAssetUrl = \Explorer::getInstance()->getAssetUrl();
		if (!in_array($this->id, $this->selection) ||(in_array($this->id, $this->selection) && $count == 1)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			$name = $object->get_name();
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
				$restoreIcon = $explorerAssetUrl . "icons/menu/svg/restore.svg";
				$items = array(
					array("name" => "<svg><use xlink:href='{$restoreIcon}#restore'/></svg> Wiederherstellen", "command" => "Restore", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'env':document.getElementById('environment').value}", "type" => "nonModalUpdater"));
			} else {
				$copyIcon = $explorerAssetUrl . "icons/menu/svg/copy.svg";
				$cutIcon = $explorerAssetUrl . "icons/menu/svg/cut.svg";
				$referIcon = $explorerAssetUrl . "icons/menu/svg/refer.svg";
				$trashIcon = $explorerAssetUrl . "icons/menu/svg/trash.svg";
				$sortIcon = $explorerAssetUrl . "icons/menu/svg/sort.svg";
				$upIcon = $explorerAssetUrl . "icons/menu/svg/up.svg";
				$downIcon = $explorerAssetUrl . "icons/menu/svg/down.svg";
				$topIcon = $explorerAssetUrl . "icons/menu/svg/top.svg";
				$bottomIcon = $explorerAssetUrl . "icons/menu/svg/bottom.svg";
				$renameIcon = $explorerAssetUrl . "icons/menu/svg/rename.svg";
				$editIcon = $explorerAssetUrl . "icons/menu/svg/edit.svg";
				$propertiesIcon = $explorerAssetUrl . "icons/menu/svg/properties.svg";
				$rightsIcon = $explorerAssetUrl . "icons/menu/svg/rights.svg";
				$explorerIcon = $explorerAssetUrl . "icons/menu/svg/explorer.svg";
				$downloadIcon = $explorerAssetUrl . "icons/menu/svg/download.svg";

				$items = array(
					array("name" => "<svg><use xlink:href='{$copyIcon}#copy'/></svg> Kopieren", "command" => "Copy", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform"),
					array("name" => "<svg><use xlink:href='{$cutIcon}#cut'/></svg> Ausschneiden", "command" => "Cut", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform"),
					array("name" => "<svg><use xlink:href='{$referIcon}#refer'/></svg> Referenz erstellen", "command" => "Reference", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform"),
					array("name" => "<svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen", "command" => "Delete", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "nonModalUpdater"),
					(count($inventory) >=2 ) ? array("name" => "<svg><use xlink:href='{$sortIcon}#sort'/></svg> Umsortieren", "direction" => "right", "menu" => array(
						($index != 0) ? array("name" => "<svg><use xlink:href='{$topIcon}#top'/></svg> Ganz nach oben", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'top'}", "type" => "nonModalUpdater") : "",
						($index != 0) ? array("name" => "<svg><use xlink:href='{$upIcon}#up'/></svg> Eins nach oben", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'up'}", "type" => "nonModalUpdater") : "",
						($index < count($inventory)-1) ? array("name" => "<svg><use xlink:href='{$downIcon}#down'/></svg> Eins nach unten", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'down'}", "type" => "nonModalUpdater") : "",
						($index < count($inventory)-1) ? array("name" => "<svg><use xlink:href='{$bottomIcon}#bottom'/></svg> Ganz nach unten", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'bottom'}", "type" => "nonModalUpdater") : ""
				)) : "",
				array("raw" => "<a href=\"#\" onclick=\"event.stopPropagation(); removeAllDirectEditors();if (!jQuery('#{$this->id}_1').hasClass('directEditor')) { jQuery('#{$this->id}_1').addClass('directEditor').html(''); var obj = new Object; obj.id = '{$this->id}'; sendRequest('GetDirectEditor', obj, '{$this->id}_1', 'nonModalUpdater',null,null,'explorer'); } jQuery('.popupmenuwrapper').parent().html('');jQuery('.open').removeClass('open'); jQuery('#footer_wrapper').css('padding-top', '0px'); return false;\"><svg><use xlink:href='{$renameIcon}#rename'/></svg> Umbenennen</a>"),
				(($object instanceof \steam_container) && ($object->get_attribute("bid:presentation") === "index")) ? array("name" => "<svg><use xlink:href='{$explorerIcon}#explorer'/></svg> Listenansicht", "link" => PATH_URL . "Explorer/Index/" . $this->id . "/?view=list") : "",
				(($object instanceof \steam_document) && ($object->get_attribute(DOC_MIME_TYPE) != "text/html") && ($object->check_access(SANCTION_READ))) ? array("name" => "<svg><use xlink:href='{$downloadIcon}#download'/></svg> Herunterladen", "link" => PATH_URL . "Download/Document/" . $this->id . "/" . $name) : "",
				array("name" => "SEPARATOR"),
				array("name" => "<svg><use xlink:href='{$propertiesIcon}#properties'/></svg> Eigenschaften", "command" => "Properties", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"),
				array("name" => "<svg><use xlink:href='{$rightsIcon}#rights'/></svg> Rechte", "command" => "Sanctions", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"));
			}
			$popupMenu->setItems($items);
			$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		}
		else {
			$copyIcon = $explorerAssetUrl . "icons/menu/svg/copy.svg";
			$cutIcon = $explorerAssetUrl . "icons/menu/svg/cut.svg";
			$referIcon = $explorerAssetUrl . "icons/menu/svg/refer.svg";
			$trashIcon = $explorerAssetUrl . "icons/menu/svg/trash.svg";

			$popupMenu =  new \Widgets\PopupMenu();
			$items = array(
			array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Copy', getParamsArray({}), getElementIdArray(''), 'inform', null, null, 'explorer', 'Kopiere Objekte ...', 0,  getSelectionAsArray().length); return false;\"><svg><use xlink:href='{$copyIcon}#copy'/></svg> {$count} Objekte kopieren</a>"),
			array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Cut', getParamsArray({}), getElementIdArray(''), 'inform', null, null, 'explorer', 'Schneide Objekte aus ...', 0,  getSelectionAsArray().length); return false;\"><svg><use xlink:href='{$cutIcon}#cut'/></svg> {$count} Objekte ausschneiden</a>"),
			array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Reference', getParamsArray({}), getElementIdArray(''), 'inform', null, null, 'explorer', 'Referenziere Objekte ...', 0,  getSelectionAsArray().length); return false;\"><svg><use xlink:href='{$referIcon}#refer'/></svg> {$count} Objektreferenzen erstellen</a>"),
			array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Delete', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Lösche Objekte ...', 0,  getSelectionAsArray().length); return false;\"><svg><use xlink:href='{$trashIcon}#trash'/></svg> {$count} Objekte löschen</a>"),
			);
			$popupMenu->setItems($items);
			$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		}
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

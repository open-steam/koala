<?php
namespace Trashbin\Commands;
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
		$explorerAssetUrl = \Explorer::getInstance()->getAssetUrl();
		if (!in_array($this->id, $this->selection) ||(in_array($this->id, $this->selection) && $count == 1)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			$env = $object->get_environment();

			$inventory = $env->get_inventory();
			foreach ($inventory as $key => $element) {
				if ($element->get_id() == $this->id) {
					$index = $key;
				}
			}

			$trashIcon = $explorerAssetUrl . "icons/menu/trash.png";
			$cutIcon = $explorerAssetUrl . "icons/menu/cut.png";
			$popupMenu =  new \Widgets\PopupMenu();

			if ($object instanceof \steam_trashbin) {
				$items = array(array("name" => "Papierkorb leeren", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"));
			} else if ($env instanceof \steam_trashbin) {
				$items = array(
					array("name" => "Endgültig löschen<img src=\"{$trashIcon}\">", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "nonModalUpdater"),
					array("name" => "Ausschneiden<img src=\"{$cutIcon}\">", "command" => "Cut", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform")
				);
			}
			$popupMenu->setItems($items);
			$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
			$popupMenu->setWidth("150px");
		} else {
			$copyIcon = $explorerAssetUrl . "icons/menu/copy.png";
			$cutIcon = $explorerAssetUrl . "icons/menu/cut.png";
			$referIcon = $explorerAssetUrl . "icons/menu/refer.png";
			$trashIcon = $explorerAssetUrl . "icons/menu/trash.png";
			$hideIcon = $explorerAssetUrl . "icons/menu/hide.png";
			$blankIcon = $explorerAssetUrl . "icons/menu/blank.png";
			$popupMenu =  new \Widgets\PopupMenu();
			$items = array(
				array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('EmptyTrashbin', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Lösche Objekte ...', 0,  getSelectionAsArray().length); return false;\">{$count} Objekte endgültig löschen<img src=\"{$trashIcon}\"></a>"),
				array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Cut', getParamsArray({}), getElementIdArray(''), 'inform', null, null, 'explorer', 'Lösche Objekte ...', 0,  getSelectionAsArray().length); return false;\">{$count} Objekte ausschneiden<img src=\"{$cutIcon}\"></a>")
			);

			$popupMenu->setItems($items);
			$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
			$popupMenu->setWidth("200px");
		}
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

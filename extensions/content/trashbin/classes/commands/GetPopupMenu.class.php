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

			$popupMenu =  new \Widgets\PopupMenu();

			if ($object instanceof \steam_trashbin) {
				$items = array(array("name" => "Papierkorb leeren", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"));
			} else if ($env instanceof \steam_trashbin) {
				$trashIcon = $explorerAssetUrl . "icons/menu/svg/trash.svg";
				$cutIcon = $explorerAssetUrl . "icons/menu/svg/cut.svg";

				$items = array(
					array("name" => "<svg><use xlink:href='{$trashIcon}#trash'/></svg> Endgültig löschen", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "nonModalUpdater"),
					array("name" => "<svg><use xlink:href='{$cutIcon}#cut'/></svg> Ausschneiden", "command" => "Cut", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform")
				);
			}
			$popupMenu->setItems($items);
			$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		} else {
			$cutIcon = $explorerAssetUrl . "icons/menu/svg/cut.svg";
			$trashIcon = $explorerAssetUrl . "icons/menu/svg/trash.svg";

			$viewAttribute = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("EXPLORER_VIEW");
			if($viewAttribute && $viewAttribute == "gallery"){
				$paramsArrayFunction = "getGalleryParamsArray";
				$ElementIdFunction = "getGalleryElementIdArray";
				$SelectionFunction = "getGallerySelectionAsArray().length";
			}else{
				$paramsArrayFunction = "getParamsArray";
				$ElementIdFunction = "getElementIdArray";
				$SelectionFunction = "getSelectionAsArray().length";
			}

			$popupMenu =  new \Widgets\PopupMenu();
			$items = array(
				array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('EmptyTrashbin', $paramsArrayFunction({}), $ElementIdFunction(''), 'updater', null, null, 'explorer', 'Lösche Objekte ...', 0, $SelectionFunction); return false;\"><svg><use xlink:href='{$trashIcon}#trash'/></svg> {$count} Objekte endgültig löschen</a>"),
				array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Cut', $paramsArrayFunction({}), $ElementIdFunction(''), 'inform', null, null, 'explorer', 'Lösche Objekte ...', 0, $SelectionFunction); return false;\"><svg><use xlink:href='{$cutIcon}#cut'/></svg> {$count} Objekte ausschneiden</a>")
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

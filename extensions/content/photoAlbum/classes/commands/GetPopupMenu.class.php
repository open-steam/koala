<?php
namespace PhotoAlbum\Commands;
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
		$this->x = $this->params["x"];
		$this->y = $this->params["y"];
		$this->height = $this->params["height"];
		$this->width = $this->params["width"];
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$explorerUrl = \Explorer::getInstance()->getAssetUrl();
		$env = $object->get_environment();

		$inventory = $env->get_inventory();
		foreach ($inventory as $key => $element) {
			if ($element->get_id() == $this->id) {
				$index = $key;
			}
		}

		$popupMenu =  new \Widgets\PopupMenu();

		$copyIcon = $explorerUrl . "icons/menu/copy.png";
		$cutIcon = $explorerUrl . "icons/menu/cut.png";
		$referIcon = $explorerUrl . "icons/menu/refer.png";
		$trashIcon = $explorerUrl . "icons/menu/trash.png";
		$hideIcon = $explorerUrl . "icons/menu/hide.png";
		$bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
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
			array("name" => "Kopieren<img src=\"{$copyIcon}\">", "command" => "Copy", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"),
			array("name" => "Ausschneiden<img src=\"{$cutIcon}\">", "command" => "Cut", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"),
			array("name" => "Referenz erstellen<img src=\"{$referIcon}\">", "command" => "Reference", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"),
			array("name" => "Löschen<img src=\"{$trashIcon}\">", "command" => "Delete", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"),
			(count($inventory) >=2) ? array("name" => "Umsortieren<img src=\"{$sortIcon}\">", "direction" => "left", "menu" => array(
				($index != 0) ? array("name" => "Ganz nach vorne<img src=\"{$topIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'top'}") : "",
				($index != 0) ? array("name" => "Eins nach vorne<img src=\"{$upIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'up'}") : "",
				($index < count($inventory)-1) ? array("name" => "Eins nach hinten<img src=\"{$downIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'down'}") : "",
				($index < count($inventory)-1) ? array("name" => "Ganz nach hinten<img src=\"{$bottomIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'bottom'}") : ""
				)) : "",
		array("name" => "Eigenschaften...<img src=\"{$propertiesIcon}\">", "command" => "Properties", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"));
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		$popupMenu->setWidth("140px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

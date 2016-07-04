<?php
namespace Portal\Commands;
class PortletGetPopupMenuReference extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $object;
	private $x, $y, $height, $width;
	private $sourceObjectId;
	private $linkObjectId;
	private $user;
	private $column;

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

		$this->sourceObjectId = $this->params["sourceObjectId"];
		$this->linkObjectId = $this->params["linkObjectId"];

		$portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->sourceObjectId);
		$this->column = $portletObject->get_environment();
		$portal = $this->column->get_environment();

		$this->sourcePortalObjectId = $portal->get_id();
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user()->get_name();
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		//icons
		$explorerUrl = \Explorer::getInstance()->getAssetUrl();
		$copyIcon = $explorerUrl . "icons/menu/copy.png";
		$cutIcon = $explorerUrl . "icons/menu/cut.png";
		$referIcon = $explorerUrl . "icons/menu/refer.png";
		$trashIcon = $explorerUrl . "icons/menu/trash.png";
		$upIcon = $explorerUrl . "icons/menu/up.png";
		$downIcon = $explorerUrl . "icons/menu/down.png";
		$topIcon = $explorerUrl . "icons/menu/top.png";
		$bottomIcon = $explorerUrl . "icons/menu/bottom.png";
		$rightsIcon = $explorerUrl . "icons/menu/rights.png";
		$blankIcon = $explorerUrl . "icons/menu/blank.png";
		$sortIcon = $explorerUrl . "icons/menu/sort.png";

		$inventory = $this->column->get_inventory();
		$id = intval($this->linkObjectId);
		foreach ($inventory as $key => $element) {
			if ($element->get_id() == $id) {
				$index = $key;
			}
		}

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(
						(count($inventory) > 1) ? array("name" => "Umsortieren<img src=\"{$sortIcon}\">", "direction" => "left", "menu" => array(
							($index != 0) ? array("name" => "Ganz nach oben<img src=\"{$topIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'first'}") : "",
							($index != 0) ? array("name" => "Eins nach oben<img src=\"{$upIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'up'}") : "",
							($index < count($inventory)-1) ? array("name" => "Eins nach unten<img src=\"{$downIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'down'}") : "",
							($index < count($inventory)-1) ? array("name" => "Ganz nach unten<img src=\"{$bottomIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'last'}") : "",
						)) : "",
						array("name" => "SEPARATOR"),
						array("name" => "Kopieren<img src=\"{$copyIcon}\">",  "command" => "PortletCopy", "namespace" => "Portal", "params" => "{'id':'{$this->linkObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "Ausschneiden<img src=\"{$cutIcon}\">",  "command" => "PortletCut", "namespace" => "Portal", "params" => "{'id':'{$this->linkObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "Löschen<img src=\"{$trashIcon}\">",  "command" => "DeleteReference", "namespace" => "PortalColumn", "params" => "{'linkObjectId':'{$this->linkObjectId}'}", "type"=>"popup"),
						array("name" => "SEPARATOR"),
						array("name" => "Rechte<img src=\"{$rightsIcon}\">",  "command" => "Sanctions", "namespace" => "Explorer", "params" => "{'id':'{$this->linkObjectId}'}", "type"=>"popup"),
						);
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		$popupMenu->setWidth("150px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

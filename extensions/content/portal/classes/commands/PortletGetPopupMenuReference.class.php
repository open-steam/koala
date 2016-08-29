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
		$copyIcon = $explorerUrl . "icons/menu/svg/copy.svg";
		$cutIcon = $explorerUrl . "icons/menu/svg/cut.svg";
		$trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";
		$sortIcon = $explorerUrl . "icons/menu/svg/sort.svg";
		$upIcon = $explorerUrl . "icons/menu/svg/up.svg";
		$downIcon = $explorerUrl . "icons/menu/svg/down.svg";
		$topIcon = $explorerUrl . "icons/menu/svg/top.svg";
		$bottomIcon = $explorerUrl . "icons/menu/svg/bottom.svg";
		$rightsIcon = $explorerUrl . "icons/menu/svg/rights.svg";

		$inventory = $this->column->get_inventory();
		$id = intval($this->linkObjectId);
		foreach ($inventory as $key => $element) {
			if ($element->get_id() == $id) {
				$index = $key;
			}
		}

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(
						array("name" => "<svg><use xlink:href='{$copyIcon}#copy'/></svg> Kopieren",  "command" => "PortletCopy", "namespace" => "Portal", "params" => "{'id':'{$this->linkObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "<svg><use xlink:href='{$cutIcon}#cut'/></svg> Ausschneiden",  "command" => "PortletCut", "namespace" => "Portal", "params" => "{'id':'{$this->linkObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "<svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen",  "command" => "DeleteReference", "namespace" => "PortalColumn", "params" => "{'linkObjectId':'{$this->linkObjectId}'}", "type"=>"popup"),
						(count($inventory) > 1) ? array("name" => "<svg><use xlink:href='{$sortIcon}#sort'/></svg> Umsortieren", "direction" => "right", "menu" => array(
							($index != 0) ? array("name" => "<svg><use xlink:href='{$topIcon}#top'/></svg> Ganz nach oben",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'first'}") : "",
							($index != 0) ? array("name" => "<svg><use xlink:href='{$upIcon}#up'/></svg> Eins nach oben",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'up'}") : "",
							($index < count($inventory)-1) ? array("name" => "<svg><use xlink:href='{$downIcon}#down'/></svg> Eins nach unten",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'down'}") : "",
							($index < count($inventory)-1) ? array("name" => "<svg><use xlink:href='{$bottomIcon}#bottom'/></svg> Ganz nach unten",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'last'}") : "",
						)) : "",
						array("name" => "SEPARATOR"),
						array("name" => "<svg><use xlink:href='{$rightsIcon}#rights'/></svg> Rechte",  "command" => "Sanctions", "namespace" => "Explorer", "params" => "{'id':'{$this->linkObjectId}'}", "type"=>"popup"),
						);
		$popupMenu->setItems($items);
		$popupMenu->setWidth("120px");
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

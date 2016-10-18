<?php
namespace PortletBookmarks\Commands;
class GetPopupMenuHeadline extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $object;
	private $x, $y, $height, $width;
	private $portletObjectId;
	private $user;

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
		$this->portletObjectId = $this->params["portletObjectId"];
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user()->get_name();
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->portletObjectId);
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$explorerUrl = \Explorer::getInstance()->getAssetUrl();
		//icons
		$trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";
		$sortIcon = $explorerUrl . "icons/menu/svg/sort.svg";
		$upIcon = $explorerUrl . "icons/menu/svg/up.svg";
		$downIcon = $explorerUrl . "icons/menu/svg/down.svg";
		$topIcon = $explorerUrl . "icons/menu/svg/top.svg";
		$bottomIcon = $explorerUrl . "icons/menu/svg/bottom.svg";
		$editIcon = $explorerUrl . "icons/menu/svg/edit.svg";

		$env = $this->object->get_environment();
		$inventory = $env->get_inventory();
		$id = intval($this->id);
		foreach ($inventory as $key => $element) {
			if ($element->get_id() == $id) {
				$index = $key;
			}
		}

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "<svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten",  "command" => "Edit", "namespace" => "PortletBookmarks", "params" => "{'portletId':'{$this->portletObjectId}','user':'{$this->user}'}", "type"=>"popup"),
		(count($inventory) > 1) ? array("name" => "<svg><use xlink:href='{$sortIcon}#sort'/></svg> Umsortieren", "direction" => "right", "menu" => array(
			($index != 0) ? array("name" => "<svg><use xlink:href='{$topIcon}#top'/></svg> Ganz nach oben",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'first'}") : "",
			($index != 0) ? array("name" => "<svg><use xlink:href='{$upIcon}#up'/></svg> Eins nach oben",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'up'}") : "",
			($index < count($inventory)-1) ? array("name" => "<svg><use xlink:href='{$downIcon}#down'/></svg> Eins nach unten",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'down'}") : "",
			($index < count($inventory)-1) ? array("name" => "<svg><use xlink:href='{$bottomIcon}#bottom'/></svg> Ganz nach unten",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'last'}") : "",
		)) : "",
		array("name" => "<svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen",  "command" => "Delete", "namespace" => "PortletBookmarks", "params" => "{'portletId':'{$this->portletObjectId}'}", "type"=>"popup"),
		);
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 125) . "px", round($this->y + $this->height + 4) . "px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

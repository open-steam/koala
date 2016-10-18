<?php
namespace PortletMsg\Commands;
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
		$copyIcon = $explorerUrl . "icons/menu/svg/copy.svg";
		$cutIcon = $explorerUrl . "icons/menu/svg/cut.svg";
		$referIcon = $explorerUrl . "icons/menu/svg/refer.svg";
		$trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";
		$sortIcon = $explorerUrl . "icons/menu/svg/sort.svg";
		$upIcon = $explorerUrl . "icons/menu/svg/up.svg";
		$downIcon = $explorerUrl . "icons/menu/svg/down.svg";
		$topIcon = $explorerUrl . "icons/menu/svg/top.svg";
		$bottomIcon = $explorerUrl . "icons/menu/svg/bottom.svg";
		$renameIcon = $explorerUrl . "icons/menu/svg/rename.svg";
		$editIcon = $explorerUrl . "icons/menu/svg/edit.svg";
		$rightsIcon = $explorerUrl . "icons/menu/svg/rights.svg";
		$newIcon = $explorerUrl . "icons/menu/svg/newElement.svg";

		$env = $this->object->get_environment();
		$inventory = $env->get_inventory();
		$portletId = intval($this->portletObjectId);
		foreach ($inventory as $key => $element) {
			if ($element->get_id() == $portletId) {
				$index = $key;
			}
		}

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "<svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten",  "command" => "Edit", "namespace" => "PortletMsg", "params" => "{'portletId':'{$this->portletObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "<svg><use xlink:href='{$newIcon}#newElement'/></svg> Meldung einfügen",  "command" => "CreateNewFormMsg", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}'}", "type"=>"popup"),
						array("name" => "<svg><use xlink:href='{$copyIcon}#copy'/></svg> Kopieren",  "command" => "CopyMsg", "namespace" => "PortletMsg", "params" => "{'id':'{$this->portletObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "<svg><use xlink:href='{$cutIcon}#cut'/></svg> Ausschneiden",  "command" => "PortletCut", "namespace" => "Portal", "params" => "{'id':'{$this->portletObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "<svg><use xlink:href='{$referIcon}#refer'/></svg> Referenz erstellen",  "command" => "PortletReference", "namespace" => "Portal", "params" => "{'id':'{$this->id}','user':'{$this->user}'}", "type"=>"nonModalUpdater"),
						array("name" => "<svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen",  "command" => "Delete", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}'}", "type"=>"popup"),
						(count($inventory) > 1) ? array("name" => "<svg><use xlink:href='{$sortIcon}#sort'/></svg> Umsortieren", "direction" => "right", "menu" => array(
							($index != 0) ? array("name" => "<svg><use xlink:href='{$topIcon}#top'/></svg> Ganz nach oben",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'first'}") : "",
							($index != 0) ? array("name" => "<svg><use xlink:href='{$upIcon}#up'/></svg> Eins nach oben",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'up'}") : "",
							($index < count($inventory)-1) ? array("name" => "<svg><use xlink:href='{$downIcon}#down'/></svg> Eins nach unten",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'down'}") : "",
							($index < count($inventory)-1) ? array("name" => "<svg><use xlink:href='{$bottomIcon}#bottom'/></svg> Ganz nach unten",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'last'}") : "",
						)) : "",
						array("name" => "SEPARATOR"),
						array("name" => "<svg><use xlink:href='{$rightsIcon}#rights'/></svg> Rechte",  "command" => "Sanctions", "namespace" => "Explorer", "params" => "{'id':'{$this->portletObjectId}'}", "type"=>"popup"),
						);
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

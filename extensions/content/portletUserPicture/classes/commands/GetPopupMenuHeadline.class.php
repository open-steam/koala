<?php
namespace PortletUserPicture\Commands;

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
		$copyIcon = $explorerUrl . "icons/menu/copy.png";
		$cutIcon = $explorerUrl . "icons/menu/cut.png";
		$referIcon = $explorerUrl . "icons/menu/refer.png";
		$trashIcon = $explorerUrl . "icons/menu/trash.png";
		$sortIcon = $explorerUrl . "icons/menu/sort.png";
		$upIcon = $explorerUrl . "icons/menu/up.png";
		$downIcon = $explorerUrl . "icons/menu/down.png";
		$topIcon = $explorerUrl . "icons/menu/top.png";
		$bottomIcon = $explorerUrl . "icons/menu/bottom.png";
		$editIcon = $explorerUrl . "icons/menu/edit.png";
		$rightsIcon = $explorerUrl . "icons/menu/rights.png";
		$blankIcon = $explorerUrl . "icons/menu/blank.png";

		$env = $this->object->get_environment();
		$inventory = $env->get_inventory();
		$id = intval($this->id);
		foreach ($inventory as $key => $element) {
			if ($element->get_id() == $id) {
				$index = $key;
			}
		}

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "<img src=\"{$editIcon}\">Bearbeiten",  "command" => "Edit", "namespace" => "PortletUserPicture", "params" => "{'portletId':'{$this->portletObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						(count($inventory) > 1) ? array("name" => "<img src=\"{$sortIcon}\">Umsortieren", "direction" => "right", "menu" => array(
							($index != 0) ? array("name" => "<img src=\"{$topIcon}\">Ganz nach oben",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'first'}") : "",
							($index != 0) ? array("name" => "<img src=\"{$upIcon}\">Eins nach oben",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'up'}") : "",
							($index < count($inventory)-1) ? array("name" => "<img src=\"{$downIcon}\">Eins nach unten",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'down'}") : "",
							($index < count($inventory)-1) ? array("name" => "<img src=\"{$bottomIcon}\">Ganz nach unten",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->portletObjectId}','order':'last'}") : "",
						)) : "",
						array("name" => "<img src=\"{$copyIcon}\">Kopieren",  "command" => "PortletCopy", "namespace" => "Portal", "params" => "{'id':'{$this->portletObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "<img src=\"{$cutIcon}\">Ausschneiden",  "command" => "PortletCut", "namespace" => "Portal", "params" => "{'id':'{$this->portletObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "<img src=\"{$referIcon}\">Referenz erstellen",  "command" => "PortletReference", "namespace" => "Portal", "params" => "{'id':'{$this->id}','user':'{$this->user}'}", "type"=>"nonModalUpdater"),
						array("name" => "<img src=\"{$trashIcon}\">Löschen",  "command" => "Delete", "namespace" => "PortletUserPicture", "params" => "{'portletId':'{$this->portletObjectId}'}", "type"=>"popup"),
						array("name" => "SEPARATOR"),
						array("name" => "<img src=\"{$rightsIcon}\">Rechte",  "command" => "Sanctions", "namespace" => "Explorer", "params" => "{'id':'{$this->portletObjectId}'}", "type"=>"popup"),
						);
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

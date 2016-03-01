<?php
namespace PortletAppointment\Commands;
class GetPopupMenu extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $object;
	private $x, $y, $height, $width;
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
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user()->get_name();
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		//icons
		$copyIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/copy.png";
		$cutIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/cut.png";
		$referIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/refer.png";
		$trashIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/trash.png";
		$sortIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/sort.png";
		$upIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/up.png";
		$downIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/down.png";
		$topIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/top.png";
		$bottomIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/bottom.png";
		$editIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/edit.png";
		$rightsIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights.png";
		$blankIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/blank.png";

		$env = $this->object->get_environment();
		$inventory = $env->get_inventory();
		$id = intval($this->id);
		foreach ($inventory as $key => $element) {
			if ($element->get_id() == $id) {
				$index = $key;
			}
		}

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "Bearbeiten <img src=\"{$editIcon}\">",  "command" => "Edit", "namespace" => "PortletAppointment", "params" => "{'portletId':'{$this->id}'}", "type"=>"popup"),
						array("name" => "Termin anlegen <img src=\"{$blankIcon}\">",  "command" => "CreateNewFormTerm", "namespace" => "PortletAppointment", "params" => "{'portletId':'{$this->id}'}", "type"=>"popup"),
					   	(count($inventory) > 1) ? array("name" => "Umsortieren <img src=\"{$sortIcon}\">", "direction" => "left", "menu" => array(
								($index != 0) ? array("name" => "Ganz nach oben <img src=\"{$topIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->id}','order':'first'}") : "",
								($index != 0) ? array("name" => "Eins nach oben <img src=\"{$upIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->id}','order':'up'}") : "",
								($index < count($inventory)-1) ? array("name" => "Eins nach unten <img src=\"{$downIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->id}','order':'down'}") : "",
								($index < count($inventory)-1) ? array("name" => "Ganz nach unten <img src=\"{$bottomIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->id}','order':'last'}") : "",
						)) : "",
						array("name" => "SEPARATOR"),
						array("name" => "Kopieren <img src=\"{$copyIcon}\">",  "command" => "PortletCopy", "namespace" => "Portal", "params" => "{'id':'{$this->id}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "Ausschneiden <img src=\"{$cutIcon}\">",  "command" => "PortletCut", "namespace" => "Portal", "params" => "{'id':'{$this->id}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "Referenz erstellen <img src=\"{$referIcon}\">",  "command" => "PortletReference", "namespace" => "Portal", "params" => "{'id':'{$this->id}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "Löschen <img src=\"{$trashIcon}\">",  "command" => "Delete", "namespace" => "PortletAppointment", "params" => "{'portletId':'{$this->id}'}", "type"=>"popup"),
					   	array("name" => "SEPARATOR"),
						array("name" => "Rechte <img src=\"{$rightsIcon}\">",  "command" => "Sanctions", "namespace" => "Explorer", "params" => "{'id':'{$this->id}'}", "type"=>"popup"),
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

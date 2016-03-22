<?php
namespace PortalColumn\Commands;
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
		$explorerUrl = \Explorer::getInstance()->getAssetUrl();
		//icons
		$createIcon = $explorerUrl . "icons/menu/newElement.png";
		$pasteIcon = $explorerUrl . "icons/menu/paste.png";
		$editIcon = $explorerUrl . "icons/menu/edit.png";

		$popupMenu =  new \Widgets\PopupMenu();
		$items = 	array(
						array("name" => "Komponente erstellen <img src=\"{$createIcon}\">", "command" => "NewPortlet", "namespace" => "PortalColumn", "params" => "{'portletId':'{$this->id}'}", "type"=>"popup"),
						array("name" => "Komponente einfügen <img src=\"{$pasteIcon}\">", "command" => "InsertPortlet", "namespace" => "PortalColumn", "params" => "{'portletId':'{$this->id}'}", "type"=>"popup"),
						array("name" => "Breite bearbeiten <img src=\"{$editIcon}\">", "command" => "Edit", "namespace" => "PortalColumn", "params" => "{'portletId':'{$this->id}'}", "type"=>"popup")
					);
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		$popupMenu->setWidth("170px");
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

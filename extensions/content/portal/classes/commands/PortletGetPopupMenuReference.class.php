<?php
namespace Portal\Commands;
class PortletGetPopupMenuReference extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $object;
	private $x, $y, $height, $width;
	private $sourceObjectId;
	private $linkObjectId;
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

		$this->sourceObjectId = $this->params["sourceObjectId"];
		$this->linkObjectId = $this->params["linkObjectId"];

		$portletObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $this->sourceObjectId );
		$portal = $portletObject->get_environment()->get_environment();

		$this->sourcePortalObjectId = $portal->get_id();
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user()->get_name();
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		//icons
		$copyIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/copy.png";
		$cutIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/cut.png";
		$referIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/refer.png";
		$trashIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/trash.png";
		$upIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/up.png";
		$downIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/down.png";
		$topIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/top.png";
		$bottomIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/bottom.png";
		$rightsIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights.png";
		$blankIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/blank.png";

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(
						array("name" => "Umsortieren<img src=\"{$blankIcon}\">", "direction" => "left", "menu" => array(
							array("name" => "Eins nach oben<img src=\"{$upIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'up'}", "type"=>"popup"),
							array("name" => "Eins nach unten<img src=\"{$downIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'down'}", "type"=>"popup"),
							array("name" => "Ganz nach oben<img src=\"{$topIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'first'}", "type"=>"popup"),
							array("name" => "Ganz nach unten<img src=\"{$bottomIcon}\">",  "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->linkObjectId}','order':'last'}", "type"=>"popup"),
						)),
						array("name" => "SEPARATOR"),
						array("name" => "Kopieren<img src=\"{$copyIcon}\">",  "command" => "PortletCopy", "namespace" => "Portal", "params" => "{'id':'{$this->linkObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "Ausschneiden<img src=\"{$cutIcon}\">",  "command" => "PortletCut", "namespace" => "Portal", "params" => "{'id':'{$this->linkObjectId}','user':'{$this->user}'}", "type"=>"popup"),
						array("name" => "Referenz erstellen<img src=\"{$referIcon}\">",  "command" => "PortletReference", "namespace" => "Portal", "params" => "{'id':'{$this->sourceObjectId}','user':'{$this->user}'}", "type"=>"inform"),
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

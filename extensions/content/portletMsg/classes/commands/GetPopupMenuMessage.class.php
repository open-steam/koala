<?php
namespace PortletMsg\Commands;
class GetPopupMenuMessage extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $object;
	private $x, $y, $height, $width;
	
	private $portletObjectId;
	private $messageObjectId;
	private $id;
	private $user;
	
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		
		$this->x = $this->params["x"];
		$this->y = $this->params["y"];
		$this->height = $this->params["height"];
		$this->width = $this->params["width"];
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user()->get_name();
		
		$this->messageObjectId = $this->params["messageObjectId"];
		$this->portletObjectId = $this->params["portletObjectId"];
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		//icons
		$copyIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/copy.png";
		$cutIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/cut.png";
		$referIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/refer.png";
		$deleteIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/delete.png";
		$hideIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/hide.png";
		$bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
		$schoolBookmarkIcon = \School::getInstance()->getAssetUrl() . "icons/schoolbookmark.png";
		$upIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/up.png";
		$downIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/down.png";
		$topIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/top.png";
		$bottomIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/bottom.png";
		$renameIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/rename.png";
		$editIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/edit.png";
		$propertiesIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties.png";
		$rightsIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights.png";
		$blankIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/blank.png";
                $addImage = \PortletMsg::getInstance()->getAssetUrl() . "icons/add_image.png";
		
		
		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "Bearbeiten <img src=\"{$editIcon}\">",  "command" => "EditMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"),
						array("name" => "Bild bearbeiten <img src=\"{$addImage}\">",  "command" => "EditMessageImage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"), 
						array("name" => "Umsortieren <img src=\"{$blankIcon}\">", "direction" => "left", "menu" => array(
							array("name" => "Eins nach oben <img src=\"{$upIcon}\">",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'up'}", "type"=>"popup"),
							array("name" => "Eins nach unten <img src=\"{$downIcon}\">",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'down'}", "type"=>"popup"),
							array("name" => "Ganz nach oben <img src=\"{$topIcon}\">",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'first'}", "type"=>"popup"),
							array("name" => "Ganz nach unten <img src=\"{$bottomIcon}\">",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'last'}", "type"=>"popup")
						)),
						array("name" => "Löschen <img src=\"{$deleteIcon}\">",  "command" => "DeleteMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"),
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
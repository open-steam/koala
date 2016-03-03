<?php
namespace PortletMsg\Commands;
class GetPopupMenuMessage extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $x, $y, $height, $width;
	private $portletObjectId;
	private $messageObjectId;
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
		$explorerUrl = \Explorer::getInstance()->getAssetUrl();
		//icons
		$copyIcon = $explorerUrl . "icons/menu/copy.png";
		$cutIcon = $explorerUrl . "icons/menu/cut.png";
		$referIcon = $explorerUrl . "icons/menu/refer.png";
		$deleteIcon = $explorerUrl . "icons/menu/delete.png";
		$hideIcon = $explorerUrl . "icons/menu/hide.png";
		$bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
		$sortIcon = $explorerUrl . "icons/menu/sort.png";
		$upIcon = $explorerUrl . "icons/menu/up.png";
		$downIcon = $explorerUrl . "icons/menu/down.png";
		$topIcon = $explorerUrl . "icons/menu/top.png";
		$bottomIcon = $explorerUrl . "icons/menu/bottom.png";
		$renameIcon = $explorerUrl . "icons/menu/rename.png";
		$editIcon = $explorerUrl . "icons/menu/edit.png";
		$editHtmlIcon = $explorerUrl . "icons/menu/edit_html.png";
		$propertiesIcon = $explorerUrl . "icons/menu/properties.png";
		$rightsIcon = $explorerUrl . "icons/menu/rights.png";
		$blankIcon = $explorerUrl . "icons/menu/blank.png";
		$addImage = \PortletMsg::getInstance()->getAssetUrl() . "icons/add_image.png";

		$messageObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->messageObjectId);
		$portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->portletObjectId);

		$content = $portletObject->get_attribute("bid:portlet:content");
		$messageId = intval($this->messageObjectId);
		foreach ($content as $key => $id) {
			if ($id == $messageId) {
				$index = $key;
			}
		}

		$imageId = $messageObject->get_attribute("bid:portlet:msg:picture_id");
		if ($imageId !== 0){
			$pictureLabel = "Bild bearbeiten";
		} else{
			$pictureLabel = "Bild hinzufügen";
		}

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "Bearbeiten <img src=\"{$editIcon}\">",  "command" => "EditMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"),
										array("name" => "Quelltext bearbeiten <img src=\"{$editHtmlIcon}\">",  "command" => "EditMessageCode", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"),
										array("name" => "{$pictureLabel} <img src=\"{$addImage}\">",  "command" => "EditMessageImage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"),
										(count($content) > 1) ? array("name" => "Umsortieren <img src=\"{$sortIcon}\">", "direction" => "left", "menu" => array(
											($index != 0) ? array("name" => "Ganz nach oben <img src=\"{$topIcon}\">",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'first'}") : "",
											($index != 0) ? array("name" => "Eins nach oben <img src=\"{$upIcon}\">",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'up'}") : "",
											($index < count($content)-1) ? array("name" => "Eins nach unten <img src=\"{$downIcon}\">",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'down'}") : "",
											($index < count($content)-1) ? array("name" => "Ganz nach unten <img src=\"{$bottomIcon}\">",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'last'}") : ""
										)
									) : "",
									array("name" => "Löschen <img src=\"{$deleteIcon}\">",  "command" => "DeleteMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"),
								);
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		$popupMenu->setWidth("180px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

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
		$deleteIcon = $explorerUrl . "icons/menu/svg/trash.svg";
		$sortIcon = $explorerUrl . "icons/menu/svg/sort.svg";
		$upIcon = $explorerUrl . "icons/menu/svg/up.svg";
		$downIcon = $explorerUrl . "icons/menu/svg/down.svg";
		$topIcon = $explorerUrl . "icons/menu/svg/top.svg";
		$bottomIcon = $explorerUrl . "icons/menu/svg/bottom.svg";
		$editIcon = $explorerUrl . "icons/menu/svg/edit.svg";
		$editHtmlIcon = $explorerUrl . "icons/menu/svg/edit_html.svg";
		$addImage = $explorerUrl . "icons/mimetype/svg/image.svg";

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
		$items = array(	array("name" => "<svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten",  "command" => "EditMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"),
										array("name" => "<svg><use xlink:href='{$editHtmlIcon}#edit_html'/></svg> Quelltext bearbeiten",  "command" => "EditMessageCode", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"),
										array("name" => "<svg><use xlink:href='{$addImage}#image'/></svg> {$pictureLabel}",  "command" => "EditMessageImage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"),
										array("name" => "<svg><use xlink:href='{$deleteIcon}#trash'/></svg> Löschen",  "command" => "DeleteMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}'}", "type"=>"popup"),
										(count($content) > 1) ? array("name" => "<svg><use xlink:href='{$sortIcon}#sort'/></svg> Umsortieren", "direction" => "right", "menu" => array(
											($index != 0) ? array("name" => "<svg><use xlink:href='{$topIcon}#top'/></svg> Ganz nach oben",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'first'}") : "",
											($index != 0) ? array("name" => "<svg><use xlink:href='{$upIcon}#up'/></svg> Eins nach oben",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'up'}") : "",
											($index < count($content)-1) ? array("name" => "<svg><use xlink:href='{$downIcon}#down'/></svg> Eins nach unten",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'down'}") : "",
											($index < count($content)-1) ? array("name" => "<svg><use xlink:href='{$bottomIcon}#bottom'/></svg> Ganz nach unten",  "command" => "OrderMessage", "namespace" => "PortletMsg", "params" => "{'portletObjectId':'{$this->portletObjectId}','messageObjectId':'{$this->messageObjectId}','order':'last'}") : ""
										)) : "",
								);
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

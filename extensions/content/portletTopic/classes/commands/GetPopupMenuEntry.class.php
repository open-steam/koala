<?php
namespace PortletTopic\Commands;
class GetPopupMenuEntry extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $object;
	private $x, $y, $height, $width;
	private $categoryIndex = 0;
	private $entryIndex = 0;
	private $categories = 0;
	private $entries = 0;

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
		$this->categoryIndex = $this->params["category"];
		$this->entryIndex = $this->params["entry"];
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$this->categories = $this->object->get_attribute("bid:portlet:content");
		$this->entries = $this->categories[$this->categoryIndex]["topics"];
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

		$inventory = $this->countPortletEntries();

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "<svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten",  "command" => "EditTopicEntry", "namespace" => "PortletTopic", "params" => "{	'portletId':'{$this->id}','entryIndex':'{$this->entryIndex}','categoryIndex':'{$this->categoryIndex}'}", "type"=>"popup"),
						array("name" => "<svg><use xlink:href='{$deleteIcon}#trash'/></svg> Löschen",  "command" => "DeleteEntry", "namespace" => "PortletTopic", "params" => "{'portletId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','entryIndex':'{$this->entryIndex}'}","type"=>"popup"),
						($inventory >= 2) ? array("name" => "<svg><use xlink:href='{$sortIcon}#sort'/></svg> Umsortieren", "direction" => "right", "menu" => array(
									($this->categoryIndex != 0 || $this->entryIndex != 0) ? array("name" => "<svg><use xlink:href='{$topIcon}#top'/></svg> Ganz nach oben",  "command" => "OrderEntry", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','entryIndex':'{$this->entryIndex}','order':'first'}") : "",
									($this->categoryIndex != 0 || $this->entryIndex != 0) ? array("name" => "<svg><use xlink:href='{$upIcon}#up'/></svg> Eins nach oben",  "command" => "OrderEntry", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','entryIndex':'{$this->entryIndex}','order':'up'}") : "",
									($this->entryIndex < count($this->entries)-1 || $this->categoryIndex < count($this->categories)-1) ? array("name" => "<svg><use xlink:href='{$downIcon}#down'/></svg> Eins nach unten",  "command" => "OrderEntry", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','entryIndex':'{$this->entryIndex}','order':'down'}") : "",
									($this->entryIndex < count($this->entries)-1 || $this->categoryIndex < count($this->categories)-1) ? array("name" => "<svg><use xlink:href='{$bottomIcon}#bottom'/></svg> Ganz nach unten",  "command" => "OrderEntry", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','entryIndex':'{$this->entryIndex}','order':'last'}") : "",
								)) : "");
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 105) . "px", round($this->y + $this->height + 4) . "px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}

	public function countPortletEntries() {
		$entries = 0;
		foreach($this->categories as $entryArray) {
			$entries = $entries + count($entryArray["topics"]);
		}
		return $entries;
	}
}
?>

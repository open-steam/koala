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
		//icons
		$copyIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/copy.png";
		$cutIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/cut.png";
		$referIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/refer.png";
		$deleteIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/delete.png";
		$hideIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/hide.png";
		$bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
		$upIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/up.png";
		$downIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/down.png";
		$topIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/top.png";
		$bottomIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/bottom.png";
		$renameIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/rename.png";
		$editIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/edit.png";
		$propertiesIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties.png";
		$rightsIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights.png";
		$blankIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/blank.png";

		$inventory = $this->countPortletEntries();

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "Bearbeiten <img src=\"{$editIcon}\">",  "command" => "EditTopicEntry", "namespace" => "PortletTopic", "params" => "{	'portletId':'{$this->id}','entryIndex':'{$this->entryIndex}','categoryIndex':'{$this->categoryIndex}'}", "type"=>"popup"),
				($inventory >= 2) ? array("name" => "Umsortieren <img src=\"{$blankIcon}\">", "direction" => "left", "menu" => array(
							($this->categoryIndex != 0 || $this->entryIndex != 0) ? array("name" => "Ganz nach oben <img src=\"{$topIcon}\">",  "command" => "OrderEntry", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','entryIndex':'{$this->entryIndex}','order':'first'}") : "",
							($this->categoryIndex != 0 || $this->entryIndex != 0) ? array("name" => "Eins nach oben <img src=\"{$upIcon}\">",  "command" => "OrderEntry", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','entryIndex':'{$this->entryIndex}','order':'up'}") : "",
							($this->entryIndex < count($this->entries)-1 || $this->categoryIndex < count($this->categories)-1) ? array("name" => "Eins nach unten <img src=\"{$downIcon}\">",  "command" => "OrderEntry", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','entryIndex':'{$this->entryIndex}','order':'down'}") : "",
							($this->entryIndex < count($this->entries)-1 || $this->categoryIndex < count($this->categories)-1) ? array("name" => "Ganz nach unten <img src=\"{$bottomIcon}\">",  "command" => "OrderEntry", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','entryIndex':'{$this->entryIndex}','order':'last'}") : "",
						)) : "",
						array("name" => "Löschen <img src=\"{$deleteIcon}\">",  "command" => "DeleteEntry", "namespace" => "PortletTopic", "params" => "{'portletId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','entryIndex':'{$this->entryIndex}'}","type"=>"popup"));
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		$popupMenu->setWidth("150px");

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

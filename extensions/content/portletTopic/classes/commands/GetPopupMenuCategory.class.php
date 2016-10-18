<?php
namespace PortletTopic\Commands;
class GetPopupMenuCategory extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $object;
	private $x, $y, $height, $width;
	private $categoryIndex;

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
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
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

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "<img src=\"{$editIcon}\">Bearbeiten",  "command" => "EditTopicCategory", "namespace" => "PortletTopic", "params" => "{'portletId':'{$this->id}','categoryIndex':'{$this->categoryIndex}'}", "type"=>"popup"),
						array("name" => "<img src=\"{$blankIcon}\">Eintrag einfügen",  "command" => "CreateEntryForm", "namespace" => "PortletTopic", "params" => "{'portletId':'{$this->id}','categoryIndex':'{$this->categoryIndex}'}", "type"=>"popup"),
						array("name" => "<img src=\"{$blankIcon}\">Umsortieren", "direction" => "right", "menu" => array(
							array("name" => "<img src=\"{$upIcon}\">Eins nach oben",  "command" => "OrderCategory", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','order':'up'}", "type"=>"popup"),
							array("name" => "<img src=\"{$downIcon}\">Eins nach unten",  "command" => "OrderCategory", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','order':'down'}", "type"=>"popup"),
							array("name" => "<img src=\"{$topIcon}\">Ganz nach oben",  "command" => "OrderCategory", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','order':'first'}", "type"=>"popup"),
							array("name" => "<img src=\"{$bottomIcon}\">Ganz nach unten",  "command" => "OrderCategory", "namespace" => "PortletTopic", "params" => "{'portletObjectId':'{$this->id}','categoryIndex':'{$this->categoryIndex}','order':'last'}", "type"=>"popup")
						)),
						array("name" => "<img src=\"{$deleteIcon}\">Löschen",  "command" => "DeleteCategory", "namespace" => "PortletTopic", "params" => "{'portletId':'{$this->id}','categoryIndex':'{$this->categoryIndex}'}", "type"=>"popup")
						);
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

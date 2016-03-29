<?php
namespace PortletAppointment\Commands;
class GetPopupMenuTerm extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $object;
	private $x, $y, $height, $width;
	private $termIndex;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->x = $this->params["x"];
		$this->y = $this->params["y"];
		$this->termIndex = $this->params["termIndex"];
		$this->height = $this->params["height"];
		$this->width = $this->params["width"];
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
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
		$upIcon = $explorerUrl . "icons/menu/up.png";
		$downIcon = $explorerUrl . "icons/menu/down.png";
		$topIcon = $explorerUrl . "icons/menu/top.png";
		$bottomIcon = $explorerUrl . "icons/menu/bottom.png";
		$renameIcon = $explorerUrl . "icons/menu/rename.png";
		$editIcon = $explorerUrl . "icons/menu/edit.png";
		$propertiesIcon = $explorerUrl . "icons/menu/properties.png";
		$rightsIcon = $explorerUrl . "icons/menu/rights.png";
		$blankIcon = $explorerUrl . "icons/menu/blank.png";

		$popupMenu =  new \Widgets\PopupMenu();
		$items = array(	array("name" => "Bearbeiten <img src=\"{$editIcon}\">",  "command" => "EditTerm", "namespace" => "PortletAppointment", "params" => "{'portletId':'{$this->id}','termIndex':'{$this->termIndex}'}", "type"=>"popup"),
						array("name" => "Löschen <img src=\"{$deleteIcon}\">",  "command" => "DeleteTerm", "namespace" => "PortletAppointment", "params" => "{'portletId':'{$this->id}','termIndex':'{$this->termIndex}'}", "type"=>"popup"));
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		$popupMenu->setWidth("150px");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($popupMenu);
		return $ajaxResponseObject;
	}
}
?>

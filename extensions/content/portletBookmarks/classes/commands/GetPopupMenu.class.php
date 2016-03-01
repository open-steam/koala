<?php

namespace PortletBookmarks\Commands;

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
        $copyIcon = $explorerUrl . "icons/menu/copy.png";
        $cutIcon = $explorerUrl . "icons/menu/cut.png";
        $referIcon = $explorerUrl . "icons/menu/refer.png";
        $trashIcon = $explorerUrl . "icons/menu/trash.png";
        $hideIcon = $explorerUrl . "icons/menu/hide.png";
        $bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
        $sortIcon = $explorerUrl . "icons/menu/sort.png";
        $upIcon = $explorerUrl . "icons/menu/up.png";
        $downIcon = $explorerUrl . "icons/menu/down.png";
        $topIcon = $explorerUrl . "icons/menu/top.png";
        $bottomIcon = $explorerUrl . "icons/menu/bottom.png";
        $renameIcon = $explorerUrl . "icons/menu/rename.png";
        $editIcon = $explorerUrl . "icons/menu/edit.png";
        $propertiesIcon = $explorerUrl . "icons/menu/properties.png";
        $rightsIcon = $explorerUrl . "icons/menu/rights.png";
        $blankIcon = $explorerUrl . "icons/menu/blank.png";

        $env = $this->object->get_environment();
    		$inventory = $env->get_inventory();
    		$id = intval($this->id);
    		foreach ($inventory as $key => $element) {
    			if ($element->get_id() == $id) {
    				$index = $key;
    			}
    		}

        $popupMenu = new \Widgets\PopupMenu();
        $items = array(
            array("name" => "Bearbeiten <img src=\"{$editIcon}\">", "command" => "Edit", "namespace" => "PortletBookmarks", "params" => "{'portletId':'{$this->id}'}", "type" => "popup"),
            (count($inventory) > 1) ? array("name" => "Umsortieren <img src=\"{$sortIcon}\">", "direction" => "left", "menu" => array(
                    ($index != 0) ? array("name" => "Ganz nach oben <img src=\"{$topIcon}\">", "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->id}','order':'first'}") : "",
                    ($index != 0) ? array("name" => "Eins nach oben <img src=\"{$upIcon}\">", "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->id}','order':'up'}") : "",
                    ($index < count($inventory)-1) ? array("name" => "Eins nach unten <img src=\"{$downIcon}\">", "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->id}','order':'down'}") : "",
                    ($index < count($inventory)-1) ? array("name" => "Ganz nach unten <img src=\"{$bottomIcon}\">", "command" => "Order", "namespace" => "Portal", "params" => "{'portletId':'{$this->id}','order':'last'}") : "",
            )) : "",
            array("name" => "SEPARATOR"),
            array("name" => "Löschen <img src=\"{$trashIcon}\">", "command" => "Delete", "namespace" => "PortletBookmarks", "params" => "{'portletId':'{$this->id}'}", "type" => "popup"),
            array("name" => "SEPARATOR")
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

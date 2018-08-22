<?php

namespace PortalColumn\Commands;

class GetPopupMenu extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $object;
    private $x, $y, $height, $width;

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
        $this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $explorerUrl = \Explorer::getInstance()->getAssetUrl();
        //icons
        $createIcon = $explorerUrl . "icons/menu/svg/newElement.svg";
        $pasteIcon = $explorerUrl . "icons/menu/svg/paste.svg";
        $editIcon = $explorerUrl . "icons/menu/svg/edit.svg";


        $items = array(
            array("name" => "<svg><use xlink:href='{$createIcon}#newElement'/></svg> Komponente erstellen", "command" => "NewPortlet", "namespace" => "PortalColumn", "params" => "{'portletId':'{$this->id}'}", "type" => "popup"),
            array("name" => "<svg><use xlink:href='{$pasteIcon}#paste'/></svg> Komponente einfügen", "command" => "InsertPortlet", "namespace" => "PortalColumn", "params" => "{'portletId':'{$this->id}'}", "type" => "popup"),
            array("name" => "<svg><use xlink:href='{$editIcon}#edit'/></svg> Breite bearbeiten", "command" => "Edit", "namespace" => "PortalColumn", "params" => "{'portletId':'{$this->id}'}", "type" => "popup")
        );
        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>

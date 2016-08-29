<?php

namespace Explorer\Commands;

class GetPopupMenuSearch extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $selection;
    private $x, $y, $height, $width;
    private $logged_in;

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

        $portal = \lms_portal::get_instance();
        $lms_user = $portal->get_user();
        $this->logged_in = $lms_user->is_logged_in();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $copyIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/copy.svg";
        $items = array();
        $items[] = array("name" => "<svg><use xlink:href='{$copyIcon}#copy'/></svg> Filtereinstellungen kopieren", "command" => "CopyFilterSettings", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'filter': encodeURI($('#searchfield').val()) }", "type" => "popup");
        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);
        $popupMenu->setPosition(round($this->x + $this->width )  . "px", round($this->y + $this->height -20) . "px");
        $popupMenu->setWidth("195px");
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>

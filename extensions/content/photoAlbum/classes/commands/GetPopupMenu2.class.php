<?php

namespace PhotoAlbum\Commands;

class GetPopupMenu2 extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $selection;
    private $x, $y, $height, $width;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->selection = json_decode($this->params["selection"]);
        $this->x = $this->params["x"];
        $this->y = $this->params["y"];
        $this->height = $this->params["height"];
        $this->width = $this->params["width"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $count = count($this->selection);
        if (!in_array($this->id, $this->selection) || (in_array($this->id, $this->selection) && $count == 1)) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            $env = $object->get_environment();

            $inventory = $env->get_inventory();
            foreach ($inventory as $key => $element) {
                if ($element->get_id() == $this->id) {
                    $index = $key;
                }
            }

            $popupMenu = new \Widgets\PopupMenu();

            if ($object instanceof \steam_trashbin) {
                $items = array(array("name" => "Papierkorb leeren", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"));
            } else if ($env instanceof \steam_trashbin) {
                $items = array(array("name" => "Wiederherstellen", "command" => "Restore", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'env':document.getElementById('environment').value}"));
            } else {
                $copyIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/copy.png";
                $cutIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/cut.png";
                $referIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/refer.png";
                $trashIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/trash.png";
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
                $items = array(
                    array("name" => "Kopieren<img src=\"{$copyIcon}\">", "command" => "Copy", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"),
                    array("name" => "Ausschneiden<img src=\"{$cutIcon}\">", "command" => "Cut", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"),
                    array("name" => "Referenz erstellen<img src=\"{$referIcon}\">", "command" => "Reference", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"),
                    array("name" => "Löschen<img src=\"{$trashIcon}\">", "command" => "Delete", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"),
                    (count($inventory) >=2) ? array("name" => "Umsortieren<img src=\"{$blankIcon}\">", "direction" => "left", "menu" => array(
                            ($index != 0) ? array("name" => "Ganz nach oben<img src=\"{$topIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'top'}") : "",
                            ($index != 0) ? array("name" => "Eins nach oben<img src=\"{$upIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'up'}") : "",
                            ($index < count($inventory) - 1) ? array("name" => "Eins nach unten<img src=\"{$downIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'down'}") : "",
                            ($index < count($inventory) - 1) ? array("name" => "Ganz nach unten<img src=\"{$bottomIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'bottom'}") : ""
                    )) : "",
                    array("name" => "SEPARATOR"),
                    array("raw" => "<a href=\"#\" onclick=\"event.stopPropagation(); removeAllDirectEditors();if (!jQuery('#{$this->id}_1').hasClass('directEditor')) { jQuery('#{$this->id}_1').addClass('directEditor').html(''); var obj = new Object; obj.id = '{$this->id}'; sendRequest('GetDirectEditor', obj, '{$this->id}_1', 'updater',null,null,'explorer'); } jQuery('.popupmenuwapper').parent().html('');jQuery('.open').removeClass('open'); return false;\">Umbenennen<img src=\"{$renameIcon}\"></a>"),
                    (($object instanceof \steam_container) && ($object->get_attribute("bid:presentation") === "index")) ? array("name" => "Listenansicht<img src=\"{$blankIcon}\">", "link" => PATH_URL . "Explorer/Index/" . $this->id . "/?view=list") : "",
                    (($object instanceof \steam_document) && (strstr($object->get_attribute(DOC_MIME_TYPE), "text"))) ? array("name" => "Bearbeiten<img src=\"{$editIcon}\">", "link" => PATH_URL . "Explorer/EditDocument/" . $this->id . "/") : "",
                    array("name" => "Eigenschaften...<img src=\"{$propertiesIcon}\">", "command" => "Properties", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"),
                    array("name" => "Rechte...<img src=\"{$rightsIcon}\">", "command" => "Sanctions", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"));
            }
            $popupMenu->setItems($items);
            $popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
            $popupMenu->setWidth("140px");
        } else {
            $copyIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/copy.png";
            $cutIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/cut.png";
            $referIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/refer.png";
            $trashIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/trash.png";
            $hideIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/hide.png";
            $blankIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/blank.png";
            $popupMenu = new \Widgets\PopupMenu();
            $items = array(
                array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Copy', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Kopiere Objekte ...', 0,  getSelectionAsArray().length); return false;\">{$count} Objekte kopieren<img src=\"{$copyIcon}\"></a>"),
                array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Cut', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Schneide Objekte aus ...', 0,  getSelectionAsArray().length); return false;\">{$count} Objekte ausschneiden<img src=\"{$cutIcon}\"></a>"),
                array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Reference', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Referenziere Objekte ...', 0,  getSelectionAsArray().length); return false;\">{$count} Objektreferenzen erstellen<img src=\"{$referIcon}\"></a>"),
                array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Delete', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Lösche Objekte ...', 0,  getSelectionAsArray().length); return false;\">{$count} Objekte löschen<img src=\"{$trashIcon}\"></a>"),

            );
            $popupMenu->setItems($items);
            $popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
            $popupMenu->setWidth("200px");
        }

        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>

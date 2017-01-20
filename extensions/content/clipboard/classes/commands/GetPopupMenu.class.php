<?php

namespace Clipboard\Commands;

class GetPopupMenu extends \AbstractCommand implements \IAjaxCommand {

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
        $explorerUrl = \Explorer::getInstance()->getAssetUrl();
        if (!in_array($this->id, $this->selection) || (in_array($this->id, $this->selection) && $count == 1)) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            $env = $object->get_environment();

            $inventory = $env->get_inventory();
            foreach ($inventory as $key => $element) {
                if ($element->get_id() == $this->id) {
                    $index = $key;
                }
            }


            if ($object instanceof \steam_trashbin) {
                $items = array(array("name" => "Papierkorb leeren", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"));
            } else if ($env instanceof \steam_trashbin) {
                $restoreIcon = $explorerUrl . "icons/menu/svg/restore.svg";
                $items = array(
                    array("name" => "<svg><use xlink:href='{$restoreIcon}#restore'/></svg> Wiederherstellen", "command" => "Restore", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'env':document.getElementById('environment').value}", "type" => "nonModalUpdater"));
            } else {
                $copyIcon = $explorerUrl . "icons/menu/svg/copy.svg";
                $cutIcon = $explorerUrl . "icons/menu/svg/cut.svg";
                $referIcon = $explorerUrl . "icons/menu/svg/refer.svg";
                $trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";
                $bookmarkIcon = $explorerUrl . "icons/menu/svg/bookmark.svg";
                $brushIcon = $explorerUrl . "icons/menu/svg/brush.svg";
                $sortIcon = $explorerUrl . "icons/menu/svg/sort.svg";
                $upIcon = $explorerUrl . "icons/menu/svg/up.svg";
                $downIcon = $explorerUrl . "icons/menu/svg/down.svg";
                $topIcon = $explorerUrl . "icons/menu/svg/top.svg";
                $bottomIcon = $explorerUrl . "icons/menu/svg/bottom.svg";
                $renameIcon = $explorerUrl . "icons/menu/svg/rename.svg";
                $editIcon = $explorerUrl . "icons/menu/svg/edit.svg";
                $propertiesIcon = $explorerUrl . "icons/menu/svg/properties.svg";
                $rightsIcon = $explorerUrl . "icons/menu/svg/rights.svg";
                $folderIcon = $folderUrl . "icons/mimetype/svg/folder.svg";

                $items = array(
                    array("name" => "<svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen", "command" => "Delete", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "nonModalUpdater"),
                    (!\Bookmarks\Model\Bookmark::isBookmark($this->id)) ? array("name" => "<svg><use xlink:href='{$bookmarkIcon}#bookmark'/></svg> Lesezeichen anlegen", "command" => "AddBookmark", "namespace" => "bookmarks", "elementId" => "{$this->id}_BookmarkMarkerWrapper", "params" => "{'id':'{$this->id}'}", "type" => "inform") : "",
                    array("name" => "<svg><use xlink:href='{$brushIcon}#brush'/></svg> Einfärben", "direction" => "right", "menu" => array(
                        array("raw" => "<a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'transparent'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='greyColor'><use xlink:href='{$explorerUrl}icons/menu/svg/transparent.svg#transparent'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'red'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='redColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'orange'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='orangeColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'yellow'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='yellowColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'green'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='greenColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'blue'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='blueColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'purple'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='purpleColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'grey'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='greyColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>"),
                        )
                    ),
                    (count($inventory) >= 2) ? array("name" => "<svg><use xlink:href='{$sortIcon}#sort'/></svg> Umsortieren", "direction" => "right", "menu" => array(
                            ($index > $firstElement) ? array("name" => "<svg><use xlink:href='{$topIcon}#top'/></svg> Ganz nach oben", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'top'}", "type" => "nonModalUpdater") : "",
                            ($index > $firstElement) ? array("name" => "<svg><use xlink:href='{$upIcon}#up'/></svg> Eins nach oben", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'up'}", "type" => "nonModalUpdater") : "",
                            ($index < count($inventory) - 1 - $counter) ? array("name" => "<svg><use xlink:href='{$downIcon}#down'/></svg> Eins nach unten", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'down'}", "type" => "nonModalUpdater") : "",
                            ($index < count($inventory) - 1 - $counter) ? array("name" => "<svg><use xlink:href='{$bottomIcon}#bottom'/></svg> Ganz nach unten", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'bottom'}", "type" => "nonModalUpdater") : ""
                        )) : "",
                    array("name" => "SEPARATOR"),
                    array("raw" => "<a href=\"#\" onclick=\"event.stopPropagation(); removeAllDirectEditors();if (!jQuery('#{$this->id}_1').hasClass('directEditor')) { jQuery('#{$this->id}_1').addClass('directEditor').html(''); var obj = new Object; obj.id = '{$this->id}'; sendRequest('GetDirectEditor', obj, '{$this->id}_1', 'nonModalUpdater',null,null,'explorer'); } jQuery('.popupmenuwrapper').remove();jQuery('.open').removeClass('open'); jQuery('#footer_wrapper').css('padding-top', '0px'); return false;\"><svg><use xlink:href='{$renameIcon}#rename'/></svg> Umbenennen</a>"),
                    (($object instanceof \steam_container) && ($object->get_attribute("bid:presentation") === "index")) ? array("name" => "<svg><use xlink:href='{$folderIcon}#folder'/></svg> Ordnerinhalt anzeigen", "link" => PATH_URL . "Explorer/Index/" . $this->id . "/?view=list") : "",
                    (($object instanceof \steam_document) && (strstr($object->get_attribute(DOC_MIME_TYPE), "text"))) ? array("name" => "<svg><use xlink:href='{$editIcon}#edit'/></svg> Bearbeiten", "link" => PATH_URL . "Explorer/EditDocument/" . $this->id . "/") : "",
                    array("name" => "<svg><use xlink:href='{$propertiesIcon}#properties'/></svg> Eigenschaften", "command" => "Properties", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"),
                    array("name" => "<svg><use xlink:href='{$rightsIcon}#rights'/></svg> Rechte", "command" => "Sanctions", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"));
            }
        } else {
            $trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";
            $brushIcon = $explorerUrl . "icons/menu/svg/brush.svg";

            $viewAttribute = \lms_steam::get_current_user()->get_attribute("EXPLORER_VIEW");
            if ($viewAttribute && $viewAttribute == "gallery") {
                $paramsArrayFunction = "getGalleryParamsArray";
                $ElementIdFunction = "getGalleryElementIdArray";
                $SelectionFunction = "getGallerySelectionAsArray().length";
            } else {
                $paramsArrayFunction = "getParamsArray";
                $ElementIdFunction = "getElementIdArray";
                $SelectionFunction = "getSelectionAsArray().length";
            }

            $items = array(
                array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Delete', $paramsArrayFunction({}), $ElementIdFunction(''), 'updater', null, null, 'explorer', 'Lösche Objekte ...', 0, $SelectionFunction); return false;\"><svg><use xlink:href='{$trashIcon}#trash'/></svg> {$count} Objekte löschen</a>"),
                array("name" => "<svg><use xlink:href='{$brushIcon}#brush'/></svg> {$count} Objekte einfärben", "direction" => "right", "menu" => array(
                    array("raw" => " <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'transparent'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='greyColor'><use xlink:href='{$explorerUrl}icons/menu/svg/transparent.svg#transparent'/></svg></a>
			<a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'red'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='redColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
			<a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'orange'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='orangeColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
			<a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'yellow'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='yellowColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
			<a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'green'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='greenColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
			<a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'blue'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='blueColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
			<a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'purple'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='purpleColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
			<a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'grey'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='greyColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>"),
                    )
                ),
            );
        }
        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>

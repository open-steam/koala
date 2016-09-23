<?php
namespace Explorer\Commands;
class GetPopupMenu extends \AbstractCommand implements \IAjaxCommand {

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
        $this->selection = json_decode($this->params["selection"]);
        $this->x = $this->params["x"];
        $this->y = $this->params["y"];
        $this->height = $this->params["height"];
        $this->width = $this->params["width"];
        $portal = \lms_portal::get_instance();
        $lms_user = $portal->get_user();
        $this->logged_in = $lms_user->is_logged_in();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $count = count($this->selection);
        $explorerUrl = \Explorer::getInstance()->getAssetUrl();
        if (!in_array($this->id, $this->selection) ||(in_array($this->id, $this->selection) && $count == 1)) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            $name = $object->get_name();
            $env = $object->get_environment();

            $firstElement;
            $counter = 0;
            $inventory = $env->get_inventory();
            foreach ($inventory as $key => $element) {
                if($element instanceof \steam_user || $element instanceof \steam_trashbin) $counter++;
                if ($element->get_id() == $this->id) {
                    $index = $key;
                    $firstElement = $counter;
                    $counter = 0;
                }
            }

            $popupMenu =  new \Widgets\PopupMenu();

            if ($object instanceof \steam_trashbin) {
                $items = array(
                  array("name" => "Papierkorb leeren", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"));
            } else if ($env instanceof \steam_trashbin) {
                $oldEnv = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["env"]);
                if ($oldEnv instanceof \steam_object && $oldEnv->check_access(SANCTION_WRITE)) {
                    $restoreIcon = $explorerUrl . "icons/menu/svg/restore.svg";
                    $items = array(
                        array("name" => "<svg><use xlink:href='{$restoreIcon}#restore'/></svg> Wiederherstellen", "command" => "Restore", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'env':document.getElementById('environment').value}", "type" => "nonModalUpdater"));
                } else {
                    $items = array(array("name" => "Keine Aktionen möglich"));
                }
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
                $folderIcon = $explorerUrl . "icons/mimetype/svg/folder.svg";
                $subscribeIcon = $explorerUrl . "icons/subscribe.svg";
                $unsubscribeIcon = $explorerUrl . "icons/unsubscribe.svg";
                $downloadIcon = $explorerUrl . "icons/menu/svg/download.svg";

                $subscription = "";
                $user = $GLOBALS["STEAM"]->get_current_steam_user();
                if ($user->get_name() == "root"){
                  $isRoot = true;
                } else {
                  $isRoot = false;
                }

                //prepare subscription element it it is an enabled extension
                if (strpos(EXTENSIONS_WHITELIST, "PortletSubscription")) {
                  if ($isRoot || !strpos(CREATE_RESTRICTED_TO_ROOT, "PortletSubscription")){
                    $type = getObjectType($object);
                    if ($type === "forum" || $type === "wiki" || $type === "room" || $type === "gallery" || $type === "portal" || ($type === "rapidfeedback" && $object->get_creator()->get_id() == $user->get_id()) || ($type === "document" && strstr($object->get_attribute(DOC_MIME_TYPE), "text")) || $type === "postbox") {
                        $subscriptions = $user->get_attribute("USER_HOMEPORTAL_SUBSCRIPTIONS");
                        if (is_array($subscriptions) && in_array($object->get_id(), $subscriptions)) {
                            $subscription = array("name" => "<svg><use xlink:href='{$unsubscribeIcon}#unsubscribe'/></svg> Abbestellen", "command" => "Unsubscribe", "namespace" => "explorer", "params" => "{'id':'{$object->get_id()}' }", "type" => "nonModalUpdater");
                        } else {
                            $subscription = array("name" => "<svg><use xlink:href='{$subscribeIcon}#subscribe'/></svg> Abonnieren", "command" => "Subscribe", "namespace" => "explorer", "params" => "{'id':'{$object->get_id()}', 'column' : '2' }", "type" => "nonModalUpdater");
                        }
                    }
                  }
                }

                $items = array(
                    ($this->logged_in && $object->check_access(SANCTION_READ)) ? array("name" => "<svg><use xlink:href='{$copyIcon}#copy'/></svg> Kopieren", "command" => "Copy", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform") : "",
                    ($object->check_access(SANCTION_WRITE)) ? array("name" => "<svg><use xlink:href='{$cutIcon}#cut'/></svg> Ausschneiden", "command" => "Cut", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform") : "",
                    ($this->logged_in) ? array("name" => "<svg><use xlink:href='{$referIcon}#refer'/></svg> Referenz erstellen", "command" => "Reference", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform") : "",
                    ($object->check_access(SANCTION_WRITE)) ? array("name" => "<svg><use xlink:href='{$trashIcon}#trash'/></svg> Löschen", "command" => "Delete", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "nonModalUpdater") : "",
                    ($object->check_access(SANCTION_WRITE)) ? array("name" => "<svg><use xlink:href='{$brushIcon}#brush'/></svg> Einfärben", "direction" => "right", "menu" => array (
                        array("raw" => " <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'transparent'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='greyColor'><use xlink:href='{$explorerUrl}icons/menu/svg/transparent.svg#transparent'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'red'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='redColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'orange'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='orangeColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'yellow'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='yellowColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'green'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='greenColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'blue'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='blueColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'purple'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='purpleColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'grey'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><svg class='greyColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>"),
                    )) : "",
                    ($this->logged_in /*&& !\Bookmarks\Model\Bookmark::isBookmark($this->id)*/) ? array("name" => "<svg><use xlink:href='{$bookmarkIcon}#bookmark'/></svg> Lesezeichen anlegen", "command" => "AddBookmark", "namespace" => "bookmarks", "params" => "{'id':'{$this->id}'}", "type" => "inform") : "",

                    $subscription,

                    ($object->check_access(SANCTION_WRITE) && count($inventory) >=2) ? array("name" => "<svg><use xlink:href='{$sortIcon}#sort'/></svg> Umsortieren", "direction" => "right", "menu" => array(
                        ($index > $firstElement) ? array("name" => "<svg><use xlink:href='{$topIcon}#top'/></svg> Ganz nach oben", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'top'}", "type" => "nonModalUpdater") : "",
                        ($index > $firstElement) ? array("name" => "<svg><use xlink:href='{$upIcon}#up'/></svg> Eins nach oben", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'up'}", "type" => "nonModalUpdater") : "",
                        ($index < count($inventory)-1-$counter) ? array("name" => "<svg><use xlink:href='{$downIcon}#down'/></svg> Eins nach unten", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'down'}", "type" => "nonModalUpdater") : "",
                        ($index < count($inventory)-1-$counter) ? array("name" => "<svg><use xlink:href='{$bottomIcon}#bottom'/></svg> Ganz nach unten", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'bottom'}", "type" => "nonModalUpdater") : ""
                    )) : "",
                    array("raw" => "<a href=\"#\" style=\"width:500px;\" onclick=\"event.stopPropagation(); removeAllDirectEditors();if (!jQuery('#{$this->id}_1').hasClass('directEditor')) { jQuery('#{$this->id}_1').addClass('directEditor').html(''); var obj = new Object; obj.id = '{$this->id}'; sendRequest('GetDirectEditor', obj, '{$this->id}_1', 'nonModalUpdater'); } jQuery('.popupmenuwrapper').parent().html('');jQuery('.open').removeClass('open'); jQuery('#footer_wrapper').css('padding-top', '0px'); return false;\"><svg><use xlink:href='{$renameIcon}#rename'/></svg> Umbenennen</a>"),
                    (($object instanceof \steam_container) && ($object->get_attribute("bid:presentation") === "index") && ($object->check_access(SANCTION_READ))) ? array("name" => "<svg><use xlink:href='{$folderIcon}#folder'/></svg> Ordnerinhalt anzeigen", "link" => PATH_URL . "Explorer/Index/" . $this->id . "/?view=list") : "",
                    (($object instanceof \steam_document) && ($object->get_attribute(DOC_MIME_TYPE) != "text/html") && ($object->check_access(SANCTION_READ))) ? array("name" => "<svg><use xlink:href='{$downloadIcon}#download'/></svg> Herunterladen", "link" => PATH_URL . "Download/Document/" . $this->id . "/" . $name) : "",
                    ($this->logged_in) ? array("name" => "SEPARATOR") : "",
                    array("name" => "<svg><use xlink:href='{$propertiesIcon}#properties'/></svg> Eigenschaften", "command" => "Properties", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"),

                    //display rights dialog for a postbox or for a non postbox object
                    ($object->check_access(SANCTION_SANCTION) && ($object->get_attribute(OBJ_TYPE) === 'postbox')) ? array("name" => "<svg><use xlink:href='{$rightsIcon}#rights'/></svg> Rechte", "command" => "Sanctions", "namespace" => "postbox", "params" => "{'id':'{$this->id}'}", "type" => "popup") : "",
                    ($object->check_access(SANCTION_SANCTION) && (stristr($object->get_attribute(OBJ_TYPE), 'postbox') === FALSE)) ? array("name" => "<svg><use xlink:href='{$rightsIcon}#rights'/></svg> Rechte", "command" => "Sanctions", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup") : ""
                );
            }

            $popupMenu->setItems($items);
            $popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
        } else {
            $writeAccess = TRUE;
            $readAccess = TRUE;
            $downloadable = true;
            foreach ($this->selection as $selectedObjectID) {
                $selectedObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $selectedObjectID);
                if(!($selectedObject instanceof \steam_document) || ($selectedObject->get_attribute(DOC_MIME_TYPE) == "text/html")){
                  $downloadable = false;
                }
                if (!$selectedObject->check_access(SANCTION_WRITE)) {
                    $writeAccess = FALSE;
                }
                if (!$selectedObject->check_access(SANCTION_READ)) {
                    $readAccess = FALSE;
                    $downloadable = false;
                }
            }

            $copyIcon = $explorerUrl . "icons/menu/svg/copy.svg";
            $cutIcon = $explorerUrl . "icons/menu/svg/cut.svg";
            $referIcon = $explorerUrl . "icons/menu/svg/refer.svg";
            $trashIcon = $explorerUrl . "icons/menu/svg/trash.svg";
            $brushIcon = $explorerUrl . "icons/menu/svg/brush.svg";
            $downloadIcon = $explorerUrl . "icons/menu/svg/download.svg";

            $viewAttribute = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("EXPLORER_VIEW");
        		if($viewAttribute && $viewAttribute == "gallery"){
              $paramsArrayFunction = "getGalleryParamsArray";
              $ElementIdFunction = "getGalleryElementIdArray";
              $SelectionFunction = "getGallerySelectionAsArray().length";
            }else{
              $paramsArrayFunction = "getParamsArray";
              $ElementIdFunction = "getElementIdArray";
              $SelectionFunction = "getSelectionAsArray().length";
            }

            $popupMenu =  new \Widgets\PopupMenu();
            if ($this->logged_in) {
                $items = array(
                    ($readAccess) ? array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Copy', $paramsArrayFunction({}), $ElementIdFunction(''), 'inform', null, null, 'explorer', 'Kopiere Objekte ...', 0,  $SelectionFunction); return false;\"><svg><use xlink:href='{$copyIcon}#copy'/></svg> {$count} Objekte kopieren</a>") : "",
                    ($writeAccess) ? array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Cut', $paramsArrayFunction({}), $ElementIdFunction(''), 'inform', null, null, 'explorer', 'Schneide Objekte aus ...', 0,  $SelectionFunction); return false;\"><svg><use xlink:href='{$cutIcon}#cut'/></svg> {$count} Objekte ausschneiden</a>") : "",
                    array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Reference', $paramsArrayFunction({}), $ElementIdFunction(''), 'inform', null, null, 'explorer', 'Referenziere Objekte ...', 0,  $SelectionFunction); return false;\"><svg><use xlink:href='{$referIcon}#refer'/></svg> {$count} Objektreferenzen erstellen</a>"),
                    ($writeAccess) ? array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Delete', $paramsArrayFunction({}), $ElementIdFunction(''), 'updater', null, null, 'explorer', 'Lösche Objekte ...', 0,  $SelectionFunction); return false;\"><svg><use xlink:href='{$trashIcon}#trash'/></svg> {$count} Objekte löschen</a>") : "",
                    ($downloadable) ? array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Download', $paramsArrayFunction({}), $ElementIdFunction(''), 'inform', null, null, 'explorer', 'Lade Objekte herunter ...', 0,  $SelectionFunction); return false;\"><svg><use xlink:href='{$downloadIcon}#download'/></svg> {$count} Objekte herunterladen</a>") : "",
                    ($writeAccess) ? array("name" => "<svg><use xlink:href='{$brushIcon}#brush'/></svg> {$count} Objekte einfärben", "direction" => "right", "menu" => array (
                    array("raw" => " <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'transparent'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='greyColor'><use xlink:href='{$explorerUrl}icons/menu/svg/transparent.svg#transparent'/></svg></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'red'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='redColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'orange'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='orangeColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'yellow'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='yellowColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'green'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='greenColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'blue'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='blueColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'purple'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='purpleColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'grey'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><svg class='greyColor'><use xlink:href='{$explorerUrl}icons/menu/svg/color.svg#color'/></svg></a>"),
                    )) : "",
                );
            } else {
                $items = array(array("name" => "Keine Aktionen möglich"));
            }
            $popupMenu->setItems($items);
            $popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
        }
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }
}
?>

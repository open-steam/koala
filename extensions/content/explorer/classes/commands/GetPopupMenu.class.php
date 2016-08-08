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
                    $restoreIcon = $explorerUrl . "icons/menu/restore.png";
                    $items = array(
                        array("name" => "<img src=\"{$restoreIcon}\">Wiederherstellen", "command" => "Restore", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'env':document.getElementById('environment').value}", "type" => "nonModalUpdater"));
                } else {
                    $items = array(array("name" => "Keine Aktionen möglich"));
                }
            } else {
                $copyIcon = $explorerUrl . "icons/menu/copy.png";
                $cutIcon = $explorerUrl . "icons/menu/cut.png";
                $referIcon = $explorerUrl . "icons/menu/refer.png";
                $trashIcon = $explorerUrl . "icons/menu/trash.png";
                $hideIcon = $explorerUrl . "icons/menu/hide.png";
                $bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
                $colorpickerIcon = \Portal::getInstance()->getAssetUrl() . "icons/colorpicker.png";
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
                $subscribeIcon = $explorerUrl . "icons/subscribe.png";
                $unsubscribeIcon = $explorerUrl . "icons/unsubscribe.png";
                $downloadIcon = $explorerUrl . "icons/menu/download.png";

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
                            $subscription = array("name" => "<img src=\"{$unsubscribeIcon}\">Abbestellen", "command" => "Unsubscribe", "namespace" => "explorer", "params" => "{'id':'{$object->get_id()}' }", "type" => "reload");
                        } else {
                            $subscription = array("name" => "<img src=\"{$subscribeIcon}\">Abonnieren", "command" => "Subscribe", "namespace" => "explorer", "params" => "{'id':'{$object->get_id()}', 'column' : '2' }", "type" => "reload");
                        }
                    }
                  }
                }

                $items = array(
                    ($this->logged_in && $object->check_access(SANCTION_READ)) ? array("name" => "<img src=\"{$copyIcon}\">Kopieren", "command" => "Copy", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform") : "",
                    ($object->check_access(SANCTION_WRITE)) ? array("name" => "<img src=\"{$cutIcon}\">Ausschneiden", "command" => "Cut", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform") : "",
                    ($this->logged_in) ? array("name" => "<img src=\"{$referIcon}\">Referenz erstellen", "command" => "Reference", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform") : "",
                    ($object->check_access(SANCTION_WRITE)) ? array("name" => "<img src=\"{$trashIcon}\">Löschen", "command" => "Delete", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "nonModalUpdater") : "",
                    ($object->check_access(SANCTION_WRITE)) ? array("name" => "<img src=\"{$colorpickerIcon}\">Einfärben", "direction" => "right", "menu" => array (
                        array("raw" => " <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'transparent'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/transparent.png\"></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'red'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/red.png\"></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'orange'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/orange.png\"></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'yellow'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/yellow.png\"></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'green'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/green.png\"></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'blue'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/blue.png\"></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'purple'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/purple.png\"></a>
                            <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'grey'}, 'listviewer-overlay', 'nonModalUpdater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/grey.png\"></a>"),
                    )) : "",
                    ($this->logged_in /*&& !\Bookmarks\Model\Bookmark::isBookmark($this->id)*/) ? array("name" => "<img src=\"{$bookmarkIcon}\">Lesezeichen anlegen", "command" => "AddBookmark", "namespace" => "bookmarks", "params" => "{'id':'{$this->id}'}", "type" => "inform") : "",

                    $subscription,

                    ($object->check_access(SANCTION_WRITE) && count($inventory) >=2) ? array("name" => "<img src=\"{$sortIcon}\">Umsortieren", "direction" => "right", "menu" => array(
                        ($index > $firstElement) ? array("name" => "<img src=\"{$topIcon}\">Ganz nach oben", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'top'}", "type" => "nonModalUpdater") : "",
                        ($index > $firstElement) ? array("name" => "<img src=\"{$upIcon}\">Eins nach oben", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'up'}", "type" => "nonModalUpdater") : "",
                        ($index < count($inventory)-1-$counter) ? array("name" => "<img src=\"{$downIcon}\">Eins nach unten", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'down'}", "type" => "nonModalUpdater") : "",
                        ($index < count($inventory)-1-$counter) ? array("name" => "<img src=\"{$bottomIcon}\">Ganz nach unten", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'bottom'}", "type" => "nonModalUpdater") : ""
                    )) : "",

                    ($this->logged_in) ? array("name" => "SEPARATOR") : "",
                    array("raw" => "<a href=\"#\" style=\"width:500px;\" onclick=\"event.stopPropagation(); removeAllDirectEditors();if (!jQuery('#{$this->id}_1').hasClass('directEditor')) { jQuery('#{$this->id}_1').addClass('directEditor').html(''); var obj = new Object; obj.id = '{$this->id}'; sendRequest('GetDirectEditor', obj, '{$this->id}_1', 'nonModalUpdater'); } jQuery('.popupmenuwrapper').parent().html('');jQuery('.open').removeClass('open'); return false;\"><img src=\"{$renameIcon}\">Umbenennen</a>"),
                    (($object instanceof \steam_container) && ($object->get_attribute("bid:presentation") === "index") && ($object->check_access(SANCTION_READ))) ? array("name" => "<img src=\"{$blankIcon}\">Listenansicht", "link" => PATH_URL . "Explorer/Index/" . $this->id . "/?view=list") : "",
                    (($object instanceof \steam_document) && ($object->get_attribute(DOC_MIME_TYPE) != "text/html") && ($object->check_access(SANCTION_READ))) ? array("name" => "<img src=\"{$downloadIcon}\">Herunterladen", "link" => PATH_URL . "Download/Document/" . $this->id . "/" . $name) : "",
                    array("name" => "<img src=\"{$propertiesIcon}\">Eigenschaften", "command" => "Properties", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"),

                    //display rights dialog for a postbox or for a non postbox object
                    ($object->check_access(SANCTION_SANCTION) && ($object->get_attribute(OBJ_TYPE) === 'postbox')) ? array("name" => "<img src=\"{$rightsIcon}\">Rechte", "command" => "Sanctions", "namespace" => "postbox", "params" => "{'id':'{$this->id}'}", "type" => "popup") : "",
                    ($object->check_access(SANCTION_SANCTION) && (stristr($object->get_attribute(OBJ_TYPE), 'postbox') === FALSE)) ? array("name" => "<img src=\"{$rightsIcon}\">Rechte", "command" => "Sanctions", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup") : ""
                );
            }

            $popupMenu->setItems($items);
            $popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
        } else {
            $writeAccess = TRUE;
            $readAccess = TRUE;
            foreach ($this->selection as $selectedObjectID) {
                $selectedObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $selectedObjectID);
                if (!$selectedObject->check_access(SANCTION_WRITE)) {
                    $writeAccess = FALSE;
                }
                if (!$selectedObject->check_access(SANCTION_READ)) {
                    $readAccess = FALSE;
                }
            }

            $copyIcon = $explorerUrl . "icons/menu/copy.png";
            $cutIcon = $explorerUrl . "icons/menu/cut.png";
            $referIcon = $explorerUrl . "icons/menu/refer.png";
            $trashIcon = $explorerUrl . "icons/menu/trash.png";
            $hideIcon = $explorerUrl . "icons/menu/hide.png";
            $blankIcon = $explorerUrl . "icons/menu/blank.png";
            $colorpickerIcon = \Portal::getInstance()->getAssetUrl() . "icons/colorpicker.png";

            $path = strtolower($_SERVER["REQUEST_URI"]);
            if(strpos($path, "galleryview") !== false){
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
                    ($readAccess) ? array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Copy', $paramsArrayFunction({}), $ElementIdFunction(''), 'inform', null, null, 'explorer', 'Kopiere Objekte ...', 0,  $SelectionFunction); return false;\"><img src=\"{$copyIcon}\">{$count} Objekte kopieren</a>") : "",
                    ($writeAccess) ? array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Cut', $paramsArrayFunction({}), $ElementIdFunction(''), 'inform', null, null, 'explorer', 'Schneide Objekte aus ...', 0,  $SelectionFunction); return false;\"><img src=\"{$cutIcon}\">{$count} Objekte ausschneiden</a>") : "",
                    array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Reference', $paramsArrayFunction({}), $ElementIdFunction(''), 'inform', null, null, 'explorer', 'Referenziere Objekte ...', 0,  $SelectionFunction); return false;\"><img src=\"{$referIcon}\">{$count} Objektreferenzen erstellen</a>"),
                    ($writeAccess) ? array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Delete', $paramsArrayFunction({}), $ElementIdFunction(''), 'updater', null, null, 'explorer', 'Lösche Objekte ...', 0,  $SelectionFunction); return false;\"><img src=\"{$trashIcon}\">{$count} Objekte löschen</a>") : "",
                    ($writeAccess) ? array("name" => "<img src=\"{$colorpickerIcon}\">{$count} Objekte einfärben", "direction" => "right", "menu" => array (
                    array("raw" => " <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'transparent'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/transparent.png\"></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'red'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/red.png\"></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'orange'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/orange.png\"></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'yellow'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/yellow.png\"></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'green'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/green.png\"></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'blue'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/blue.png\"></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'purple'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/purple.png\"></a>
                        <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', $paramsArrayFunction({'color':'grey'}), $ElementIdFunction('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  $SelectionFunction); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/grey.png\"></a>"),
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

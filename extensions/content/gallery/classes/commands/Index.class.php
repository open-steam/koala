<?php

namespace Gallery\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject->setTitle("Galerie");
        $frameResponseObject = $this->getHtmlForObjectId($frameResponseObject);
        return $frameResponseObject;
    }

    /*
     * returns html content - gallery view
     *
     * @objectId gallery id
     * @from
     */

    public function getHtmlForObjectId(\FrameResponseObject $frameResponseObject) {
        $rawHtml = new \Widgets\RawHtml();
        $objectId = $this->id;
        if (isset($this->params[1])) {
            $from = $this->params[1];
        } else {
            $from = 0;
        }

        $steam = $GLOBALS["STEAM"]->get_id();
        $currentRoom = \steam_factory::get_object($steam, $objectId);
        $objType = getObjectType($currentRoom);
        if($objType !== "gallery"){
            $errorHtml = new \Widgets\RawHtml();
            $errorHtml->setHtml("Die angeforderte Seite kann nicht dargestellt werden.");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;
        }
        
        $this->object = $currentRoom;
        $currentRoomPath = $currentRoom->get_path(1);
        $currentRoomData = $currentRoom->get_attributes(array(OBJ_NAME, OBJ_DESC), 1);
        $steamUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $steamUserId = $steamUser->get_id();

        //check if user may write in this folder
        $writeAllowed = $currentRoom->check_access_write($steamUser, 1);

        //get inventory and inventorys attributes if allowed to




        $allowed = $currentRoom->check_access_read($steamUser, 1);
        $result = $GLOBALS["STEAM"]->buffer_flush();
        $writeAllowed = $result[$writeAllowed];
        $allowed = $result[$allowed];
        $currentRoomPath = $result[$currentRoomPath];
        $currentRoomData = $result[$currentRoomData];

        $currentRoomDisplayName = str_replace("'s workarea", "", stripslashes($currentRoomData[OBJ_NAME]));

        if (isset($currentRoomData[OBJ_DESC]) && $currentRoomData[OBJ_DESC] != "") {
            $currentRoomDisplayName = $currentRoomData[OBJ_DESC];
        }
        $currentRoomDisplayName = str_replace("s workroom.", "", $currentRoomDisplayName);

        $numberOfThumbs = 10;

        //forces a stable navigation structure
        $from-=$from % $numberOfThumbs;


        //navigation commands
        $picCount = sizeof($currentRoom->get_inventory());

        $to = $from + ( $numberOfThumbs - 1 );
        if ($from >= $picCount) {
            $from = ($picCount - 1) - ($picCount - 1) % $numberOfThumbs;
        }
        if ($to >= $picCount)
            $to = $picCount - 1;

        if ($allowed && $currentRoom instanceof \steam_container)
            if ($from >= 0 && $to >= $numberOfThumbs - 1)
                $inventory = $currentRoom->get_inventory_paged($from, $to);
            else
                $inventory = $currentRoom->get_inventory_paged(0, $numberOfThumbs - 1);
        else
            $inventory = array();

        //$contentJS = $this->loadTemplate("overlay.template.js");
        //add css
        //	\Gallery::getInstance()->addCSS();
        //add js
        //	\Gallery::getInstance()->addJS();
        //\lms_portal::get_instance()->add_javascript_src("JQuery", PATH_URL . "gallery/js/jquery.min.js");
        //	\lms_portal::get_instance()->add_javascript_src("JQuery", PATH_URL . "gallery/js/jquery.colorbox.js");
        //	\lms_portal::get_instance()->add_javascript_src("JQuery", PATH_URL . "gallery/js/colorbox.control.js");
        //TODO: overlay.template.js not working - overlay to start gallery missing
        //$this->addJS("overlay.template.js");

        $tpl = \Gallery::getInstance()->loadTemplate("gallery.template.html");
        //$tpl= new \HTML_TEMPLATE_IT();
        //$tpl->loadTemplateFile(\Gallery::getInstance()->getExtensionPath()."ui/html/gallery.template.html");
        $tpl->setVariable("CURRENT_OBJ_ID", $objectId);
        $tpl->setVariable("IMAGEURL", \Gallery::getInstance()->getAssetUrl() . "image/round_green_play_button_4044.jpg");

        $tpl->setVariable("FROM", max($from + 1, 1));
        $tpl->setVariable("TO", min($to + 1, $picCount));
        $tpl->setVariable("PIC_COUNT", $picCount);

        $pagemin = $from - $numberOfThumbs;
        $pagemin = max($pagemin, 0);

        //Navigation
        $backlink = "<a href=\"" . PATH_URL . "gallery/index/" . $objectId . "/" . $pagemin . "\" class=\"pagingleft\"><img alt=\"Zurück\" title=\"Zurück\" src=\"" . \Gallery::getInstance()->getAssetUrl() . "/icons/top_seq_prev_on.gif\"></a>";
        if ($from == 0) {

            $backlink = "<a href=\"\" class=\"pagingleft\"><img alt=\"Zurück\" title=\"Zur&uuml;ck\" src=\"" . \Gallery::getInstance()->getAssetUrl() . "/icons/top_seq_prev_off.gif\"></a>";
            $tpl->setVariable("BACKLINK", $backlink);
        } else {
            $tpl->setVariable("BACKLINK", $backlink);
        }

        $pagemax = min($to, $picCount - 1);
        $forwardlink = "<a href=\"" . PATH_URL . "gallery/index/" . $objectId . "/" . ($pagemax + 1) . "\" class=\"pagingleft\"><img alt=\"Zurück\" title=\"Zurück\" src=\"" . \Gallery::getInstance()->getAssetUrl() . "/icons/top_seq_next_on.gif\"></a>";
        if ($to >= $picCount - 1) {
            $forwardlink = "<a href=\"\" class=\"pagingright\"><img alt=\"Vor\" title=\"Vor\" src=\"" . \Gallery::getInstance()->getAssetUrl() . "/icons/top_seq_next_off.gif\">";
            $tpl->setVariable("FORWARDLINK", $forwardlink);
        } else {

            $tpl->setVariable("FORWARDLINK", $forwardlink);
        }
        //Rights
        foreach ($inventory as $item) {
            $tnr[$item->get_id()] = array();
            $tnr[$item->get_id()]["creator"] = $item->get_creator(1);
            $tnr[$item->get_id()]["item_write_access"] = $item->check_access_write($GLOBALS["STEAM"]->get_current_steam_user(), 1);
            $tnr[$item->get_id()]["item_read_access"] = $item->check_access_read($GLOBALS["STEAM"]->get_current_steam_user(), 1);
        }
        $result = $GLOBALS["STEAM"]->buffer_flush();
        $creators = array();
        $itemWriteAccess = array();
        $itemReadAccess = array();
        foreach ($inventory as $item) {
            $creators[$item->get_id()] = $result[$tnr[$item->get_id()]["creator"]];
            $itemWriteAccess[$item->get_id()] = $result[$tnr[$item->get_id()]["item_write_access"]];
            $itemReadAccess[$item->get_id()] = $result[$tnr[$item->get_id()]["item_read_access"]];
        }
        \steam_factory::load_attributes($steam, $inventory, array(OBJ_NAME, OBJ_DESC, OBJ_KEYWORDS, DOC_MIME_TYPE, "bid:description"));

        // If you want to use further Methods of caching e.g. PHP PEARs Cache_Lite
        // insert caching mechanisms in here...
        // below this, the steam connector is no longer used...

        $undisplayedPicCount = 0;


        //GET RIGHTS

        $sanction = $currentRoom->get_sanction();

        $attrib = $this->object->get_attributes(array(OBJ_NAME, OBJ_DESC, "bid:doctype"));
        $bid_doctype = isset($attrib["bid:doctype"]) ? $attrib["bid:doctype"] : "";
        $docTypeQuestionary = strcmp($attrib["bid:doctype"], "questionary") == 0;
        $docTypeMessageBoard = $this->object instanceof \steam_messageboard;

        // in questionaries the write right is limited to insert rights only
        if ($docTypeQuestionary) {
            $SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_INSERT;
        }
        // In message boards only annotating is allowed. The owner
        // is the only one who can also write and change message
        // board entries.
        else if ($docTypeMessageBoard) {
            $SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_ANNOTATE;
        }
        // normal documents
        else {
            $SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_WRITE | SANCTION_EXECUTE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE;
        }
        $sanctionFlag = false;

        if (isset($sanction[$steamUserId])) {
            if ($sanction[$steamUserId] >= $SANCTION_WRITE_FOR_CURRENT_OBJECT) {
                $sanctionFlag = true;
            }
        }
        $env = $currentRoom->get_environment();
        if ($env instanceof \steam_room) {
            $envSanction = $env->get_sanction();
            if (isset($envSanction[$steamUserId])) {
                if ($envSanction[$steamUserId] >= $SANCTION_WRITE_FOR_CURRENT_OBJECT) {
                    $sanctionFlag = true;
                }
            }
        }
        $currentRoomCreater = $currentRoom->get_creator();
        $currentRoomCreaterId = $currentRoomCreater->get_id();
        if ($currentRoomCreaterId == $steamUserId) {
            $sanctionFlag = true;
        }


        for ($i = 0; $i < count($inventory); $i++) {
            $item = $inventory[$i];
            if (!$itemReadAccess[$item->get_id()]) {
                $undisplayedPicCount++;
                continue;
            }


            // render a steam_document
            if ($item instanceof \steam_document) {
                $itemMimetype = $item->get_attribute(DOC_MIME_TYPE);
                //care for documents not to be displayed in the browser
                if ($itemMimetype === "image/gif" || $itemMimetype === "image/jpg"
                        || $itemMimetype === "image/jpeg" || $itemMimetype === "image/png") {
                    $tpl->setCurrentBlock("ITEM");
                    if ($sanctionFlag) {
                        $tpl->setVariable("REMOVE_ICON", \Gallery::getInstance()->getAssetUrl() . "icons/trash.png");
                        $tpl->setVariable("ITEM_PATH_URL2", PATH_URL);
                        $tpl->setVariable("ITEM_THUMBNAIL_ID2", $item->get_id());
                    }
                    $tpl->setVariable("FULLSCREEN_ICON", \Gallery::getInstance()->getAssetUrl() . "icons/image_fullscreen.png");
                    $tpl->setVariable("SAVE_ICON", \Gallery::getInstance()->getAssetUrl() . "icons/image_save.png");
                    $tpl->setVariable("EDIT_ICON", \Gallery::getInstance()->getAssetUrl() . "icons/image_properties.gif");
                    $popupMenu = new \Widgets\PopupMenu();
                    $popupMenu->setData($item);
                    $popupMenu->setElementId("gallery-overlay");
                    $tpl->setVariable("POPUP_MENU", $popupMenu->getHtml());
                    $rawHtml->addWidget($popupMenu);
                    // Skip image if rights are insufficient

                    $itemName = $item->get_attribute(OBJ_NAME);
                    $itemDescription = $item->get_attribute(OBJ_DESC);
                    $itemKeywords = implode(", ", $item->get_attribute(OBJ_KEYWORDS));
                    //set Item
                    $tpl->setVariable("OBJECT_ID", $item->get_id());
                    $tpl->setVariable("OBJECT_NAME", $itemName);
                    $tpl->setVariable("OBJECT_DESC", $itemDescription);
                    $tpl->setVariable("ITEM_PATH_URL", PATH_URL);
                    $tpl->setVariable("ITEM_THUMBNAIL_ID", $item->get_id());
                    $tpl->setVariable("ITEM_BIGTHUMB_ID", $item->get_id());
                    if ($i - $undisplayedPicCount == 0) {
                        $tpl->setVariable("FIRST_GALLERY_ID", $item->get_id());
                    }
                    $tpl->parse("ITEM");
                }
            }
        }
        $actionBar = new \Widgets\ActionBar();
        $actionBar->setActions(array(array("name" => "Explorer-Ansicht", "link" => PATH_URL . "gallery/explorerView/" . $this->id . "/"), array("name" => "Neues Bild", "ajax" => array("onclick" => array("command" => "Addpicture", "params" => array("id" => $this->id), "requestType" => "popup"))), array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "Properties", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer"))), array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup", "namespace" => "explorer")))));

        $css = self::auslesen(PATH_URL . "gallery/css/style.css");
        $js = self::auslesen(PATH_URL . "gallery/js/code.js");
        $rawHtml->setCss($css);
        $rawHtml->setJs($js);
        $rawHtml->setHtml($tpl->get());
        if ($sanctionFlag) {
            $frameResponseObject->addWidget($actionBar);
        }
        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
    }

    public static function auslesen($path) {
        $ausgabeString = "";
        $thisFileContent = file($path);
        foreach ($thisFileContent as $zeile) {
            $ausgabeString .= $zeile;
        }
        return $ausgabeString;
    }

}

?>
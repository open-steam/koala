<?php

namespace PortletBookmarks\Commands;

class Index extends \AbstractCommand implements \IIdCommand {

    private $params;
    private $id;
    private $content;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->id = $requestObject->getId();
        $user = \lms_steam::get_current_user();
        $bookmarkRoom = $user->get_attribute("USER_BOOKMARKROOM");

        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $parent = $obj->get_environment();
        $portletWidth = $parent->get_attribute("bid:portal:column:width");
        $portletWidth = substr($portletWidth, 0, count($portletWidth)-3);
       
        $numberOfBookmarks = $obj->get_attribute("PORTLET_BOOKMARK_COUNT");

        $bookmarks = $bookmarkRoom->get_inventory();

        $n = $numberOfBookmarks;
        if (count($bookmarks) <= $numberOfBookmarks) {
            $n = count($bookmarks);
        }
        $viewBookmarks = array();
        for ($i = 0; $i < $n; $i++) {
            $viewBookmarks[$i] = $bookmarks[$i];
        }
        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setData($obj);
        $popupMenu->setCommand("GetPopupMenu");
        $popupMenu->setNamespace("PortletBookmarks");
        $popupMenu->setElementId("overlay_menu");
        $popupMenu->setParams(array(array("key" => "id", "value" => $this->id)));
        $listViewer = new \Widgets\ListViewer();
        $headline = new HeadlineProvider();
        $headline->setWidth($portletWidth);
        $listViewer->setHeadlineProvider($headline);
        $listViewer->setContentProvider(new ContentProvider());
        $listViewer->setColorProvider(new ColorProvider());
        $listViewer->setContentFilter(new ContentFilter());
        $listViewer->setContent($viewBookmarks);
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->addWidget($popupMenu);
        $rawHtml->addWidget($listViewer);
        
        $rawHtml->setHtml('<div class="portlet bookmark"><h1 class="headline">Meine Lesezeichen <div class="editbutton" style="display:none;float:right;padding-right:5px;">' . $popupMenu->getHtml() . "</div></h1><div class=\"entry\" style=\"padding:5px;\">" . $listViewer->getHtml() . "</div><div class=\"entry\" style=\"padding-left:".($portletWidth/2-80)."px;padding-right:".($portletWidth/2-80)."px;\"><a href=\"" . PATH_URL . "bookmarks/\">Alle Lesezeichen anzeigen</a></div><br>" . '<div id="overlay_menu"></div></div><br>');

        $this->content = $rawHtml;
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $idResponseObject->addWidget($this->content);
        return $idResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {



        return $frameResponseObject;
    }

}

class HeadlineProvider implements \Widgets\IHeadlineProvider {
    private $width = 352;
    
    public function setWidth($w){
        $this->width = $w;
    }
    public function getHeadlines() {
        //return array("", "Name", "Marker","Änderungsdatum", "Größe");
        return array("", "", "");
    }

    public function getHeadLineWidths() {
        //return array(22, 580, 150);
        
        return array(22, $this->width-172, 130);
    }

    public function getHeadLineAligns() {
        return array("left", "left", "right");
    }

}

class ContentProvider implements \Widgets\IContentProvider {

    private $rawImage = 0;
    private $rawName = 1;
    private $rawChangeDate = 2;

    public function getId($contentItem) {
        return $contentItem->get_id();
    }

    public function getCellData($cell, $contentItem) {
        if (!is_int($cell)) {
            throw new \Exception("cell must be an integer!!");
        }
        
        
        //case there is an not bookmark in bookmarks
        if( $contentItem instanceof \steam_link){
            $contentItemObject = $contentItem->get_source_object();
        }else{
            $contentItemObject = $contentItem;
        }
        
        
        if ($cell == $this->rawImage) {
            return "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($contentItemObject) . "\"></img>";
        } else if ($cell == $this->rawName) {
            $url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItemObject->get_id(), "view");
            $desc = $contentItemObject->get_attribute("OBJ_DESC");
            $name = getCleanName($contentItemObject, 50);

            //check existence of link target
            $sourceObject = $contentItemObject;
            if (!(($sourceObject != null) && ($sourceObject instanceof \steam_object))) {
                return "<div style=\"color:red\">$name (Lesezeichenziel gelöscht)</div>";
            }

            if (isset($url) && $url != "") {
                return "<a href=\"" . $url . "\" title=\"$desc\"> " . $name . "</a>";
            } else {
                return $name;
            }
        } else if ($cell == $this->rawChangeDate) {
            return getReadableDate($contentItemObject->get_attribute("OBJ_LAST_CHANGED"));
        }
    }

    public function getNoContentText() {
        return "Hier können Sie Lesezeichen zu beliebigen Ordnern und Dokumenten anlegen. Wenn Sie den Inhalt eines Ordners betrachten, können Sie Lesezeichen anlegen.";
    }

    public function getOnClickHandler($contentItem) {
        return "";
    }

}

class ColorProvider implements \Widgets\IColorProvider {

    public function getColor($contentItem) {
        $color = $contentItem->get_attribute("OBJ_COLOR_LABEL");
        return ($color === 0) ? "" : $color;
    }

}

class ContentFilter implements \Widgets\IContentFilter {

    public function filterObject($object) {
        if ($object instanceof \steam_user) {
            return true;
        } else {
            return false;
        }
    }

}

?>
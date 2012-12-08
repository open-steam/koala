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
        $listViewer->setHeadlineProvider(new HeadlineProvider());
        $listViewer->setContentProvider(new ContentProvider());
        $listViewer->setColorProvider(new ColorProvider());
        $listViewer->setContentFilter(new ContentFilter());
        $listViewer->setContent($viewBookmarks);
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->addWidget($popupMenu);
        $rawHtml->addWidget($listViewer);
        $rawHtml->setCss('
            .headline .popupmenuanker {
             display: none;
            }
            .headline:hover .popupmenuanker {
             display: block;
}
');
        $rawHtml->setHtml('<h1 class="headline">Meine Lesezeichen' . $popupMenu->getHtml() . "</h1>" . $listViewer->getHtml() . "<br><a class=\"pill button\" href=\"" . PATH_URL . "bookmarks/\">Alle Lesezeichen anzeigen</a>" . '<div id="overlay_menu"></div>');

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

    public function getHeadlines() {
        //return array("", "Name", "Marker","Änderungsdatum", "Größe");
        return array("", "", "");
    }

    public function getHeadLineWidths() {
        //return array(22, 580, 150);
        return array(22, 200, 130);
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

        if ($cell == $this->rawImage) {
            return "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($contentItem->get_source_object()) . "\"></img>";
        } else if ($cell == $this->rawName) {
            $url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->get_source_object()->get_id(), "view");
            $desc = $contentItem->get_source_object()->get_attribute("OBJ_DESC");
            $name = getCleanName($contentItem, 50);

            //check existence of link target
            $sourceObject = $contentItem->get_link_object();
            if (!(($sourceObject != null) && ($sourceObject instanceof \steam_object))) {
                return "<div style=\"color:red\">$name (Lesezeichenziel gelöscht)</div>";
            }

            if (isset($url) && $url != "") {
                return "<a href=\"" . $url . "\" title=\"$desc\"> " . $name . "</a>";
            } else {
                return $name;
            }
        } else if ($cell == $this->rawChangeDate) {
            return getReadableDate($contentItem->get_source_object()->get_attribute("OBJ_LAST_CHANGED"));
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
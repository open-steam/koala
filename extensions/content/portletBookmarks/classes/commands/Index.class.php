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
        if (strpos($portletWidth, "px") === TRUE) {
            $portletWidth = substr($portletWidth, 0, count($portletWidth)-3);
        }

        $bookmarks = $bookmarkRoom->get_inventory();
        $showAllBookmarksLink = "";
        $number = $obj->get_attribute("PORTLET_BOOKMARK_COUNT");
        if($number > count($bookmarks)){
          $number = count($bookmarks);
        }
        else{
          $showAllBookmarksLink = '<br><div style="padding-top: 10px; text-align: center;"><a href="' . PATH_URL . 'bookmarks/">Alle Lesezeichen anzeigen</a></div>';
        }

        $viewBookmarks = array();

        for ($i = 0; $i < $number; $i++) {
            $viewBookmarks[$i] = $bookmarks[$i];
        }
        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setData($obj);
        $popupMenu->setCommand("GetPopupMenuHeadline");
        $popupMenu->setNamespace("PortletBookmarks");
        $popupMenu->setElementId("portal-overlay");
        $popupMenu->setParams(array(array("key" => "portletObjectId", "value" => $this->id)));
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

        $portletName = $obj->get_attribute(OBJ_DESC);
        //if the title is empty the headline will not be displayed (only in edit mode)
        if (empty($portletName)) {
            $head = "headline editbutton";
        } else {
            $head = "headline";
        }

        $rawHtml->setHtml('<div id="'. $this->id .'" class="portlet bookmark"><h1 class="'. $head .'">'. $portletName .'<div class="editbutton" style="display:none;float:right;">' . $popupMenu->getHtml() . '</div></h1><div class="entry"><div>' . $listViewer->getHtml() . '</div>' . $showAllBookmarksLink . '<div id="overlay_menu"></div></div></div>');
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
    private $width;

    public function setWidth($width) {
        if (($width - 40) < 0) {
            $this->width = 40;
        } else {
            $this->width = $width;
        }
    }

    public function getHeadlines() {
        //return array("", "Name", "Marker","Änderungsdatum", "Größe");
        return array("", "", "");
    }

    public function getHeadLineAbsoluteWidths() {
        //return array(22, 580, 150);

        return array(22, $this->width-40, 0);
    }

    public function getHeadLineAligns() {
        return array("left", "left", "right");
    }
    
    public function getHeadLineClasses() {
        return array("", "", "");
    }

}

class ContentProvider implements \Widgets\IContentProvider {

    private $rawImage = 0;
    private $rawName = 1;
    //private $rawChangeDate = 2;

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
          if ($contentItem instanceof \steam_exit) {
              $exitObj = $contentItem->get_exit();
              if ($exitObj === 0) {
                  $icon = "folder.png";
              } else {
                $icon = deriveIcon($exitObj);
              }
          } else if ($contentItem instanceof \steam_link) {
              $linkObj = $contentItem->get_link_object();
              if ($linkObj === 0) {
                  $icon = "generic.png";
              } else {
                $icon = deriveIcon($linkObj);
              }
          } else {
            $icon = deriveIcon($contentItem);
          }
          $iconSVG = str_replace("png", "svg", $icon);
          $idSVG = str_replace(".svg", "", $iconSVG);
          $iconSVG = PATH_URL . "explorer/asset/icons/mimetype/svg/" . $iconSVG;
          $url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->get_id(), "view");
          return "<a style='text-align:center; display:block;' href=\"" . $url . "\"><svg style='width:16px; height:16px;'><use xlink:href='" . $iconSVG . "#" . $idSVG . "'/></svg></a>";
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

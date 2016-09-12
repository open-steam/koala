<?php

namespace Explorer\Commands;

class LoadContent extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $objects;
    private $object;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];

        $this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        if ($this->object && $this->object instanceof \steam_container) {
            $this->objects = $this->object->get_inventory();
        } else {
            $this->objects = array();
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $listViewer = new \Widgets\ListViewer();
        $listViewer->setHeadlineProvider(new HeadlineProvider($this->object));
        $listViewer->setContentProvider(new ContentProvider($this->object));
        $listViewer->setColorProvider(new ColorProvider());
        $listViewer->setContentFilter(new ContentFilter());
        $listViewer->setContent($this->objects);
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($listViewer);
        $tipsy = new \Widgets\Tipsy();
        $ajaxResponseObject->addWidget($tipsy);

        return $ajaxResponseObject;
    }

}

class HeadlineProvider implements \Widgets\IHeadlineProvider {

    private $object;
    //save the current object to check the attribute SHOW_TAGS lateron
    function __construct($object){
        $this->object = $object;
    }

    public function getHeadlines() {
        if (defined("EXPLORER_TAGS_VISIBLE") && EXPLORER_TAGS_VISIBLE && $this->object->get_attribute("SHOW_TAGS") == "1") {
            return array("", "Name", "", "Beschreibung", "", "Änderungsdatum", "Größe", "", "<input onChange=\"elements = jQuery('.listviewer-items .show > div > input'); for (i=0; i<elements.length; i++) { if (this.checked != elements[i].checked) { elements[i].click() }}\" type=\"checkbox\" ></input>");
        } else {
            return array("", "Name", "", "Beschreibung", "", "Änderungsdatum", "Größe", "", "<input onChange=\"elements = jQuery('.listviewer-item > div > input'); for (i=0; i<elements.length; i++) { if (this.checked != elements[i].checked) { elements[i].click() }}\" type=\"checkbox\" ></input>");
        }
    }

    public function getHeadLineWidths() {
        return array(25, 250, 10, 380, 10, 145, 75, 30, 20);
    }

    public function getHeadLineAligns() {
        return array("left", "left", "left", "left", "left", "right", "right", "right", "right");
    }

    public function getOnClickHandler($headline) {
      if(strpos($headline, "Name") !== false) {
        return "sortByName(this)";
      }
      if(strpos($headline, "Änderungsdatum") !== false) {
        return "sortByDate(this)";
      }
      else{
        return "";
      }

    }

    public function getOnMouseOverHandler($headline) {
      if(strpos($headline, "Name") !== false) {
        return "jQuery(this).addClass('hover')";
      }
      if(strpos($headline, "Änderungsdatum") !== false) {
        return "jQuery(this).addClass('hover')";
      }
      else{
        return "";
      }
    }

    public function getOnMouseOutHandler($headline) {
      if(strpos($headline, "Name") !== false) {
        return "jQuery(this).removeClass('hover')";
      }
      if(strpos($headline, "Änderungsdatum") !== false) {
        return "jQuery(this).removeClass('hover')";
      }
      else{
        return "";
      }
    }

}

class ContentProvider implements \Widgets\IContentProvider {

    private $rawImage = 0;
    private $rawName = 1;
    private $rawDesc = 3;
    //private $rawMarker = 5;
    private $rawChangeDate = 5;
    private $rawSize = 6;
    //private $rawSubscribe = 6;
    private $rawMenu = 7;
    private $rawCheckbox = 8;
    private $object;

    //save the current object to check the attribute SHOW_TAGS lateron
    function __construct($object){
        $this->object = $object;
    }


    public function getId($contentItem) {
        return $contentItem->get_id();
    }

    public function getCellData($cell, $contentItem) {
        if (!is_int($cell)) {
            throw new \Exception("cell must be an integer!!");
        }

        if ($cell == $this->rawCheckbox) {
            if (!($contentItem instanceof \steam_trashbin)) {
                return "<input id=\"{$contentItem->get_id()}_checkbox\" style=\"margin-top:0px\" type=\"checkbox\" onclick=\"event.stopPropagation(); if (this.checked) { jQuery('#{$contentItem->get_id()}').addClass('listviewer-item-selected') } else { jQuery('#{$contentItem->get_id()}').removeClass('listviewer-item-selected') }\"></input>";
            } else {
                return "";
            }
        } elseif ($cell == $this->rawImage) {
            $url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->get_id(), "view");

            $icon = deriveIcon($contentItem);
            $iconSVG = str_replace("png", "svg", $icon);
            $idSVG = str_replace(".svg", "", $iconSVG);
            $iconSVG = PATH_URL . "explorer/asset/icons/mimetype/svg/" . $iconSVG;

            return "<a style='text-align:center; display:block;' href=\"" . $url . "\"><svg style='width:16px; height:16px;'><use xlink:href='" . $iconSVG . "#" . $idSVG . "'/></svg></a>";
        } elseif ($cell == $this->rawName) {
            $creator = $contentItem->get_creator();
            if(is_object($creator)){
              $creatorHtml = "<div style=\"font-weight:bold; width:100px; float:left;\">Besitzer</div> <img style=\"margin: 3px\" align=\"middle\" src=\"" . PATH_URL . "download/image/"
                           . $creator->get_attribute(OBJ_ICON)->get_id() . "/30/30\"> "
                           . $creator->get_attribute(USER_FIRSTNAME) . " "
                           . $creator->get_attribute(USER_FULLNAME) . "<br clear=\"all\">";
            }
            $tipsy = new \Widgets\Tipsy();
            $tipsy->setElementId($contentItem->get_id());
            $tipsyHtml = $creatorHtml
                       . "<div style=\"font-weight:bold; width:100px; float:left;\">zuletzt geändert</div> " . getFormatedDate($contentItem->get_attribute(OBJ_LAST_CHANGED)) . "<br>" //);
                       . "<div style=\"font-weight:bold; width:100px; float:left;\">erstellt</div> " . getFormatedDate($contentItem->get_attribute(OBJ_CREATION_TIME)) . "<br>";
            $tags = $contentItem->get_attribute(OBJ_KEYWORDS);
            if(sizeOf($tags) > 0){
              $tipsyHtml .= "<div style=\"font-weight:bold; width:100px; float:left;\">Tags</div> " . implode(" ", $tags) . "<br>";
            }
            $tipsy->setHtml($tipsyHtml);

            $url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->get_id(), "view");
            $desc = $contentItem->get_attribute("OBJ_DESC");
            $name = getCleanName($contentItem, 50);

            if (isset($url) && $url != "") {
                if ($contentItem instanceof \steam_link) {
                    $linkObject = $contentItem->get_link_object();
                    $linkObjectType = getObjectType($linkObject);
                    if ($linkObjectType === "rapidfeedback") {
                        $url = PATH_URL . "rapidfeedback/Index/" . $linkObject->get_id() . "/";
                    }
                }
                if ($contentItem instanceof \steam_docextern) {
                    return "<a href=\"" . $url . "new/" . "\" target=\"_blank\"> " . $name . "</a>" . "<script>" . $tipsy->getHtml() . "</script>";
                }

                return "<a href=\"" . $url . "\"> " . $name . "</a>" . "<script>" . $tipsy->getHtml() . "</script>";

            } else {
                return $name . "<script>" . $tipsy->getHtml() . "</script>";
            }
        } elseif ($cell == $this->rawDesc) {
          return $contentItem->get_attribute("OBJ_DESC");
        } elseif ($cell == $this->rawMarker) {
            //  return ""; //disabled
            //if (defined("EXPLORER_TAGS_VISIBLE") && EXPLORER_TAGS_VISIBLE && $this->object->get_attribute("SHOW_TAGS") == "1") {
                $keywords = $contentItem->get_attribute("OBJ_KEYWORDS");
                $keywordList = "";
                foreach ($keywords as $keyword) {
                    if ($keyword !== "") {
                        $keywordList.=$keyword . " ";
                    }
                }
                return $keywordList;
            //} else {
              //  return "";
            //}

            //speed test //TODO: fix
            $html = "";
            $html .= "<div class=\"marker\">" . \Explorer\Model\Sanction::getMarkerHtml($contentItem) . "</div>";

            $html .= "<div class=\"marker\" id=\"{$contentItem->get_id()}_BookmarkMarkerWrapper\">";
            $linkError = false;
            if ($contentItem instanceof \steam_exit) {
                $exitObject = $contentItem->get_exit();
                if ($exitObject instanceof \steam_object) {
                    $id = $exitObject->get_id();
                } else {
                    $linkError = true;
                    $html .= "<div style=\"color:red\">Referenz defekt</div>";
                }
            } elseif ($contentItem instanceof \steam_link) {
                $linkObject = $contentItem->get_link_object();
                if ($linkObject instanceof \steam_object) {
                    $id = $linkObject->get_id();
                } else {
                    $linkError = true;
                    $html .= "<div style=\"color:red\">Referenz defekt</div>";
                }
            } else {
                $id = $contentItem->get_id();
            }
            if (!$linkError && \Bookmarks\Model\Bookmark::isBookmark($id)) {
                $html .= \Bookmarks\Model\Bookmark::getMarkerHtml($id);
            }
            $html .= "</div>";

            return $html;
        } elseif ($cell == $this->rawChangeDate) {
            return getReadableDate($contentItem->get_attribute("OBJ_LAST_CHANGED"));
        } elseif ($cell == $this->rawSize) {
            return getObjectReadableSize($contentItem);
        } elseif ($cell == $this->rawMenu) {
            $popupMenu = new \Widgets\PopupMenu();
            $popupMenu->setData($contentItem);
            $popupMenu->setElementId("listviewer-overlay");
            return $popupMenu;
        }
    }

    public function getNoContentText() {
        return "Dieser Ordner enthält keine Objekte.";
    }

    public function getOnClickHandler($contentItem) {
        if (!($contentItem instanceof \steam_trashbin)) {
            //check the checkbox at the end of the row. If the order or number of the headlines changes, adjust the selector for the proper field
            return "jQuery('#{$contentItem->get_id()}').children()[8].children[0].checked = !jQuery('#{$contentItem->get_id()}').children()[8].children[0].checked; widgets_listViewer_selection_toggle({$contentItem->get_id()}, jQuery('#{$contentItem->get_id()}').children()[8].children[0].checked);";
        } else {
            return "";
        }
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
        if (get_class($object) === "steam_object") {
            return true;
        } elseif ($object instanceof \steam_user) {
            return true;
        } elseif ($object instanceof \steam_trashbin) {
            return true;
        } elseif ($object instanceof \steam_drawing) {
            return true;
        } elseif ($object instanceof \steam_calendar) {
            return true;
        } elseif ($object instanceof \steam_date) {
            return true;
        } elseif ($object instanceof \steam_group) {
            return true;
        } elseif ($object instanceof \steam_script) {
            return true;
        } else {
            return false;
        }
    }

}

<?php

namespace Clipboard\Commands;

class LoadClipboard extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $objects;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];

        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        if ($object && $object instanceof \steam_container) {
            $this->objects = $object->get_inventory();
        } else {
            $this->objects = array();
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $listViewer = new \Widgets\ListViewer();
        $listViewer->setHeadlineProvider(new HeadlineProvider());
        $listViewer->setContentProvider(new ContentProvider());
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

    public function getHeadlines() {
        return array("", "Name", "", "Beschreibung", "", "Änderungsdatum", "Größe", "", "", "<input onChange=\"elements = jQuery('.listviewer-item > div > input'); for (i=0; i<elements.length; i++) { if (this.checked != elements[i].checked) { elements[i].click() }}\" type=\"checkbox\" ></input>");
    }

    public function getHeadLineAbsoluteWidths() {
        return array(20, 0, 0, 0, 0, 0, 0, 0, 20, 0);
    }

    public function getHeadLineAligns() {
        return array("left", "left", "left", "left", "left", "right", "right", "center", "right", "right");
    }

    public function getHeadLineClasses() {
        return array("", "", "", "", "", "", "", "", "", "");
    }

    public function getOnClickHandler($headline) {
        if (strpos($headline, "Name") !== false) {
            return "sortByName(this)";
        }
        if (strpos($headline, "Änderungsdatum") !== false) {
            return "sortByDate(this)";
        } else {
            return "";
        }
    }

    public function getOnMouseOverHandler($headline) {
        if (strpos($headline, "Name") !== false) {
            return "jQuery(this).addClass('hover')";
        }
        if (strpos($headline, "Änderungsdatum") !== false) {
            return "jQuery(this).addClass('hover')";
        } else {
            return "";
        }
    }

    public function getOnMouseOutHandler($headline) {
        if (strpos($headline, "Name") !== false) {
            return "jQuery(this).removeClass('hover')";
        }
        if (strpos($headline, "Änderungsdatum") !== false) {
            return "jQuery(this).removeClass('hover')";
        } else {
            return "";
        }
    }

}

class ContentProvider implements \Widgets\IContentProvider {

    private $rawImage = 0;
    private $rawName = 1;
    private $rawDesc = 3;
    private $rawMarker = 4;
    private $rawChangeDate = 5;
    private $rawSize = 6;
    private $rawReference = 7;
    private $rawMenu = 8;
    private $rawCheckbox = 9;

    public function getId($contentItem) {
        return $contentItem->get_id();
    }

    public function getCellData($cell, $contentItem) {
        if (!is_int($cell)) {
            throw new \Exception("cell must be an integer!!");
        }

        if ($cell == $this->rawCheckbox) {
            if (!($contentItem instanceof \steam_trashbin)) {
                return "<input id=\"{$contentItem->get_id()}_checkbox\" style=\"margin-top:0px\" type=\"checkbox\" onclick=\"event.stopPropagation(); if(this.checked) { jQuery('#{$contentItem->get_id()}').addClass('listviewer-item-selected') } else { jQuery('#{$contentItem->get_id()}').removeClass('listviewer-item-selected') }\"></input>";
            } else {
                return "";
            }
        } else if ($cell == $this->rawImage) {
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
            //adding Tipsy
            $tipsy = new \Widgets\Tipsy();
            $tipsy->setElementId($contentItem->get_id() . "_" . $this->rawName);
            $tipsyHtml = "<div style=\"font-weight:bold; width:100px; float:left;\">Besitzer</div> <img style=\"margin: 3px\" align=\"middle\" src=\"" . PATH_URL . "download/image/"
                    . $contentItem->get_creator()->get_attribute(OBJ_ICON)->get_id() . "/30/30\"> "
                    . $contentItem->get_creator()->get_attribute(USER_FIRSTNAME) . " "
                    . $contentItem->get_creator()->get_attribute(USER_FULLNAME) . "<br clear=\"all\">"
                    . "<div style=\"font-weight:bold; width:100px; float:left;\">zuletzt geändert</div> " . getFormatedDate($contentItem->get_attribute(OBJ_LAST_CHANGED)) . "<br>"
                    . "<div style=\"font-weight:bold; width:100px; float:left;\">erstellt</div> " . getFormatedDate($contentItem->get_attribute(OBJ_CREATION_TIME)) . "<br>";

            $tags = $contentItem->get_attribute(OBJ_KEYWORDS);
            if (sizeOf($tags) > 0) {
                $tipsyHtml .= "<div style=\"font-weight:bold; width:100px; float:left;\">Tags</div> " . implode(" ", $tags) . "<br>";
            }
            if ($contentItem instanceof \steam_link) {
                $tipsyHtml .= "<br>Dieses Element ist lediglich eine Referenz auf ein bestehendes Objekt. Änderungen können nur am Originalobjekt vorgenommen werden. Ein Klick auf dieses Element führt Sie zum Originalobjekt.<br>";
            }
            $tipsy->setHtml($tipsyHtml);

            $url = \ExtensionMaster::getInstance()->getUrlForObjectId($contentItem->get_id(), "view");
            $desc = $contentItem->get_attribute("OBJ_DESC");
            $name = getCleanName($contentItem, 50);
            if (isset($url) && $url != "") {
                return "<a href=\"" . $url . "\" title=\"$desc\"> " . $name . "</a>" . "<script>" . $tipsy->getHtml() . "</script>";
            } else {
                return $name . "<script>" . $tipsy->getHtml() . "</script>";
            }
        } else if ($cell == $this->rawDesc) {
            return ($contentItem->get_attribute("OBJ_DESC") != "")? "<span>".$contentItem->get_attribute("OBJ_DESC")."</span>" : "";
        } else if ($cell == $this->rawMarker) {
            return "";
            /* $html = "";
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
              } else if ($contentItem instanceof \steam_link) {
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
              return $html; */
        } else if ($cell == $this->rawChangeDate) {
            return getReadableDate($contentItem->get_attribute("OBJ_LAST_CHANGED"), true);
        } else if ($cell == $this->rawSize) {
            return "<span style='white-space:nowrap;'>".getObjectReadableSize($contentItem)."</span>";
        } else if ($cell == $this->rawMenu) {
            $popupMenu = new \Widgets\PopupMenu();
            $popupMenu->setData($contentItem);
            $popupMenu->setElementId("listviewer-overlay");
            return $popupMenu;
        } else if ($cell == $this->rawReference) {
            if ($contentItem instanceof \steam_link) {
                return "<div class='referenceWrapper'><svg style='width:16px; height:16px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/menu/svg/refer.svg#refer'/></svg></div>";
            }
        }
    }

    public function getNoContentText() {
        return "Die Zwischenablage ist leer.";
    }

    public function getOnClickHandler($contentItem) {
        if (!($contentItem instanceof \steam_trashbin)) {
            return "jQuery('#{$contentItem->get_id()}').children()[9].children[0].checked = !jQuery('#{$contentItem->get_id()}').children()[9].children[0].checked; widgets_listViewer_selection_toggle({$contentItem->get_id()}, jQuery('#{$contentItem->get_id()}').children()[9].children[0].checked);";
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
        } else if ($object instanceof \steam_user) {
            return true;
        } else if ($object instanceof \steam_trashbin) {
            return true;
        } else if ($object instanceof \steam_drawing) {
            return true;
        } else if ($object instanceof \steam_calendar) {
            return true;
        } else if ($object instanceof \steam_date) {
            return true;
        } else if ($object instanceof \steam_group) {
            return true;
        } else if ($object instanceof \steam_script) {
            return true;
        } else {
            return false;
        }
    }

}

?>

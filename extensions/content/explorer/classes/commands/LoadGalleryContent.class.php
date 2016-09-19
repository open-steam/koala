<?php

namespace Explorer\Commands;

class LoadGalleryContent extends \AbstractCommand implements \IAjaxCommand {

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
        $ajaxResponseObject->setStatus("ok");

        $galleryStart = new \Widgets\RawHtml();
        $galleryStart->setHtml("<ul id='explorerGallery'>");
        $ajaxResponseObject->addWidget($galleryStart);

        if(sizeOf($this->objects) == 0){
          $noItem = new \Widgets\RawHtml();
          $noItem->setHtml("<div class='gallery-noitem'>Dieser Ordner enthält keine Objekte.</div><script>$('#selectAll').hide();$('#objectSliderLabel').hide();$('#slider').hide();</script>");
          $ajaxResponseObject->addWidget($noItem);
        }

        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $userHiddenAttribute = $currentUser->get_attribute("EXPLORER_SHOW_HIDDEN_DOCUMENTS");

        foreach ($this->objects as $key => $object) {

          if (get_class($object) === "steam_object") {
              continue;
          } elseif ($object instanceof \steam_user) {
              continue;
          } elseif ($object instanceof \steam_trashbin) {
              continue;
          } elseif ($object instanceof \steam_drawing) {
              continue;
          } elseif ($object instanceof \steam_calendar) {
              continue;
          } elseif ($object instanceof \steam_date) {
              continue;
          } elseif ($object instanceof \steam_group) {
              continue;
          } elseif ($object instanceof \steam_script) {
              continue;
          }

          $hidden = "";
          if ($object->get_attribute("bid:hidden") === "1") {
            if($userHiddenAttribute == "TRUE"){
              $hidden = "hiddenObject";
            }
            else{
              continue;
            }
          }

          $entry = new \Widgets\RawHtml();

          $id = $object->get_id();
          $name = $object->get_name();
          $desc = $object->get_attribute('OBJ_DESC');
          $creator = $object->get_creator();
          $tags = $object->get_attribute(OBJ_KEYWORDS);

          if(is_object($creator)){
            $creatorHtml = "<div style=\"font-weight:bold; width:100px; float:left;\">Besitzer</div> <img style=\"margin: 3px\" align=\"middle\" src=\"" . PATH_URL . "download/image/"
                         . $creator->get_attribute(OBJ_ICON)->get_id() . "/30/30\"> "
                         . $creator->get_attribute(USER_FIRSTNAME) . " "
                         . $creator->get_attribute(USER_FULLNAME) . "<br clear=\"all\">";
          }

          $tipsy = new \Widgets\Tipsy();
          $tipsy->setElementId($id);
          $tipsyHtml = $creatorHtml
                  . "<div style=\"font-weight:bold; width:100px; float:left;\">zuletzt geändert</div> " . getFormatedDate($object->get_attribute(OBJ_LAST_CHANGED)) . "<br>" //);
                  . "<div style=\"font-weight:bold; width:100px; float:left;\">erstellt</div> " . getFormatedDate($object->get_attribute(OBJ_CREATION_TIME)) . "<br>";

          if(sizeOf($tags) > 0){
            $tipsyHtml .= "<div style=\"font-weight:bold; width:100px; float:left;\">Tags</div> " . implode(" ", $tags) . "<br>";
          }

          if($desc != ""){
            $tipsyHtml .= "<div style=\"font-weight:bold; width:100px; overflow:hidden; white-space:nowrap; float:left;\">Beschreibung</div> " . $desc . "<br>";
          }

          $tipsy->setHtml($tipsyHtml);

          $url = \ExtensionMaster::getInstance()->getUrlForObjectId($id, "view");

          if ($object instanceof \steam_exit) {
              $exitObj = $object->get_exit();
              if ($exitObj === 0) {
                $icon = "folder.png";
              } else {
                $icon = deriveIcon($exitObj);
              }
          } else if ($object instanceof \steam_link) {
              $linkObj = $object->get_link_object();
              if ($linkObj === 0) {
                $icon = "generic.png";
              } else {
                $icon = deriveIcon($linkObj);
              }
          } else {
            $icon = deriveIcon($object);
          }

          $iconSVG = str_replace("png", "svg", $icon);
          $idSVG = str_replace(".svg", "", $iconSVG);
          $iconSVG = PATH_URL . "explorer/asset/icons/mimetype/svg/" . $iconSVG;

          $text = "Dieses Element ist lediglich eine Referenz auf ein bestehendes Objekt. ";
          $text.= "Änderungen können nur am Originalobjekt vorgenommen werden. ";
          $text.= "Ein Klick auf dieses Element führt Sie zum Originalobjekt.";

          $linkIcon = "<div class='galleryReference'></div>";
          if (isset($url) && $url != "") {
              if ($object instanceof \steam_link) {
                  $linkIcon = "<div title='" . $text . "'><svg class='galleryReference'><use xlink:href='" . PATH_URL . "explorer/asset/icons/menu/svg/refer.svg#refer'/></svg></div>";
                  $linkObject = $object->get_link_object();
                  $linkObjectType = getObjectType($linkObject);
                  if ($linkObjectType === "rapidfeedback") {
                      $url = PATH_URL . "rapidfeedback/Index/" . $linkObject->get_id() . "/";
                  }
              }

              if ($object instanceof \steam_docextern) {
                  $urlNameHtml = "<div id='" . $id . "_1'><a href=\"" . $url . "new/" . "\" target=\"_blank\"> " . $name . "</a></div>" . "<script>" . $tipsy->getHtml() . "</script>";
                  $urlHtml = "<a href=\"" . $url . "new/" . "\" target=\"_blank\">";
              } else{
              $urlNameHtml = "<div id='" . $id . "_1'><a href=\"" . $url . "\"> " . $name . "</a></div>" . "<script>" . $tipsy->getHtml() . "</script>";
              $urlHtml = "<a href=\"" . $url . "\">";
            }
          }

          $popupMenu = new \Widgets\PopupMenu();
          $popupMenu->setData($object);
          $popupMenu->setElementId("gallery-overlay");
          $popupMenu->setCommand("GetPopupMenu");
          $popupMenuHtml = $popupMenu->getHtml();

          $tagList = "";
          foreach ($tags as $tag) {
              if ($tag !== "") {
                  $tagList.=$tag . " ";
              }
          }

          $colorProvider = new ColorProvider();
          $color = $colorProvider->getColor($object);

          $galleryNumber = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("GALLERY_NUMBER");
          $galleryNumberClass = "Row5";
          if(is_numeric($galleryNumber) && $galleryNumber > 0 && $galleryNumber < 11){
            $galleryNumberClass = "Row" . $galleryNumber;
          }

          $entry->setHtml("<li class='galleryEntry " . $color . " " . $hidden . " " . $galleryNumberClass . "' id='" . $id . "'>

                <input id='" . $id . "_checkbox' class='galleryEntryCheckbox' type='checkbox' onclick='event.stopPropagation(); if(this.checked){ jQuery(\"#" . $id . "\").addClass(\"selected\") } else { jQuery(\"#" . $id . "\").removeClass(\"selected\") }'>

                " . $linkIcon . "

                " . $urlHtml . "

                <svg class='galleryPicture'><use xlink:href='" . $iconSVG . "#" . $idSVG . "'/></svg>

                </a>

                <p style='display:none'>" . $tagList . "</p>

                " . $popupMenuHtml . "

                " . $urlNameHtml . "

            </li>");

            $ajaxResponseObject->addWidget($entry);
        }

        $TipsyStyle = \Widgets::getInstance()->readCSS("Tipsy.css");
        $PopupMenuStyle = \Widgets::getInstance()->readCSS("PopupMenu.css");

        $galleryEnd = new \Widgets\RawHtml();
        $galleryEnd->setHtml("</ul><div id='gallery-overlay'></div><script>$(document).trigger('galleryReady');</script><style>" . $TipsyStyle . $PopupMenuStyle . "</style>");

        $ajaxResponseObject->addWidget($galleryEnd);

        return $ajaxResponseObject;
    }

}

class ColorProvider implements \Widgets\IColorProvider {

    public function getColor($contentItem) {
        $color = $contentItem->get_attribute("OBJ_COLOR_LABEL");
        return ($color === 0) ? "" : $color;
    }

}

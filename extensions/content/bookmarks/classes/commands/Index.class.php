<?php

namespace Bookmarks\Commands;

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

        $currentUser = \lms_steam::get_current_user();
            
        if (isset($this->id)) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            if ($object instanceof \steam_exit) {
                $object = $object->get_exit();
                $this->id = $object->get_id();
            }
        } else {
            $object = $currentUser->get_attribute("USER_BOOKMARKROOM");
            $this->id = $object->get_id();
        }


        //chronic
        //\ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentObject($object);
        $title = getCleanName($object, 65);
        $chronicPath = "/bookmarks/index/" . $this->id . "/";
        \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentPath($chronicPath, $title);

        if ($object && $object instanceof \steam_container) {
            $objects = $object->get_inventory();
        } else {
            $objects = array();
        }

        //build breadcrumb
  			$icon = deriveIcon($object);
  			$iconSVG = str_replace("png", "svg", $icon);
  			$idSVG = str_replace(".svg", "", $iconSVG);
  			$iconSVG = PATH_URL . "explorer/asset/icons/mimetype/svg/" . $iconSVG;
  			$breadcrumbArray = array(array("name" => "<svg><use xlink:href='" . $iconSVG . "#" . $idSVG . "'/></svg><p>" . $title . "</p>"));
        /*
        $parent = $object->get_environment();
  			while($parent instanceof \steam_container){
  				$title = getCleanName($parent, 65);
  				$icon = deriveIcon($object);
  				$iconSVG = str_replace("png", "svg", $icon);
  				$idSVG = str_replace(".svg", "", $iconSVG);
  				$iconSVG = PATH_URL . "explorer/asset/icons/mimetype/svg/" . $iconSVG;
  				array_unshift($breadcrumbArray, array("name" => "<svg style='width:16px; height:16px; float:left; color:#3a6e9f;'><use xlink:href='" . $iconSVG . "#" . $idSVG . "'/></svg><p style=\"float:left; margin-top:0px; margin-left:5px; margin-right:5px;\">" . $title . "</p>", "link" => PATH_URL . "bookmarks/index/" . $parent->get_id() . "/"));
  				$parent = $parent->get_environment();
  			}
        */
        array_unshift($breadcrumbArray, "");
        $breadcrumb = new \Widgets\Breadcrumb();
        $breadcrumb->setData($breadcrumbArray);

        $this->getExtension()->addJS();
        $this->getExtension()->addCSS();

        $loader = new \Widgets\Loader();
        $loader->setWrapperId("bookmarksWrapper");
        $loader->setMessage("Lade Lesezeichen...");
        $loader->setNamespace("Bookmarks");
        $loader->setParams(array("id" => $this->id));
        $loader->setElementId("bookmarksWrapper");
        $loader->setType("updater");

        //$actionBar = new \Widgets\ActionBar();
        //$actionBar->setActions(array(array("name" => "Ordner anlegen", "ajax" => array("onclick" => array("command" => "newElement", "params" => array("id" => $this->id), "requestType" => "popup")))));
        //$actionBar->setActions(array(array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newelement"))), array("name"=>"Eigenschaften", "link"=>PATH_URL."explorer/properties/"), array("name"=>"Rechte", "link"=>PATH_URL."explorer/rights/")));

        //check the explorer view attribute which is specified in the profile
        $viewAttribute = $currentUser->get_attribute("EXPLORER_VIEW");
        if($viewAttribute && $viewAttribute == "gallery"){
          $loader->setCommand("LoadGalleryContent");
          $selectAll = new \Widgets\RawHtml();
          $selectAll->setHtml("<div id='selectAll'><input onchange='elements = jQuery(\".galleryEntry > input\"); for (i=0; i<elements.length; i++) { if (this.checked != elements[i].checked) { elements[i].click() }}' type='checkbox'><p>Alle auswählen</p></div>");
          $frameResponseObject->addWidget($selectAll);
          $script = "function initSort(){";
          foreach ($objects as $o) {
              if ($o instanceof \steam_link && $o->get_link_object() == 0) $o->delete(); //remove bookmarks whose target objects has been deleted
              if (getObjectType($o) !== "trashbin") {
                  $script .= "$('#" . $o->get_id() . "').attr('onclick', '');
                  $('#" . $o->get_id() . "').attr('onmouseover', '');
                  $('#" . $o->get_id() . "').attr('onmouseout', '');
                  $('#" . $o->get_id() . "_1').unbind('mouseenter mouseleave');    ";
              }
          }
          $assetUrl = \Explorer::getInstance()->getAssetUrl() . "images/sort_gallery.svg";
          $script .= '
              $("#sort-icon").attr("name", "true");
              $("#sort-icon").parent().bind("click", function(){$(this).css("background-color", "#ff8300")});
              var newIds = "";
              $("#bookmarksGallery").sortable();
              $("#bookmarksGallery").disableSelection();
              $("#bookmarksGallery").bind("sortupdate", function(event, ui){
                  var changedElement = $(ui.item).attr("id");
                  $("#bookmarksGallery").children().each(function(index, value){
                      if(index == $("#bookmarksGallery").children().length-1) newIds += value.id;
                      else newIds += value.id + ", ";
                    });
                  sendRequest("Sort", {"changedElement": changedElement, "id": $("#environment").attr("value"), "newIds":newIds }, "", "data", function(response){ }, function(response){ }, "explorer");
                  newIds = "";
              });
              $("#content").prepend("<div style=\"margin-left:335px; background-repeat:no-repeat; position:absolute;height:30px;width:300px;background-image:url(' . $assetUrl . ');\"></div>");

          }';
        }
        else{
          $loader->setCommand("LoadBookmarks");
          $script = "function initSort(){";
          foreach ($objects as $o) {
              if ($o instanceof \steam_link && $o->get_link_object() == 0) $o->delete(); //remove bookmarks whose target objects has been deleted
              if (getObjectType($o) !== "trashbin") {
                  $script .= "$('#" . $o->get_id() . "').attr('onclick', '');
                  $('#" . $o->get_id() . "').attr('onmouseover', '');
                  $('#" . $o->get_id() . "').attr('onmouseout', '');
                  $('#" . $o->get_id() . "_1').unbind('mouseenter mouseleave');    ";
              }
          }
          $assetUrl = \Explorer::getInstance()->getAssetUrl() . "images/sort_explorer.svg";
          $script .= '
              $("#sort-icon").attr("name", "true");
              $("#sort-icon").parent().bind("click", function(){$(this).css("background-color", "#ff8300")});
              var newIds = "";
              $( ".listviewer-items" ).sortable({zIndex: 1});
              $( ".listviewer-items" ).bind("sortupdate", function(event, ui){
                  var changedElement = $(ui.item).attr("id");
                  $(".listviewer-items").children();
                  $(".listviewer-items").children().each(function(index, value){
                      if(index == $(".listviewer-items").children().length-1)newIds +=value.id;
                      else newIds+=value.id + ", ";});
                      sendRequest("Sort", {"changedElement": changedElement, "id": $("#environment").attr("value"), "newIds":newIds }, "", "data", function(response){ }, function(response){ }, "explorer");
                      newIds = "";
              });
              $("#content").prepend("<div style=\"margin-left:335px; background-repeat:no-repeat; position:absolute;height:30px;width:300px;background-image:url(' . $assetUrl . ');\"></div>");

      }';
        }

        $environmentData = new \Widgets\RawHtml();
        $environmentData->setHtml("<input type=\"hidden\" id=\"environment\" value=\"$this->id\">");
        $environmentData->setJs($script);

        $frameResponseObject->setTitle("Lesezeichen");
        //$frameResponseObject->addWidget($actionBar);
        $frameResponseObject->addWidget($environmentData);
        $frameResponseObject->addWidget($breadcrumb);

        $frameResponseObject->addWidget($loader);
        return $frameResponseObject;
    }

}

?>

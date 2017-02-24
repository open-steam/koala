<?php

namespace Explorer\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;
    private $filter;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        if (isset($this->params[0])) {
            $intVal = intval($this->params[0]);
            if ($intVal !== 0) {
                $this->id = $intVal;
            } else {
                $this->id = "";
                if (strpos($this->params[0], "filter=") !== false) {
                    $this->filter = substr($this->params[0], 7);
                } else {
                    $this->filter = "";
                }
            }
        }
        if (isset($this->params[1])) {
            if (strpos($this->params[1], "filter=") !== false) {
                $this->filter = substr($this->params[1], 7);
            } else {
                $this->filter = "";
            }
        }
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {

        if (isset($this->id)) {

            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            if ($object instanceof \steam_exit) {

                $object = $object->get_exit();
                $this->id = $object->get_id();
            }
        } else {
            $currentUser = \lms_steam::get_current_user();

            $object = $currentUser->get_workroom();
            $this->id = $object->get_id();

            if (defined("DELETE_GROUP_HOME_EXITS") && DELETE_GROUP_HOME_EXITS && $object->get_attribute("DELETED_GROUP_HOME_EXITS") == "0") {
                $inventory = $object->get_inventory_filtered(array(
                    array('+', 'class', CLASS_EXIT),
                ));
                foreach ($inventory as $element) {
                    $exitElement = $element->get_exit();
                    if ($exitElement instanceof \steam_room && $exitElement->get_creator() instanceof \steam_group) {
                        $element->delete();
                    }
                }
                $object->set_attribute("DELETED_GROUP_HOME_EXITS", 1);
            }
        }

        if (!$object instanceof \steam_object) {
            \ExtensionMaster::getInstance()->send404Error();
        }

        //TODO: this is the wrong position for this exception
        //it should be placed after the big switch, but i'm not sure where
        //we have to think about it
        //
        //if the object is not a steam_container it cannot have any inventory.
        /*
          if (!$object instanceof \steam_container) {
          throw new \Exception("This object cannot contain any objects.", E_OBJECT_NO_INVENTORY);
          }
         */

        $objectModel = \AbstractObjectModel::getObjectModel($object);

        if ($object && $object instanceof \steam_container) {

            $count = $object->count_inventory();
            if ($count > 500) {

                throw new \Exception("Es befinden sich $count Objekte in diesem Ordner. Das Laden ist nicht möglich.");
            }
            try {

                $objects = $object->get_inventory();
            } catch (\NotFoundException $e) {
                \ExtensionMaster::getInstance()->send404Error();
            } catch (\AccessDeniedException $e) {
                $labelDenied = new \Widgets\RawHtml();
                $labelDenied->setHtml("Der Explorer kann nicht angezeigt werden, da Sie nicht über die erforderlichen Leserechte verfügen.");
                $frameResponseObject->addWidget($labelDenied);
                return $frameResponseObject;
                //throw new \Exception("", E_USER_ACCESS_DENIED);
            }
        } else {
            $objects = array();
        }

        $objectType = getObjectType($object);
        switch ($objectType) {
            case "document":
                header("location: " . PATH_URL . "explorer/ViewDocument/" . $this->id . "/");
                die;
                break;

            case "forum":
                header("location: " . PATH_URL . "forum/Index/" . $this->id . "/");
                die;
                break;

            case "referenceFolder":
                $exitObject = $object->get_exit();
                header("location: " . PATH_URL . "explorer/Index/" . $exitObject->get_id() . "/");
                die;
                break;

            case "referenceFile":
                $linkObject = $object->get_link_object();

                if (($linkObject === NULL) || !($linkObject instanceof \steam_object)) {

                    \ExtensionMaster::getInstance()->send404Error();
                }
                header("location: " . PATH_URL . "explorer/Index/" . $linkObject->get_id() . "/");
                die;
                break;

            case "user":
                header("location: " . PATH_URL . "user/Index/" . $object->get_name() . "/");
                die;
                break;

            case "group":
                \ExtensionMaster::getInstance()->send404Error();
                break;

            case "trashbin":
                \ExtensionMaster::getInstance()->send404Error();
                break;

            case "portal_old":
                $rawHtml = new \Widgets\RawHtml();
                //$rawHtml->setHtml("Dies ist ein \"altes\" Portal und kann nicht mehr angezeigt werden. Bitte umwandeln.");
                $frameResponseObject->addWidget($rawHtml);
                $frameResponseObject->setProblemDescription("Dies ist ein \"altes\" Portal und kann nicht mehr angezeigt werden.");
                $frameResponseObject->setProblemSolution("Bitte umwandeln.");

                return $frameResponseObject;
                break;

            case "gallery":
                header("location: " . PATH_URL . "gallery/Index/" . $this->id . "/");
                die;
                break;

            case "portal":
                header("location: " . PATH_URL . "portal/Index/" . $this->id . "/");
                die;
                break;

            case "portalColumn":
                \ExtensionMaster::getInstance()->send404Error();
                break;

            case "portalPortlet":
                \ExtensionMaster::getInstance()->send404Error();
                break;

            case "userHome":
                //ok
                break;

            case "groupWorkroom":
                //ok
                break;

            case "room":
                //ok
                break;

            case "container":
                //ok
                break;

            case "map":
                header("location: " . PATH_URL . "map/Index/" . $this->id . "/");
                die;
                break;

            case "unknown":
                \ExtensionMaster::getInstance()->send404Error();
                break;
        }

        //build breadcrumb
        $title = getCleanName($object, 65);
        $icon = deriveIcon($object);
        $iconSVG = str_replace("png", "svg", $icon);
        $idSVG = str_replace(".svg", "", $iconSVG);
        $iconSVG = PATH_URL . "explorer/asset/icons/mimetype/svg/" . $iconSVG;
        $breadcrumbArray = array(array("name" => "<svg style='width:16px; height:16px; float:left; color:#3a6e9f;'><use xlink:href='" . $iconSVG . "#" . $idSVG . "'/></svg><p style=\"float:left; margin-top:0px; margin-left:5px; margin-right:5px;\">" . $title . "</p>"));
        /*
          $parent = $object->get_environment();
          while($parent instanceof \steam_container){
          $title = getCleanName($parent, 65);
          $icon = deriveIcon($object);
          $iconSVG = str_replace("png", "svg", $icon);
          $idSVG = str_replace(".svg", "", $iconSVG);
          $iconSVG = PATH_URL . "explorer/asset/icons/mimetype/svg/" . $iconSVG;
          array_unshift($breadcrumbArray, array("name" => "<svg style='width:16px; height:16px; float:left; color:#3a6e9f;'><use xlink:href='" . $iconSVG . "#" . $idSVG . "'/></svg><p style=\"float:left; margin-top:0px; margin-left:5px; margin-right:5px;\">" . $title . "</p>", "link" => PATH_URL . "explorer/index/" . $parent->get_id() . "/"));
          $parent = $parent->get_environment();
          }
         */
        array_unshift($breadcrumbArray, "");
        $breadcrumb = new \Widgets\Breadcrumb();
        $breadcrumb->setData($breadcrumbArray);

        $this->getExtension()->addJS();
        $this->getExtension()->addCSS();

        //check sanctions
        $envWriteable = ($object->check_access_write(\lms_steam::get_current_user()));
        $envSanction = $object->check_access(SANCTION_SANCTION);

        //$actionBar = new \Widgets\ActionBar();
        /*
          $actionBar->setActions(array(!$envWriteable ?  : array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newElement", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
          array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
          array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup")))
          ));
          if ($envSanction) {
          $actionBar->setActions(
          array(
          array("name" => "Neu", "ajax" => array("onclick" => array("command" => "newElement", "params" => array("id" => $this->id), "requestType" => "popup"))),
          array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup"))),
          array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup")))));
          } elseif ($envWriteable) {
          $actionBar->setActions(
          array(
          array("name" => "Neu", "ajax" => array("onclick" => array("command" => "newElement", "params" => array("id" => $this->id), "requestType" => "popup"))),
          array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup")))));
          } else {
          $actionBar->setActions(
          array(
          array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup")))));
          }


          $actionBar->setActions(array(array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newelement"))), array("name"=>"Eigenschaften", "link"=>PATH_URL."explorer/properties/"), array("name"=>"Rechte", "link"=>PATH_URL."explorer/rights/")));
         */
        $presentation = $object->get_attribute("bid:presentation");
        $preHtml = "";
        if ($presentation === "head") {
            $objects = $object->get_inventory();
            if (count($objects) > 0) {
                $first = $objects[0];
                $mimetype = $first->get_attribute(DOC_MIME_TYPE);
                if ($mimetype == "image/png" || $mimetype == "image/jpeg" || $mimetype == "image/gif" || $mimetype == "image/svg+xml" || $mimetype == "image/bmp") {
                    // Image
                    $preHtml = "<div style=\"text-align:center\"><img style=\"max-width:100%\" src=\"" . PATH_URL . "Download/Document/" . $first->get_id() . "/\"></div>";
                } elseif ($mimetype == "text/html") {
                    $rawContent = $first->get_content();
                    //$preHtml = strip_tags($rawContent,"<h1><h2><h3><h4><h5><p><a><div><style><b><i><strong><img><hr><table><tr><th><td><ul><ol><li>");
                    //$preHtml = $rawContent;
                    $htmlDocument = new \HtmlDocument();

                    $preHtml = $htmlDocument->makeViewModifications($rawContent, $object, true);
                    $preHtml = cleanHTML($preHtml);
                } elseif (strstr($mimetype, "text")) {
                    $bidDokument = new \BidDocument($first);
                    $preHtml = $bidDokument->get_content();
                }
            }
        } elseif ($presentation === "index" && !(isset($_GET["view"]) && ($_GET["view"] === "list"))) {
            $objects = $object->get_inventory();
            if (count($objects) > 0) {
                $first = $objects[0];
                $url = \ExtensionMaster::getInstance()->getUrlForObjectId($first->get_id(), "view");
                header("location: {$url}");
                die;
            }
        }

        /*
          //make html output modifications
          $htmlDocument = new \HtmlDocument();
          $preHtml = $htmlDocument->makeViewModifications($preHtml);
          $preHtml = cleanHTML($preHtml);
         */

        if ($preHtml !== "") {
            $preHtml = "<div style=\"border-bottom: 1px solid #ccc; padding-bottom:10px; margin-bottom:10px; clear:both;\">{$preHtml}</div>";
        }

        $description = new \Widgets\RawHtml();
        if (isUserHome($object)) {
            $desc = "";
        } else {
            $desc = $object->get_attribute("OBJ_DESC");
        }
        $description->setHtml("<p style='float:left; color:#AAAAAA; clear:both; margin-top:0px'>" . $desc . "</p>");

        $inventory = $object->get_inventory();
        $keywordmatrix = array();
        foreach ($inventory as $inv) {
            $keywordmatrix[] = $inv->get_attribute("OBJ_KEYWORDS");
        }
        $kwList = array();
        foreach ($keywordmatrix as $kwRow) {
            foreach ($kwRow as $element) {
                $kwList[] = $element;
            }
        }

        $popupMenuSearch = new \Widgets\PopupMenu();
        $popupMenuSearch->setCommand("GetPopupMenuSearch");
        $popupMenuSearch->setNamespace("Explorer");
        $popupMenuSearch->setData($object);
        $popupMenuSearch->setElementId("search-area-popupmenu");

        if (defined("EXPLORER_TAGS_VISIBLE") && EXPLORER_TAGS_VISIBLE) {
            $searchField = new \Widgets\Search();
            $searchField->setId("searchfield");
            $searchField->setAutocomplete($kwList);
            $searchField->setPopupMenu($popupMenuSearch);
            $searchField->setValue($this->filter);
        }

        $frameResponseObject->setTitle($title);
        if (defined("EXPLORER_TAGS_VISIBLE") && EXPLORER_TAGS_VISIBLE) {
            $frameResponseObject->addWidget($searchField);
        }

        $environment = new \Widgets\RawHtml();
        $environment->setHtml("{$preHtml}<input type=\"hidden\" id=\"environment\" name=\"environment\" value=\"{$this->id}\">");
        $selectAll = new \Widgets\RawHtml();

        $loader = new \Widgets\Loader();
        $loader->setWrapperId("explorerWrapper");
        $loader->setMessage("Lade Objekte...");
        $loader->setParams(array("id" => $this->id));
        $loader->setElementId("explorerWrapper");
        $loader->setType("updater");

        //check the explorer view attribute which is specified in the profile
        $viewAttribute = \lms_steam::get_current_user()->get_attribute("EXPLORER_VIEW");
        if ($viewAttribute && $viewAttribute == "gallery") {
            $loader->setCommand("loadGalleryContent");
            $searchField->setGalleryView();
            $selectAll = new \Widgets\RawHtml();
            $selectAll->setHtml("<div id='selectAll' style='float:right; margin-right:20px;'><p style='float:left; margin-top:1px;'>Alle auswählen: </p><input onchange='elements = jQuery(\".galleryEntry > input\"); for (i=0; i<elements.length; i++) { if (this.checked != elements[i].checked) { elements[i].click() }}' type='checkbox'></div>");
            $script = "function initSort(){";
            foreach ($objects as $o) {
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
    					$("#explorerGallery").sortable();
    					$("#explorerGallery").disableSelection();
    					$("#explorerGallery").bind("sortupdate", function(event, ui){
    							var changedElement = $(ui.item).attr("id");
    							$("#explorerGallery").children().each(function(index, value){
    									if(index == $("#explorerGallery").children().length-1) newIds += value.id;
    									else newIds += value.id + ", ";
    								});
    							sendRequest("Sort", {"changedElement": changedElement, "id": $("#environment").attr("value"), "newIds":newIds }, "", "data", function(response){ }, function(response){ }, "explorer");
    							newIds = "";
    					});
    					$("#content").prepend("<div style=\"margin-left:335px; background-repeat:no-repeat; position:absolute;height:30px;width:300px;background-image:url(' . $assetUrl . ');\"></div>");
    	}';
        } else {
            $loader->setCommand("loadContent");
            $selectAll->setHtml("");
            $script = "function initSort(){";
            foreach ($objects as $o) {
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

        $sortHtml = new \Widgets\RawHtml();
        $sortHtml->setJs($script);
        $sortHtml->setPostJsCode('$($(".popupmenuanker")[0]).css("margin-top", "3px");');

        //$frameResponseObject->addWidget($actionBar);
        $frameResponseObject->addWidget($sortHtml);
        $frameResponseObject->addWidget($breadcrumb);
        $frameResponseObject->addWidget($description);
        $frameResponseObject->addWidget($environment);
        $frameResponseObject->addWidget($selectAll);
        $frameResponseObject->addWidget($loader);

        return $frameResponseObject;
    }

}

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
            $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
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
        
        //if the object is not a steam_container it cannot have any inventory.
        if (!$object instanceof \steam_container) {
            
            throw new \Exception("This object cannot contain any objects.");
        }
        

        $objectModel = \AbstractObjectModel::getObjectModel($object);

        if ($object && $object instanceof \steam_container) {
            
            $count = $object->count_inventory();
            if ($count > 500) {
            
                throw new \Exception("Es befinden sich $count Objekte in diesem Ordner. Das Laden ist nicht möglich.");
            }
            try{
                
            $objects = $object->get_inventory();
            
            }catch(NotFoundException $e) {\ExtensionMaster::getInstance()->send404Error();}
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
        $title = getCleanName($object, 65);

        $parent = $object->get_environment();
        if ($parent instanceof \steam_container) {
            //$parentLink = array("name"=>"nach oben", "link"=>PATH_URL . "explorer/Index/" . $parent->get_id() . "/");
            $parentLink = "";
        } else {
            $parentLink = "";
        }
        $breadcrumb = new \Widgets\Breadcrumb();
        $breadcrumb->setData(array($parentLink, array("name" => "<img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($object) . "\"></img> " . $title)));

        $this->getExtension()->addJS();
        $this->getExtension()->addCSS();

        //check sanctions
        $envWriteable = ($object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
        $envSanction = $object->check_access(SANCTION_SANCTION);

        $actionBar = new \Widgets\ActionBar();

        /* $actionBar->setActions(array(!$envWriteable ?  : array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newElement", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
          array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"properties", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),
          array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup")))
          )); */
        if ($envSanction) {
            $actionBar->setActions(array(array("name" => "Neu", "ajax" => array("onclick" => array("command" => "newElement", "params" => array("id" => $this->id), "requestType" => "popup"))),
                array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup"))),
                array("name" => "Rechte", "ajax" => array("onclick" => array("command" => "Sanctions", "params" => array("id" => $this->id), "requestType" => "popup")))
            ));
        } elseif ($envWriteable) {
            $actionBar->setActions(array(array("name" => "Neu", "ajax" => array("onclick" => array("command" => "newElement", "params" => array("id" => $this->id), "requestType" => "popup"))),
                array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup")))));
        } else {
            $actionBar->setActions(array(
                array("name" => "Eigenschaften", "ajax" => array("onclick" => array("command" => "properties", "params" => array("id" => $this->id), "requestType" => "popup")))));
        }

        //$actionBar->setActions(array(array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newelement"))), array("name"=>"Eigenschaften", "link"=>PATH_URL."explorer/properties/"), array("name"=>"Rechte", "link"=>PATH_URL."explorer/rights/")));

        $presentation = $object->get_attribute("bid:presentation");
        $preHtml = "";
        if ($presentation === "head") {
            $objects = $object->get_inventory();
            if (count($objects) > 0) {
                $first = $objects[0];
                $mimetype = $first->get_attribute(DOC_MIME_TYPE);
                if ($mimetype == "image/png" || $mimetype == "image/jpeg" || $mimetype == "image/gif") {
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
            $preHtml = "<div style=\"border-bottom: 1px solid #ccc; padding-bottom:10px; margin-bottom:10px\">{$preHtml}</div>";
        }

        $environment = new \Widgets\RawHtml();
        $environment->setHtml("{$preHtml}<input type=\"hidden\" id=\"environment\" name=\"environment\" value=\"{$this->id}\">");

        $loader = new \Widgets\Loader();
        $loader->setWrapperId("explorerWrapper");
        $loader->setMessage("Lade Dokumente ...");
        $loader->setCommand("loadContent");
        $loader->setParams(array("id" => $this->id));
        $loader->setElementId("explorerWrapper");
        $loader->setType("updater");

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml("<div id=\"explorerContent\">" . $breadcrumb->getHtml() . $environment->getHtml() . $loader->getHtml() . "</div>");

        $rawHtml->addWidget($breadcrumb);
        $rawHtml->addWidget($environment);
        $rawHtml->addWidget($loader);

        $script = "function initSort(){";
        foreach ($objects as $o) {
            if (getObjectType($o) !== "trashbin") {
                $script .= "$('#" . $o->get_id() . "').attr('onclick', '');
                $('#" . $o->get_id() . "').attr('onmouseover', '');
                $('#" . $o->get_id() . "').attr('onmouseout', '');
                $('#" . $o->get_id() . "_1').unbind('mouseenter mouseleave');    ";
            }
        }
        $assetUrl = \Explorer::getInstance()->getAssetUrl() . "images/sort.png";
        $script .= '
            $("#sort-icon").attr("name", "true");
            $("#sort-icon").parent().bind("click", function(){$(this).css("background-color", "#CCCCCC");
});
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
            $(".actionBar").prepend("<div style=\"margin-top:38px;position:absolute;height:177px;width:30px;float:left;background-image:url(' . $assetUrl . ');\"></div>"); 
                
                
                                    
    }';
        $rawHtml->setJs($script);
        $rawHtml->setPostJsCode('$($(".popupmenuanker")[0]).css("margin-top", "3px");');

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

        $frameResponseObject->addWidget($actionBar);
        $frameResponseObject->addWidget($rawHtml);

        return $frameResponseObject;
    }

}

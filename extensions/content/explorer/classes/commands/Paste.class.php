<?php

namespace Explorer\Commands;

class Paste extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $env;
    private $id;
    private $path;
    private $elements;
    private $clipboard;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->clipboard = \lms_steam::get_current_user_no_guest();
        if (!isset($this->params["env"])) {
            $this->path = strtolower($this->params["path"]);
            if ($this->path == "bookmarks/") {
                $this->env = $this->clipboard->get_attribute("USER_BOOKMARKROOM")->get_id();
            } else if (strpos($this->path, "bookmark") !== false || strpos($this->path, "photoalbum") !== false || strpos($this->path, "portal") !== false){
               $pathArray = explode("/", $this->path);
               if(is_numeric($pathArray[2])){
                   $this->env = $pathArray[2];
               }
            }
        } else {
            $this->env = $this->params["env"];
        }
        if (isset($this->params["id"])) {
            $this->id = $this->params["id"];
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            $environment = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->env);
            //$object->move($environment);
            $this->protectedInsert($object, $environment);
        } else {
            $this->elements = $this->clipboard->get_inventory();
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        if (!isset($this->id)) {
            $ajaxResponseObject->setStatus("ok");
            $jswrapper = new \Widgets\JSWrapper();
            $ids = "";
            $elements = "";
            foreach ($this->elements as $key => $element) {
                if (count($this->elements) > $key + 1) {
                    $ids .= "{\"id\":\"" . $element->get_id() . "\", \"env\":\"" . $this->env . "\"}, ";
                    $elements .= "\"\", ";
                } else {
                    $ids .= "{\"id\":\"" . $element->get_id() . "\", \"env\":\"" . $this->env . "\"}";
                    $elements .= "\"\"";
                }
            }
            $js = "sendMultiRequest('Paste', jQuery.parseJSON('[$ids]'), jQuery.parseJSON('[$elements]'), 'updater', null, null, 'explorer', 'Füge Objekt ein ...', 0, " . count($this->elements) . ");";
            $jswrapper->setJs($js);
            $ajaxResponseObject->addWidget($jswrapper);
            return $ajaxResponseObject;
        } else {
            $ajaxResponseObject->setStatus("ok");
            $clipboardModel = new \Explorer\Model\Clipboard($this->clipboard);
            $jswrapper = new \Widgets\JSWrapper();
            $js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';";
            if (count($this->clipboard->get_inventory()) == 0) {
                $js .= "window.location.reload();";
            }
            $jswrapper->setJs($js);
            $ajaxResponseObject->addWidget($jswrapper);
            return $ajaxResponseObject;
        }
    }


    /*
     * $steamObject = moving object
     * $steamEnvirument = destination
     *
     *
     * types
     *
     * container_portal_bid
     *
     * container_portalColumn_bid
     *
     * container_portlet_bid
     */
    private function protectedInsert($steamObject, $steamEnvironment){
        if((!$steamEnvironment instanceof \steam_object) || !($steamObject instanceof \steam_object)){
            return false;
        }

        $steamEnvironmentId = $steamEnvironment->get_id();

        //case bookmarks
        $bookmarksRoom = \lms_steam::get_current_user_no_guest()->get_attribute(USER_BOOKMARKROOM);
	      $bookmarksRoomId = $bookmarksRoom->get_id();

        if($bookmarksRoomId === $steamEnvironmentId){
            if($steamObject instanceof \steam_link){
                $steamObject->move($steamEnvironment);
                return TRUE;
            }
            return FALSE;
        }

        //case portal
        $envObjectType = getObjectType($steamEnvironment);

        if($envObjectType === "portal"){
          $portletType = $steamObject->get_attribute("bid:portlet");
          if(is_numeric($portletType)) return false;
          if($portletType == "msg" || $portletType == "appointment" || $portletType == "termplan" || $portletType == "topic" || $portletType == "headline" || $portletType == "poll" || $portletType == "media" || $portletType == "rss" || $portletType == "chronic" || $portletType == "userpicture" || $portletType == "folderlist" || $portletType == "subscription"){
            $portalObject = $steamEnvironment;
            //get first column
            $portalInventory = $portalObject->get_inventory();
            $firstColumn = $portalInventory[0];
            if(!($firstColumn->get_attribute("OBJ_TYPE") === "container_portalColumn_bid")){
                //no fist column found
                return false;
            }
            $steamObject->move($firstColumn);
            return true;
          } else {
            return false;
          }
        }

        $path = strtolower($_SERVER["REQUEST_URI"]);

        //case photoalbum
        if(strpos($path, "photoalbum") != false){
          if($steamObject instanceof \steam_document && substr($steamObject->get_attribute("DOC_MIME_TYPE"),0,5) === "image"){
            $steamObject->move($steamEnvironment);
            return true;
          }
          else{
            return false;
          }
        }

        //case explorer
        if(strpos($path, "explorer") != false && $steamObject instanceof \container_portlet_bid) {
          return false;
        }

        //case normal
        $steamObject->move($steamEnvironment);
        return true;
    }

}

?>

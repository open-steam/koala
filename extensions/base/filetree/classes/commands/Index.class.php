<?php
namespace FileTree\Commands;

class Index extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $openFolders = array();

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        if (strpos($this->params["dir"], "root") === 0) {
            $room = $currentUser->get_workroom();
            if (strlen($this->params["dir"]) > 4) {
                $currentContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), substr($this->params["dir"], 4));
                array_push($this->openFolders, $currentContainer->get_id());
                
                $root = $currentContainer;
                while (true) {
                    $currentContainer = $currentContainer->get_environment();
                    if ("0" == $currentContainer) break;
                    if (!($currentContainer instanceof \steam_object)) break;

                    //is Presentation, autoforward case
                    if ($currentContainer->get_attribute("bid:presentation") === "index") { 
                        $currentContainer = $currentContainer->get_environment();
                    }
                    if ("0" == $currentContainer) break;
                    if (!($currentContainer instanceof \steam_object)) break;

                    if (!$currentContainer->check_access_read()) {
                        break;
                    }
                    array_push($this->openFolders, $currentContainer->get_id());
                    $root = $currentContainer;
                }
                
                if (!in_array($room->get_id(), $this->openFolders)) {
                    $room = $root;
                }
            }
        } else {
            $room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["dir"]);
        }

        $html = "";
        if (($room->get_id() == $currentUser->get_workroom()->get_id()) || $room->get_id() != $this->params["dir"]) {
            $url = \ExtensionMaster::getInstance()->getUrlForObjectId($room->get_id(), "view");
            $html = "<a class=\"treeRoot\" href=\"" . $url . "\" rel=\"" . $room->get_id() . "/\"><img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($room) . "\"></img> " . getCleanName($room, -1) . "</a></li></ul>"; 
        }
        $html .= $this->getFolderHTML($room);
        $rawHtml = new \Widgets\RawHTML();
        $rawHtml->setHTML($html);
        $ajaxResponseObject->addWidget($rawHtml);
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

    private function getFolderHTML($containerObject) {
        $html = "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
        foreach ($containerObject->get_inventory() as $object) {
            if ($object->get_name() !== "trashbin" && !($object instanceof \steam_user) && !($object instanceof \steam_group)) {
                if ($object instanceof \steam_container && $object->get_attribute("OBJ_TYPE") == "0") {
                    // check if container contains more containers
                    $empty = true;
                    $inventory = $object->get_inventory_filtered(array(
                                        array('+', 'class', CLASS_ROOM),
                                        array('+', 'class', CLASS_CONTAINER),
                                    ));
                    foreach ($inventory as $inventoryItem) {
                        if ($inventoryItem->get_attribute("OBJ_TYPE") == "0") {
                            $empty = false;
                            break;
                        }
                    }
                    if ($empty) {
                        $css = "empty"; 
                    } else {
                        if (!in_array($object->get_id(), $this->openFolders) && $object->get_id() != $this->params["dir"]) {
                            $css = "collapsed";
                        } else {
                            $css = "expanded";
                        }
                    }
                    
                    $url = \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view");
                    $html .= "<li class=\"directory " . $css . "\"><a href=\"" . $url . "\" rel=\"" . $object->get_id() . "/\"><img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($object) . "\"></img> " . getCleanName($object, -1) . "</a>";
                    if (in_array($object->get_id(), $this->openFolders)) {
                        $html .= $this->getFolderHTML($object);
                    }
                    $html .= "</li>";
                }
            }
        }
        $html .= "</ul>";
        return $html;
    }
}
?>
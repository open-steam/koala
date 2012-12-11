<?php
namespace FileTree\Commands;

class Index extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $openFolders = array();
    private $highlight = 0;
    private $openRoot = 3;

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
            $bid = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), "/");
            if (strlen($this->params["dir"]) > 4) {
                $currentContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), substr($this->params["dir"], 4));
                $this->highlight = $currentContainer->get_id();
                array_push($this->openFolders, $currentContainer->get_id());

                $root = $currentContainer;
                while (true) {
                    if ($currentContainer->get_id() === $bid->get_id()) {
                        $this->openRoot = 1;
                    }
                    if ($currentContainer->get_id() === $room->get_id()) {
                        $this->openRoot = 2;
                    }
                    $currentContainer = $currentContainer->get_environment();
                    if ("0" == $currentContainer)
                        break;
                    if (!($currentContainer instanceof \steam_object))
                        break;

                    //is Presentation, autoforward case
                    if ($currentContainer->get_attribute("bid:presentation") === "index") {
                        $currentContainer = $currentContainer->get_environment();
                    }
                    if ("0" == $currentContainer)
                        break;
                    if (!($currentContainer instanceof \steam_object))
                        break;

                    if (!$currentContainer->check_access_read()) {
                        break;
                    }
                    
                    if ($this->isHiddenItem($currentContainer, $currentUser)) {
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

        if ($room->get_id() === $currentUser->get_workroom()->get_id()) {
            $this->openRoot = 2;
        }

        if ($room->get_id() != $this->params["dir"]) {
            $html = $this->getThreeRootHTML($currentUser, $room);
        } else {
            $html = $this->getFolderHTML($currentUser, $room);
        }
        $rawHtml = new \Widgets\RawHTML();
        $rawHtml->setHTML($html);
        $ajaxResponseObject->addWidget($rawHtml);
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

    private function getOneRootHTML($currentUser, $containerObject, $room, $root) {
        $html = "";
        if ($this->highlight == $room->get_id()) {
            $cssHighlight = "highlighted";
        } else {
            $cssHighlight = "";
        }
        $empty = true;
        if ($room->check_access_read()) {
            foreach ($room->get_inventory_filtered(array(array('+', 'class', CLASS_ROOM))) as $inventoryItem) {
                if ($inventoryItem->check_access_read() && (getObjectType($inventoryItem) === "room") && !$this->isHiddenItem($inventoryItem, $currentUser)) {
                    if (!($root === 1 && ($inventoryItem->get_name() === "home" || $inventoryItem->get_name() === "scripts"))) {
                        $empty = false;
                        break;
                    }
                }
            }
        }
        if ($empty) {
            $css = "empty";
        } else {
            if (!in_array($room->get_id(), $this->openFolders) && $room->get_id() != $this->params["dir"] && $this->openRoot !== $root) {
                $css = "collapsed";
            } else {
                $css = "expanded";
            }
        }
        $url = \ExtensionMaster::getInstance()->getUrlForObjectId($room->get_id(), "view");
        $html .= "<li class=\"directory " . $css . "\"><a href=\"" . $url . "\" rel=\"" . $room->get_id() . "/\" class=\"" . $cssHighlight . "\"><img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($room) . "\"></img> " . getCleanName($room, -1) . "</a>";
        if ($this->openRoot === $root) {
            $html .= $this->getFolderHtml($currentUser, $containerObject);
        }
        $html .= "</li>";
        return $html;
    }
    
    private function getThreeRootHTML($currentUser, $containerObject) {
        $bid = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), "/");
        $html = "<ul class=\"jqueryFileTree\" style=\"display: none;\">";

        $html .= $this->getOneRootHTML($currentUser, $containerObject, $bid, 1);
        $html .= $this->getOneRootHTML($currentUser, $containerObject, $currentUser->get_workroom(), 2);
        if ($this->openRoot === 3) {
            $html .= $this->getOneRootHTML($currentUser, $containerObject, $containerObject, 3);
        }

        $html .= "</ul>";
        return $html;
    }

    private function getFolderHTML($currentUser, $containerObject) {
        $bid = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), "/");
        $html = "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
        if ($containerObject->check_access_read() && $containerObject instanceof \steam_container) {
            foreach ($containerObject->get_inventory_filtered(array(array('+', 'class', CLASS_ROOM))) as $object) {
                if (getObjectType($object) === "room" && !$this->isHiddenItem($object, $currentUser) && !($containerObject->get_id() === $bid->get_id() && ($object->get_name() === "home" || $object->get_name() === "scripts"))) {
                    // check if container contains more containers
                    $empty = true;
                    if ($object->check_access_read()) {
                        foreach ($object->get_inventory_filtered(array(array('+', 'class', CLASS_ROOM))) as $inventoryItem) {
                            if ($inventoryItem->check_access_read() && (getObjectType($inventoryItem) === "room") && !$this->isHiddenItem($inventoryItem, $currentUser)) {
                                $empty = false;
                                break;
                            }
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
                    if ($this->highlight == $object->get_id()) {
                        $cssHighlight = "highlighted";
                    } else {
                        $cssHighlight = "";
                    }

                    $url = \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view");
                    $html .= "<li class=\"directory " . $css . "\"><a href=\"" . $url . "\" rel=\"" . $object->get_id() . "/\" class=\"" . $cssHighlight . "\"><img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($object) . "\"></img> " . getCleanName($object, -1) . "</a>";
                    if (in_array($object->get_id(), $this->openFolders)) {
                        $html .= $this->getFolderHTML($currentUser, $object);
                    }
                    $html .= "</li>";
                }
            }
        }
        $html .= "</ul>";
        return $html;
    }

    private function isHiddenItem($steamObject, $userObject) {
        //other
        $userHiddenAttribute = $userObject->get_attribute("EXPLORER_SHOW_HIDDEN_DOCUMENTS");
        $userShowHiddenObjects = false;
        if ($userHiddenAttribute === "TRUE")
            $userShowHiddenObjects = true;
        if ($userHiddenAttribute === "FALSE")
            $userShowHiddenObjects = false;
        if ($userShowHiddenObjects)
            return false;

        //hidden item
        $steamObjectHiddenAttribute = $steamObject->get_attribute("bid:hidden");
        if ($steamObjectHiddenAttribute === "1") {
            return true;
        }

        return false;
    }
}
?>
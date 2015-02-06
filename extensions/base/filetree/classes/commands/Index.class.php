<?php
namespace FileTree\Commands;

class Index extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $openFolders = array();
    private $highlight = 0;
    private $openRoot = 3;
    
    /* openroot explanation
     * 
     * openroot is global variable
     * it is set in ajaxRespone
     * and used in getOneRootHTML
     * 
     * openroot=1 if $room is "/" (or the server root/root portal) 
     * openroot=2 if $room is the personal workroom of the user
     * openroot=3 initial value, a room is given by parameter, no path up to server root or user workroom
     * 
     * filetree shows up to three roots in the tree view
     * the first root is the server root "/"
     * the second root is the user workroom/user home
     * the third root is shown, if there is no path up to the 1.(server) or 2.(user) root 
     * 
     */
    

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
            $bidServerRoot = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), "/");
            if (strlen($this->params["dir"]) > 4) {
                $currentContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), substr($this->params["dir"], 4));
                $this->highlight = $currentContainer->get_id();
                array_push($this->openFolders, $currentContainer->get_id());

                $root = $currentContainer;
                while (true) {
                    if ($currentContainer->get_id() === $bidServerRoot->get_id()) {
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
        
        //here is the problem line 65
        if(($room instanceof \steam_object) && ($currentUser instanceof \steam_user)){
            if ($room->get_id() === $currentUser->get_workroom()->get_id()) { //here is the problem
                $this->openRoot = 2;
            }
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

    
    /*
     * getOneRootHTML
     */
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
    
    
    /*
     * getThreeRootHTML
     * returns data for three root nodes in the file tree
     * getOneRootHtml is called three times
     */
    private function getThreeRootHTML($currentUser, $containerObject) {
        $bidServerRoot = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), "/");
        $html = "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
        
        //show server root
        $html .= $this->getOneRootHTML($currentUser, $containerObject, $bidServerRoot, 1);
        
        //show user root only if user is loggt in
        if ($currentUser instanceof \steam_user){
            $html .= $this->getOneRootHTML($currentUser, $containerObject, $currentUser->get_workroom(), 2);
        }
        
        if ($this->openRoot === 3) {
            $html .= $this->getOneRootHTML($currentUser, $containerObject, $containerObject, 3);
        }

        $html .= "</ul>";
        return $html;
    }

    
    /*
     * getFolderHTML
     */
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

    
    /*
     * isHiddenItem returns true if an object is marked hidden
     */
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
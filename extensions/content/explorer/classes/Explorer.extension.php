<?php

class Explorer extends AbstractExtension implements IIconBarExtension {

    public function getName() {
        return "Explorer";
    }

    public function getDesciption() {
        return "Extension for explorer view.";
    }

    public function getVersion() {
        return "v1.0.0";
    }

    public function getAuthors() {
        $result = array();
        $result[] = new Person("Dominik", "Niehus", "nicke@uni-paderborn.de");
        return $result;
    }

    public function getPriority() {
        return -20;
    }

    public function getIconBarEntries() {
        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $trashbin = $currentUser->get_attribute(USER_TRASHBIN);
        $trashbinModel = new \Explorer\Model\Trashbin($trashbin);
        $trashbinCount = count($trashbin->get_inventory());
        
        $showTrashbin = $trashbinCount == 0 ? FALSE : TRUE;
        $clipboardModel = new \Explorer\Model\Clipboard($currentUser);
        $clipboardCount = count($currentUser->get_inventory());
        $showClipboard = $clipboardCount == 0 ? FALSE : TRUE;
        $array = array();
        $path = strtolower($_SERVER["REQUEST_URI"]);
        
        if ($path == "/explorer/") {
            $array[] = array("name" => "<img id=\"sort-icon\" name=\"false\"  
                onclick=\"if(name == 'false'){initSort();}else{window.location.reload();}\" 
                title=\"Sortieren\" src=\"" . \Portal::getInstance()->getAssetUrl() .
                "icons/portal_sort_white.png\">");
            $array[] = array("name" => "<img name=\"false\" title=\"Navigationsbaum\" 
                src=\"" . \FileTree::getInstance()->getAssetUrl() . "icons/tree_white.png\">",
                "onclick" => "openFileTree()");
        } else if (strpos($path, "/explorer/index/") !== false) {
            $array[] = array("name" => "<img id=\"sort-icon\" name=\"false\"  
                onclick=\"if(name == 'false'){initSort();}else{window.location.reload();}\" 
                title=\"Sortieren\" src=\"" . \Portal::getInstance()->getAssetUrl() .
                "icons/portal_sort_white.png\">");
            $array[] = array("name" => "<img name=\"false\" title=\"Navigationsbaum\" 
                src=\"" . \FileTree::getInstance()->getAssetUrl() . "icons/tree_white.png\">",
                "onclick" => "openFileTree()");
        } else if ($path == "/bookmarks/") {
            $array[] = array("name" => "<img id=\"sort-icon\" name=\"false\"  
                onclick=\"if(name == 'false'){initSort();}else{window.location.reload();}\" 
                title=\"Sortieren\" src=\"" . \Portal::getInstance()->getAssetUrl() .
                "icons/portal_sort_white.png\">");
        } else if (strpos($path, "/bookmarks/index/") !== false) {
            $array[] = array("name" => "<img id=\"sort-icon\" name=\"false\"  
                onclick=\"if(name == 'false'){initSort();}else{window.location.reload();}\" 
                title=\"Sortieren\" src=\"" . \Portal::getInstance()->getAssetUrl() .
                "icons/portal_sort_white.png\">");
        }

        if ($showClipboard) {
            $array[] = array("name" => "<div id=\"clipboardIconbarWrapper\">" . $clipboardModel->getIconbarHtml() . "</div>",
                "menu" => array(
                    array("name" => "Zwischenablage öffnen", "link" => "/clipboard/"),
                    array("name" => "Objekte hier einfügen", "onclick" => "event.stopPropagation();sendRequest('Paste', {'env':jQuery('#environment').attr('value')}, '', 'popup', null, null, 'explorer');"),
                    array("name" => "Zwischenablage leeren", "onclick" => "event.stopPropagation();sendRequest('EmptyClipboard', {}, '', 'popup', null, null, 'explorer');")));
        } else {
            $array[] = array("name" => "<div id=\"clipboardIconbarWrapper\">" . $clipboardModel->getIconbarHtml() . "</div>",
                "menu" => array(
                    array("name" => "Zwischenablage öffnen", "link" => "/clipboard/")));
        }
        
        if ($showTrashbin) {
            $array[] = array("name" => "<div id=\"trashbinIconbarWrapper\">" . $trashbinModel->getIconbarHtml() . "</div>",
                "menu" => array(
                    array("name" => "Papierkorb öffnen", "link" => "/trashbin/"),
                    array("name" => "Papierkorb leeren", "onclick" => "event.stopPropagation();sendRequest('EmptyTrashbin', {}, '', 'popup', null, null, 'explorer');")
                    ));
        } else {
            $array[] = array("name" => "<div id=\"trashbinIconbarWrapper\">" . $trashbinModel->getIconbarHtml() . "</div>",
                "menu" => array(
                    array("name" => "Papierkorb öffnen", "link" => "/trashbin/")));
        }
        return $array;
    }

    public function getCurrentObject(UrlRequestObject $urlRequestObject) {
        $params = $urlRequestObject->getParams();
        $id = $params[0];
        if (isset($id)) {
            if (!isset($GLOBALS["STEAM"])) {
                return null;
            }
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
            if (!($object instanceof steam_object)) {
                return null;
            }
            $type = getObjectType($object);
            if (array_search($type, array("referenceFolder", "container", "userHome", "groupWorkroom", "room", "document")) !== false) {
                return $object;
            }
        } else {
            $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
            $object = $currentUser->get_workroom();
            return $object;
        }
        return null;
    }

}

?>
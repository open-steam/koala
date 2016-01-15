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

        $pathShort = rtrim($path, "/");
        $arr = explode('/', $pathShort);
        $id = $arr[count($arr)-1];
        /*
        $pos = strpos($path, "explorer");
        if ($pos !== false) {
            //check sanctions
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
            $envWriteable = ($object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
            $envSanction = $object->check_access(SANCTION_SANCTION);
        }
         * */

        if ($path == "/explorer/") {
            $array[] = array("name" => "<img id=\"sort-icon\" name=\"false\" onclick=\"if(name == 'false'){initSort();}else{window.location.reload();}\" title=\"Sortieren\" src=\"" . \Portal::getInstance()->getAssetUrl() . "icons/portal_sort_white.png\">");
            $array[] = array("name" => "<img name=\"false\" title=\"Navigationsbaum\" src=\"" . \FileTree::getInstance()->getAssetUrl() . "icons/tree_white.png\">","onclick" => "openFileTree()");

            $object = $currentUser->get_workroom();
            $envWriteable = ($object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
            $envSanction = $object->check_access(SANCTION_SANCTION);

            if ($envSanction) {
                    $array[] = array("name" => "<img title=\"Neu\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/newElement_white.png\">", "onclick"=>"sendRequest('newElement', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                    $array[] = array("name" => "<img title=\"Rechte\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights_white.png\">", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
           } elseif ($envWriteable) {
                    $array[] = array("name" => "<img title=\"Neu\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/newElement_white.png\">", "onclick"=>"sendRequest('newElement', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            } else {
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            }

        } else if (strpos($path, "/explorer/index/") !== false) {
            $array[] = array("name" => "<img id=\"sort-icon\" name=\"false\" onclick=\"if(name == 'false'){initSort();}else{window.location.reload();}\" title=\"Sortieren\" src=\"" . \Portal::getInstance()->getAssetUrl() . "icons/portal_sort_white.png\">");
            $array[] = array("name" => "<img name=\"false\" title=\"Navigationsbaum\" src=\"" . \FileTree::getInstance()->getAssetUrl() . "icons/tree_white.png\">","onclick" => "openFileTree()");

            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
            $envWriteable = ($object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
            $envSanction = $object->check_access(SANCTION_SANCTION);

            if ($envSanction) {
                    $array[] = array("name" => "<img title=\"Neu\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/newElement_white.png\">", "onclick"=>"sendRequest('newElement', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                    $array[] = array("name" => "<img title=\"Rechte\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights_white.png\">", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            } elseif ($envWriteable) {
                    $array[] = array("name" => "<img title=\"Neu\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/newElement_white.png\">", "onclick"=>"sendRequest('newElement', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            } else {
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            }

        } else if (strpos($path, "/explorer/viewdocument/") !== false) {
            $array[] = array("name" => "<img name=\"false\" title=\"Navigationsbaum\" src=\"" . \FileTree::getInstance()->getAssetUrl() . "icons/tree_white.png\">","onclick" => "openFileTree()");

            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
            $mimetype = $object->get_attribute(DOC_MIME_TYPE);
            $objName = $object->get_name();
            $envSanction = $object->check_access(SANCTION_SANCTION);
            if ($mimetype != "text/html") {
              $array[] = array("name" => "<img title=\"Herunterladen\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/download_white.png\">","onclick" => "window.open('" . PATH_URL . "Download/Document/" . $id . "/" . $objName . "')");
            }
            if ($mimetype == "text/html") {
              $array[] = array("name" => "<img title=\"Bearbeiten\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/edit_white.gif\">", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "Explorer/EditDocument/" . $id . "/'");
              $array[] = array("name" => "<img title=\"Quelltext bearbeiten\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/edit_html_white.png\">", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "Explorer/CodeEditDocument/" . $id . "/'");
            }
            else if (strstr($mimetype, "text")) {
              $array[] = array("name" => "<img title=\"Bearbeiten\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/edit_white.gif\">", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "Explorer/EditDocument/" . $id . "/'");
            }
            if ($envSanction) {
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                    $array[] = array("name" => "<img title=\"Rechte\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights_white.png\">", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            } else {
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            }

        } else if (strpos($path, "/explorer/editdocument/") !== false) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
            $envSanction = $object->check_access(SANCTION_SANCTION);

            if ($envSanction) {
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                    $array[] = array("name" => "<img title=\"Rechte\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights_white.png\">", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            } else {
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            }
        } else if (strpos($path, "/explorer/codeeditdocument/") !== false) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
            $envSanction = $object->check_access(SANCTION_SANCTION);

            if ($envSanction) {
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                    $array[] = array("name" => "<img title=\"Rechte\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights_white.png\">", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            } else {
                    $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            }
        } else if ($path == "/bookmarks/") {
            $object = $currentUser->get_attribute("USER_BOOKMARKROOM");
            $array[] = array("name" => "<img id=\"sort-icon\" name=\"false\" onclick=\"if(name == 'false'){initSort();}else{window.location.reload();}\" title=\"Sortieren\" src=\"" . \Portal::getInstance()->getAssetUrl() . "icons/portal_sort_white.png\">");
            $array[] = array("name" => "<img title=\"Ordner anlegen\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/newFolder_white.png\">", "onclick"=>"sendRequest('newElement', {'id':{$object->get_id()}}, '', 'popup', null, null, 'Bookmarks');return false;");
        } else if (strpos($path, "/bookmarks/index/") !== false) {
            $array[] = array("name" => "<img id=\"sort-icon\" name=\"false\" onclick=\"if(name == 'false'){initSort();}else{window.location.reload();}\" title=\"Sortieren\" src=\"" . \Portal::getInstance()->getAssetUrl() . "icons/portal_sort_white.png\">");
            $array[] = array("name" => "<img title=\"Ordner anlegen\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/newFolder_white.png\">", "onclick"=>"sendRequest('newElement', {'id':{$id}}, '', 'popup', null, null, 'Bookmarks');return false;");
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
                    array("name" => "Papierkorb leeren", "onclick" => "event.stopPropagation();sendRequest('EmptyTrashbin', {}, '', 'popup', null, null, 'explorer');")));
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
            if(!$currentUser instanceof \steam_user) throw new Exception ("The current user cannot be determined. The variable is of the type ".gettype($currentUser).". The class is ".get_class($currentUser));
            $object = $currentUser->get_workroom();
            return $object;
        }
        return null;
    }

}

?>

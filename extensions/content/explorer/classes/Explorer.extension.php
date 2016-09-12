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
        $TrashbinNotEmpty = $trashbinCount == 0 ? FALSE : TRUE;

        $clipboardModel = new \Explorer\Model\Clipboard($currentUser);
        $clipboardInventory = $currentUser->get_inventory();
        $clipboardCount = count($clipboardInventory);
        $ClipboardNotEmpty = $clipboardCount == 0 ? FALSE : TRUE;

        $array = array();
        $path = strtolower($_SERVER["REQUEST_URI"]);
        $pathArray = explode("/", $path);
        $currentObjectID = "";
        for ($count = 0; $count < count($pathArray); $count++) {
            if (intval($pathArray[$count]) !== 0) {
                $currentObjectID = $pathArray[$count];
                break;
            }
        }
        if ($currentObjectID === "403" || $currentObjectID === "404") {
            $currentObjectID = "";
        }
        if ($path == "/explorer/" || $path == "/explorer/galleryview/") {
            $array[] = array("name" => "<div id='sort-icon' title='Sortieren' name='false' onclick='if($(this).attr(\"name\") == \"false\"){initSort();}else{window.location.reload();}'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/sort.svg#sort'/></svg></div>");

            $object = $currentUser->get_workroom();
            $envWriteable = ($object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
            $envSanction = $object->check_access(SANCTION_SANCTION);

            /*
            if($path == "/explorer/"){
              $array[] = array("name" => "<img title=\"Galerieansicht\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/gallery.png\">", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "explorer/galleryview/'");
            } else{
              $array[] = array("name" => "<img title=\"Listenansicht\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/explorer_white.png\">", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "explorer/'");
            }
            */

            if ($envSanction) {
              $array[] = array("name" => "<div title='Neues Objekt'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/newElement.svg#newElement'/></svg></div>", "onclick"=>"sendRequest('newElement', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
              $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
              $array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
           } elseif ($envWriteable) {
             $array[] = array("name" => "<div title='Neues Objekt'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/newElement.svg#newElement'/></svg></div>", "onclick"=>"sendRequest('newElement', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
             $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            } else {
              $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            }

            $array[] = array("name" => "SEPARATOR");
            $array[] = array("name" => "<div title='Navigationsbaum'><svg><use xlink:href='" . \FileTree::getInstance()->getAssetUrl() . "icons/tree.svg#tree'/></svg></div>", "onclick" => "openFileTree()");

        } else if (strpos($path, "/explorer/index/") !== false || strpos($path, "/explorer/galleryview/") !== false) {
            $array[] = array("name" => "<div id='sort-icon' title='Sortieren' name='false' onclick='if($(this).attr(\"name\") == \"false\"){initSort();}else{window.location.reload();}'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/sort.svg#sort'/></svg></div>");

            if($currentObjectID != ""){
              $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
              $envWriteable = ($object->check_access_write($GLOBALS["STEAM"]->get_current_steam_user()));
              $envSanction = $object->check_access(SANCTION_SANCTION);

              /*
              if(strpos($path, "/explorer/index/") !== false){
                $array[] = array("name" => "<img title=\"Galerieansicht\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/gallery.png\">", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "explorer/galleryview/" . $currentObjectID . "/'");
              } else{
                $array[] = array("name" => "<img title=\"Listenansicht\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/explorer_white.png\">", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "explorer/index/" . $currentObjectID . "/'");
              }
              */

              if ($envSanction) {
                $array[] = array("name" => "<div title='Neues Objekt'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/newElement.svg#newElement'/></svg></div>", "onclick"=>"sendRequest('newElement', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                $array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
             } elseif ($envWriteable) {
               $array[] = array("name" => "<div title='Neues Objekt'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/newElement.svg#newElement'/></svg></div>", "onclick"=>"sendRequest('newElement', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
               $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
              } else {
                $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
              }
            }
            $array[] = array("name" => "SEPARATOR");
            $array[] = array("name" => "<div title='Navigationsbaum'><svg><use xlink:href='" . \FileTree::getInstance()->getAssetUrl() . "icons/tree.svg#tree'/></svg></div>", "onclick" => "openFileTree()");
        } else if (strpos($path, "/explorer/viewdocument/") !== false) {

            if($currentObjectID != ""){
              $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
              $env = $object->get_environment();
              //$array[] = array("name" => "<img title=\"Aufwärts\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/arrow_up_white.png\">", "onclick"=>"location.href='" . PATH_URL . "explorer/index/{$env->get_id()}/'");
              $mimetype = $object->get_attribute(DOC_MIME_TYPE);
              $objName = $object->get_name();
              $envSanction = $object->check_access(SANCTION_SANCTION);
              if ($mimetype != "text/html") {
                $array[] = array("name" => "<div title='Herunterladen'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/download.svg#download'/></svg></div>","onclick" => "window.open('" . PATH_URL . "Download/Document/" . $currentObjectID . "/" . $objName . "')");
              }
              if ($mimetype == "text/html") {
                $array[] = array("name" => "<div title='Bearbeiten'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/edit.svg#edit'/></svg></div>", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "Explorer/EditDocument/" . $currentObjectID . "/'");
                $array[] = array("name" => "<div title='Quelltext bearbeiten'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/text_html.svg#text_html'/></svg></div>", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "Explorer/CodeEditDocument/" . $currentObjectID . "/'");
              }
              else if (strstr($mimetype, "text")) {
                $array[] = array("name" => "<div title='Bearbeiten'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/edit.svg#edit'/></svg></div>", "link"=>"", "onclick"=> "window.location.href = '" . PATH_URL . "Explorer/EditDocument/" . $currentObjectID . "/'");
              }
              if ($envSanction) {
                $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
                $array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
              } else {
                $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
              }
            }
            $array[] = array("name" => "SEPARATOR");
            $array[] = array("name" => "<div title='Navigationsbaum'><svg><use xlink:href='" . \FileTree::getInstance()->getAssetUrl() . "icons/tree.svg#tree'/></svg></div>", "onclick" => "openFileTree()");

        } else if (strpos($path, "/explorer/editdocument/") !== false) {
          if($currentObjectID != ""){
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
            $env = $object->get_environment();
            //$array[] = array("name" => "<img title=\"Aufwärts\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/arrow_up_white.png\">", "onclick"=>"location.href='" . PATH_URL . "explorer/index/{$env->get_id()}/'");
            $envSanction = $object->check_access(SANCTION_SANCTION);

            if ($envSanction) {
              $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
              $array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            } else {
              $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            }
          }
          $array[] = array("name" => "SEPARATOR");
          $array[] = array("name" => "<div title='Navigationsbaum'><svg><use xlink:href='" . \FileTree::getInstance()->getAssetUrl() . "icons/tree.svg#tree'/></svg></div>", "onclick" => "openFileTree()");
        } else if (strpos($path, "/explorer/codeeditdocument/") !== false) {
          if($currentObjectID != ""){
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
            $env = $object->get_environment();
            //$array[] = array("name" => "<img title=\"Aufwärts\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/arrow_up_white.png\">", "onclick"=>"location.href='" . PATH_URL . "explorer/index/{$env->get_id()}/'");
            $envSanction = $object->check_access(SANCTION_SANCTION);

            if ($envSanction) {
              $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
              $array[] = array("name" => "<div title='Rechte'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/rights.svg#rights'/></svg></div>", "onclick"=>"sendRequest('Sanctions', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            } else {
              $array[] = array("name" => "<div title='Eigenschaften'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/properties.svg#properties'/></svg></div>", "onclick"=>"sendRequest('Properties', {'id':{$object->get_id()}}, '', 'popup', null, null, 'explorer');return false;");
            }
          }
          $array[] = array("name" => "SEPARATOR");
          $array[] = array("name" => "<div title='Navigationsbaum'><svg><use xlink:href='" . \FileTree::getInstance()->getAssetUrl() . "icons/tree.svg#tree'/></svg></div>", "onclick" => "openFileTree()");
        } else if ($path == "/bookmarks/") {
            $object = $currentUser->get_attribute("USER_BOOKMARKROOM");
            $array[] = array("name" => "<div id='sort-icon' title='Sortieren' name='false' onclick='if($(this).attr(\"name\") == \"false\"){initSort();}else{window.location.reload();}'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/sort.svg#sort'/></svg></div>");
            $array[] = array("name" => "<div title='Ordner anlegen'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/folder.svg#folder'/></svg></div>", "onclick"=>"sendRequest('newElement', {'id':{$object->get_id()}}, '', 'popup', null, null, 'Bookmarks');return false;");

            $array[] = array("name" => "SEPARATOR");
            $array[] = array("name" => "<div title='Navigationsbaum'><svg><use xlink:href='" . \FileTree::getInstance()->getAssetUrl() . "icons/tree.svg#tree'/></svg></div>", "onclick" => "openFileTree()");

        } else if (strpos($path, "/bookmarks/index/") !== false || strpos($path, "/bookmarks/galleryview/") !== false) {
            $array[] = array("name" => "<div id='sort-icon' title='Sortieren' name='false' onclick='if($(this).attr(\"name\") == \"false\"){initSort();}else{window.location.reload();}'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/sort.svg#sort'/></svg></div>");
            if($currentObjectID != ""){
              $array[] = array("name" => "<div title='Ordner anlegen'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/mimetype/svg/folder.svg#folder'/></svg></div>", "onclick"=>"sendRequest('newElement', {'id':{$currentObjectID}}, '', 'popup', null, null, 'Bookmarks');return false;");
            }
            $array[] = array("name" => "SEPARATOR");
            $array[] = array("name" => "<div title='Navigationsbaum'><svg><use xlink:href='" . \FileTree::getInstance()->getAssetUrl() . "icons/tree.svg#tree'/></svg></div>", "onclick" => "openFileTree()");
        }
        if ($ClipboardNotEmpty) {
          if($path != "/clipboard/"){
            $paste = $this->checkClipboardInventory($clipboardInventory);
            $array[] = array("name" => "<div id=\"clipboardIconbarWrapper\">" . $clipboardModel->getIconbarHtml() . "</div>",
                "menu" => array(
                    array("name" => "<svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/clipboard.svg#clipboard'/></svg> Zwischenablage öffnen", "link" => "/clipboard/"),
                    ($paste) ? array("name" => "<svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/paste.svg#paste'/></svg> Objekte hier einfügen", "onclick" => "event.stopPropagation();sendRequest('Paste', {'env':jQuery('#environment').attr('value')}, '', 'popup', null, null, 'explorer')") : "",
                    array("name" => "<svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/empty_clipboard.svg#empty_clipboard'/></svg> Zwischenablage leeren", "onclick" => "event.stopPropagation();sendRequest('EmptyClipboard', {}, '', 'popup', null, null, 'explorer');")));
          }
          else{
            $array[] = array("name" => "<div title='Zwischenablage leeren'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/empty_clipboard.svg#empty_clipboard'/></svg></div>", "onclick"=>"sendRequest('EmptyClipboard', {}, '', 'popup', null, null, 'explorer');return false;");
          }
        } else {
          if($path != "/clipboard/"){
            $array[] = array("name" => "<div id=\"clipboardIconbarWrapper\">" . $clipboardModel->getIconbarHtml() . "</div>",
                "menu" => array(
                    array("name" => "<svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/clipboard.svg#clipboard'/></svg> Zwischenablage öffnen", "link" => "/clipboard/")));
          }
        }

        if ($TrashbinNotEmpty) {
          if($path != "/trashbin/"){
            $array[] = array("name" => "<div id=\"trashbinIconbarWrapper\">" . $trashbinModel->getIconbarHtml() . "</div>",
                "menu" => array(
                    array("name" => "<svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/trash.svg#trash'/></svg> Papierkorb öffnen", "link" => "/trashbin/"),
                    array("name" => "<svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/empty_trashbin.svg#empty_trashbin'/></svg> Papierkorb leeren", "onclick" => "event.stopPropagation();sendRequest('EmptyTrashbin', {}, '', 'popup', null, null, 'explorer');")));
          }
          else{
            $array[] = array("name" => "<div title='Papierkorb leeren'><svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/empty_trashbin.svg#empty_trashbin'/></svg></div>", "onclick"=>"sendRequest('EmptyTrashbin', {}, '', 'popup', null, null, 'explorer');return false;");
          }
        } else {
          if($path != "/trashbin/"){
            $array[] = array("name" => "<div id=\"trashbinIconbarWrapper\">" . $trashbinModel->getIconbarHtml() . "</div>",
                "menu" => array(
                    array("name" => "<svg><use xlink:href='" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/trash.svg#trash'/></svg> Papierkorb öffnen", "link" => "/trashbin/")));
          }
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
            //if(!$currentUser instanceof \steam_user) throw new Exception ("The current user cannot be determined. The variable is of the type ".gettype($currentUser).". The class is ".get_class($currentUser));
            if($currentUser){
              $object = $currentUser->get_workroom();
              return $object;
            }
        }
        return null;
    }

    public function checkClipboardInventory($inventory) {
      $path = strtolower($_SERVER["REQUEST_URI"]);
      if(strpos($path, "favorite") !== false) return false;
      if(strpos($path, "group") !== false) return false;
      if(strpos($path, "postbox") !== false) return false;
      if(strpos($path, "profile") !== false) return false;
      if(strpos($path, "rapidfeedback") !== false) return false;
      if(strpos($path, "wiki") !== false) return false;
      if(strpos($path, "viewdocument") !== false) return false;
      if(strpos($path, "editdocument") !== false) return false;
      return true;
    }

}

?>

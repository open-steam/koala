<?php
namespace Explorer\Commands;
class GetPopupMenu extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $selection;
	private $x, $y, $height, $width;
	private $logged_in;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->selection = json_decode($this->params["selection"]);
		$this->x = $this->params["x"];
		$this->y = $this->params["y"];
		$this->height = $this->params["height"];
		$this->width = $this->params["width"];
		$portal = \lms_portal::get_instance();
		$lms_user = $portal->get_user();
		$this->logged_in = $lms_user->is_logged_in();
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$count = count($this->selection);
		$explorerUrl = \Explorer::getInstance()->getAssetUrl();
		if (!in_array($this->id, $this->selection) ||(in_array($this->id, $this->selection) && $count == 1)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			$name = $object->get_name();
			$env = $object->get_environment();

			$firstElement = 0;
			$inventory = $env->get_inventory();
			foreach ($inventory as $key => $element) {
				if($element instanceof \steam_user || $element instanceof \steam_trashbin) $firstElement++;
				if ($element->get_id() == $this->id) {
					$index = $key;
					break;
				}
			}

			$popupMenu =  new \Widgets\PopupMenu();

			if ($object instanceof \steam_trashbin) {
				$items = array(array("name" => "Papierkorb leeren", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"));
			} else if ($env instanceof \steam_trashbin) {
				$oldEnv = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["env"]);
				if ($oldEnv instanceof \steam_object && $oldEnv->check_access(SANCTION_WRITE)) {
					$restoreIcon = $explorerUrl . "icons/menu/restore.png";
					$items = array(
						array("name" => "Wiederherstellen<img src=\"{$restoreIcon}\">", "command" => "Restore", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'env':document.getElementById('environment').value}"));
        } else {
					$items = array(array("name" => "Keine Aktionen möglich"));
				}
			} else {
        $copyIcon = $explorerUrl . "icons/menu/copy.png";
        $cutIcon = $explorerUrl . "icons/menu/cut.png";
        $referIcon = $explorerUrl . "icons/menu/refer.png";
        $trashIcon = $explorerUrl . "icons/menu/trash.png";
        $hideIcon = $explorerUrl . "icons/menu/hide.png";
        $bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
				$colorpickerIcon = \Portal::getInstance()->getAssetUrl() . "icons/colorpicker.png";
				$sortIcon = $explorerUrl . "icons/menu/sort.png";
        $upIcon = $explorerUrl . "icons/menu/up.png";
        $downIcon = $explorerUrl . "icons/menu/down.png";
        $topIcon = $explorerUrl . "icons/menu/top.png";
        $bottomIcon = $explorerUrl . "icons/menu/bottom.png";
        $renameIcon = $explorerUrl . "icons/menu/rename.png";
        $editIcon = $explorerUrl . "icons/menu/edit.png";
        $propertiesIcon = $explorerUrl . "icons/menu/properties.png";
        $rightsIcon = $explorerUrl . "icons/menu/rights.png";
        $blankIcon = $explorerUrl . "icons/menu/blank.png";
        $subscribeIcon = $explorerUrl . "icons/subscribe.png";
        $unsubscribeIcon = $explorerUrl . "icons/unsubscribe.png";
				$downloadIcon = $explorerUrl . "icons/menu/download.png";

        $subscription = "";
        //prepare subscription element it it is an enabled extension
        if (strpos(EXTENSIONS_WHITELIST, "PortletSubscription")) {
            $type = getObjectType($object);
            $user = $GLOBALS["STEAM"]->get_current_steam_user();
            if ($type === "forum" || $type === "wiki" || $type === "room" || $type === "gallery" || $type === "portal" || ($type === "rapidfeedback" && $object->get_creator()->get_id() == $user->get_id()) || ($type === "document" && strstr($object->get_attribute(DOC_MIME_TYPE), "text")) || $type === "postbox") {
                $subscriptions = $user->get_attribute("USER_HOMEPORTAL_SUBSCRIPTIONS");
                if (is_array($subscriptions) && in_array($object->get_id(), $subscriptions)) {
                    $subscription = array("name" => "Abbestellen<img src=\"{$unsubscribeIcon}\">", "command" => "Unsubscribe", "namespace" => "explorer", "params" => "{'id':'{$object->get_id()}' }", "type" => "reload");
                } else {
                    $subscription = array("name" => "Abonnieren<img src=\"{$subscribeIcon}\">", "command" => "Subscribe", "namespace" => "explorer", "params" => "{'id':'{$object->get_id()}', 'column' : '2' }", "type" => "reload");
                }
            }
        }

        $items = array(
            ($this->logged_in && $object->check_access(SANCTION_READ)) ? array("name" => "Kopieren<img src=\"{$copyIcon}\">", "command" => "Copy", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}") : "",
            ($object->check_access(SANCTION_WRITE)) ? array("name" => "Ausschneiden<img src=\"{$cutIcon}\">", "command" => "Cut", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}") : "",
            ($this->logged_in) ? array("name" => "Referenz erstellen<img src=\"{$referIcon}\">", "command" => "Reference", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}") : "",
            ($object->check_access(SANCTION_WRITE)) ? array("name" => "Löschen<img src=\"{$trashIcon}\">", "command" => "Delete", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}") : "",
            ($object->check_access(SANCTION_WRITE)) ? array("name" => "Einfärben<img src=\"{$colorpickerIcon}\">", "direction" => "left", "menu" => array (
                array("raw" => " <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'transparent'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/transparent.png\"></a>
                    <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'red'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/red.png\"></a>
                    <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'orange'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/orange.png\"></a>
                    <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'yellow'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/yellow.png\"></a>
                    <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'green'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/green.png\"></a>
                    <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'blue'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/blue.png\"></a>
                    <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'purple'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/purple.png\"></a>
                    <a href=\"#\" onclick=\"sendRequest('ChangeColorLabel', {'id':'{$this->id}', 'color':'grey'}, 'listviewer-overlay', 'updater', null, null, 'explorer'); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/grey.png\"></a>"),
            )) : "",
            ($this->logged_in /*&& !\Bookmarks\Model\Bookmark::isBookmark($this->id)*/) ? array("name" => "Lesezeichen anlegen<img src=\"{$bookmarkIcon}\">", "command" => "AddBookmark", "namespace" => "bookmarks", "elementId" => "{$this->id}_BookmarkMarkerWrapper", "params" => "{'id':'{$this->id}'}") : "",

            $subscription,

            ($object->check_access(SANCTION_WRITE) && count($inventory) >=2) ? array("name" => "Umsortieren<img src=\"{$sortIcon}\">", "direction" => "left", "menu" => array(
								($index > $firstElement) ? array("name" => "Ganz nach oben<img src=\"{$topIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'top'}") : "",
                ($index > $firstElement) ? array("name" => "Eins nach oben<img src=\"{$upIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'up'}") : "",
                ($index < count($inventory)-1) ? array("name" => "Eins nach unten<img src=\"{$downIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'down'}") : "",
                ($index < count($inventory)-1) ? array("name" => "Ganz nach unten<img src=\"{$bottomIcon}\">", "command" => "Order", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'direction':'bottom'}") : ""
            )) : "",

            ($this->logged_in) ? array("name" => "SEPARATOR") : "",
            array("raw" => "<a href=\"#\" style=\"width:500px;\" onclick=\"event.stopPropagation(); removeAllDirectEditors();if (!jQuery('#{$this->id}_1').hasClass('directEditor')) { jQuery('#{$this->id}_1').addClass('directEditor').html(''); var obj = new Object; obj.id = '{$this->id}'; sendRequest('GetDirectEditor', obj, '{$this->id}_1', 'updater'); } jQuery('.popupmenuwapper').parent().html('');jQuery('.open').removeClass('open'); return false;\">Umbenennen<img src=\"{$renameIcon}\"></a>"),
            (($object instanceof \steam_container) && ($object->get_attribute("bid:presentation") === "index") && ($object->check_access(SANCTION_READ))) ? array("name" => "Listenansicht<img src=\"{$blankIcon}\">", "link" => PATH_URL . "Explorer/Index/" . $this->id . "/?view=list") : "",
            (($object instanceof \steam_document) && ($object->get_attribute(DOC_MIME_TYPE) != "text/html") && ($object->check_access(SANCTION_READ))) ? array("name" => "Herunterladen<img src=\"{$downloadIcon}\">", "link" => PATH_URL . "Download/Document/" . $this->id . "/" . $name) : "",
            array("name" => "Eigenschaften...<img src=\"{$propertiesIcon}\">", "command" => "Properties", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup"),

            //display rights dialog for a postbox or for a non postbox object
            ($object->check_access(SANCTION_SANCTION) && ($object->get_attribute(OBJ_TYPE) === 'postbox')) ? array("name" => "Rechte...<img src=\"{$rightsIcon}\">", "command" => "Sanctions", "namespace" => "postbox", "params" => "{'id':'{$this->id}'}", "type" => "popup") : "",
            ($object->check_access(SANCTION_SANCTION) && (stristr($object->get_attribute(OBJ_TYPE), 'postbox') === FALSE)) ? array("name" => "Rechte...<img src=\"{$rightsIcon}\">", "command" => "Sanctions", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "popup") : ""
        );
    }
			$popupMenu->setItems($items);
			$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
			$popupMenu->setWidth("150px");
		} else {
      $writeAccess = TRUE;
      $readAccess = TRUE;
      foreach ($this->selection as $selectedObjectID) {
          $selectedObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $selectedObjectID);
          if (!$selectedObject->check_access(SANCTION_WRITE)) {
              $writeAccess = FALSE;
          }
          if (!$selectedObject->check_access(SANCTION_READ)) {
              $readAccess = FALSE;
          }
      }

      $copyIcon = $explorerUrl . "icons/menu/copy.png";
      $cutIcon = $explorerUrl . "icons/menu/cut.png";
      $referIcon = $explorerUrl . "icons/menu/refer.png";
      $trashIcon = $explorerUrl . "icons/menu/trash.png";
      $hideIcon = $explorerUrl . "icons/menu/hide.png";
      $blankIcon = $explorerUrl . "icons/menu/blank.png";
			$colorpickerIcon = \Portal::getInstance()->getAssetUrl() . "icons/colorpicker.png";
      $popupMenu =  new \Widgets\PopupMenu();
      if ($this->logged_in) {
          $items = array(
              ($readAccess) ? array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Copy', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Kopiere Objekte ...', 0,  getSelectionAsArray().length); return false;\">{$count} Objekte kopieren<img src=\"{$copyIcon}\"></a>") : "",
              ($writeAccess) ? array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Cut', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Schneide Objekte aus ...', 0,  getSelectionAsArray().length); return false;\">{$count} Objekte ausschneiden<img src=\"{$cutIcon}\"></a>") : "",
              array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Reference', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Referenziere Objekte ...', 0,  getSelectionAsArray().length); return false;\">{$count} Objektreferenzen erstellen<img src=\"{$referIcon}\"></a>"),
              ($writeAccess) ? array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Delete', getParamsArray({}), getElementIdArray(''), 'updater', null, null, 'explorer', 'Lösche Objekte ...', 0,  getSelectionAsArray().length); return false;\">{$count} Objekte löschen<img src=\"{$trashIcon}\"></a>") : "",
              ($writeAccess) ? array("name" => "{$count} Objekte einfärben<img src=\"{$colorpickerIcon}\">", "direction" => "left", "menu" => array (
                  array("raw" => " <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'transparent'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/transparent.png\"></a>
                          <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'red'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/red.png\"></a>
                          <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'orange'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/orange.png\"></a>
                          <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'yellow'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/yellow.png\"></a>
                          <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'green'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/green.png\"></a>
                          <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'blue'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/blue.png\"></a>
                          <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'purple'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/purple.png\"></a>
                          <a href=\"#\" onclick=\"sendMultiRequest('ChangeColorLabel', getParamsArray({'color':'grey'}), getElementIdArray('listviewer-overlay'), 'updater', null, null, 'explorer', 'Ändere Farbe ...', 0,  getSelectionAsArray().length); return false;\"><img src=\"{$this->getExtension()->getAssetUrl()}icons/grey.png\"></a>"),
                  )) : "",
          );
      } else {
          $items = array(array("name" => "Keine Aktionen möglich"));
      }
		$popupMenu->setItems($items);
		$popupMenu->setPosition(round($this->x + $this->width - 155) . "px", round($this->y + $this->height + 4) . "px");
		$popupMenu->setWidth("200px");
            }
    $ajaxResponseObject->setStatus("ok");
    $ajaxResponseObject->addWidget($popupMenu);
    return $ajaxResponseObject;
	}
}
?>

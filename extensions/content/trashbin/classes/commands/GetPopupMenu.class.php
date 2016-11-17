<?php

namespace Trashbin\Commands;

class GetPopupMenu extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $selection;
    private $x, $y, $height, $width;

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
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $count = count($this->selection);
        $explorerAssetUrl = \Explorer::getInstance()->getAssetUrl();
        if (!in_array($this->id, $this->selection) || (in_array($this->id, $this->selection) && $count == 1)) {
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            $env = $object->get_environment();

            $inventory = $env->get_inventory();
            foreach ($inventory as $key => $element) {
                if ($element->get_id() == $this->id) {
                    $index = $key;
                }
            }


            if ($object instanceof \steam_trashbin) {
                $items = array(array("name" => "Papierkorb leeren", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}"));
            } else if ($env instanceof \steam_trashbin) {
                $trashIcon = $explorerAssetUrl . "icons/menu/svg/trash.svg";
                $cutIcon = $explorerAssetUrl . "icons/menu/svg/cut.svg";

                $items = array(
                    array("name" => "<svg><use xlink:href='{$trashIcon}#trash'/></svg> Endgültig löschen", "command" => "EmptyTrashbin", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "nonModalUpdater"),
                    array("name" => "<svg><use xlink:href='{$cutIcon}#cut'/></svg> Ausschneiden", "command" => "Cut", "namespace" => "explorer", "params" => "{'id':'{$this->id}'}", "type" => "inform")
                );
                /*
                  if ($object->get_attribute("OBJ_LAST_LOCATION_ID") !== "") {
                  $formerEnvironment = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object->get_attribute("OBJ_LAST_LOCATION_ID"));
                  if ($formerEnvironment instanceof \steam_object && $formerEnvironment->check_access(SANCTION_WRITE)) {
                  $restoreIcon = $explorerAssetUrl . "icons/menu/svg/restore.svg";
                  $items[] = array("name" => "<svg><use xlink:href='{$restoreIcon}#restore'/></svg> Zurücklegen", "command" => "Restore", "namespace" => "explorer", "params" => "{'id':'{$this->id}', 'restoreFromTrashbin': 'true'}", "type" => "nonModalUpdater");
                  }
                  }
                 */
            }
        } else {

            $cutIcon = $explorerAssetUrl . "icons/menu/svg/cut.svg";
            $trashIcon = $explorerAssetUrl . "icons/menu/svg/trash.svg";

            $viewAttribute = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("EXPLORER_VIEW");
            if ($viewAttribute && $viewAttribute == "gallery") {
                $paramsArrayFunction = "getGalleryParamsArray";
                $ElementIdFunction = "getGalleryElementIdArray";
                $SelectionFunction = "getGallerySelectionAsArray().length";
            } else {
                $paramsArrayFunction = "getParamsArray";
                $ElementIdFunction = "getElementIdArray";
                $SelectionFunction = "getSelectionAsArray().length";
            }

            $items = array(
                array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('EmptyTrashbin', $paramsArrayFunction({}), $ElementIdFunction(''), 'updater', null, null, 'explorer', 'Lösche Objekte ...', 0, $SelectionFunction); return false;\"><svg><use xlink:href='{$trashIcon}#trash'/></svg> {$count} Objekte endgültig löschen</a>"),
                array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Cut', $paramsArrayFunction({}), $ElementIdFunction(''), 'inform', null, null, 'explorer', 'Lösche Objekte ...', 0, $SelectionFunction); return false;\"><svg><use xlink:href='{$cutIcon}#cut'/></svg> {$count} Objekte ausschneiden</a>")
            );

            $objectWithoutFormerEnvironment = false;
            foreach ($this->selection as $objectid) {
                $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectid);
                $formerEnvironment = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $object->get_attribute("OBJ_LAST_LOCATION_ID"));
                if ((!$formerEnvironment instanceof \steam_object) || $object->get_attribute("OBJ_LAST_LOCATION_ID") == "") {
                    $objectWithoutFormerEnvironment = true;
                }
            }
            if (!$objectWithoutFormerEnvironment) {
                $restoreIcon = $explorerAssetUrl . "icons/menu/svg/restore.svg";
                $items[] = array("raw" => "<a href=\"#\" onclick=\"sendMultiRequest('Restore', $paramsArrayFunction({'restoreFromTrashbin': 'true'}), $ElementIdFunction(''), 'nonModalUpdater', null, null, 'explorer', 'Lege Objekte zurück ...', 0, $SelectionFunction); return false;\"><svg><use xlink:href='{$restoreIcon}#restore'/></svg> {$count} Objekte Zurücklegen</a>");
            }
        }
        $popupMenu = new \Widgets\PopupMenu();
        $popupMenu->setItems($items);
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($popupMenu);
        return $ajaxResponseObject;
    }

}

?>

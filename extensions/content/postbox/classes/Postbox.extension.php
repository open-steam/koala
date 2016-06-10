<?php

class Postbox extends AbstractExtension implements IObjectExtension, IIconBarExtension {

    public function getName() {
        return "Postbox";
    }

    public function getDesciption() {
        return "Extension Postbox";
    }

    public function getVersion() {
        return "v1.0.0";
    }

    public function getAuthors() {
        $result = array();
        $result[] = new Person("Christoph", "Sens", "csens@mail.uni-paderborn.de");
        return $result;
    }

    public function getObjectReadableName() {
        return "Briefkasten";
    }

    public function getObjectReadableDescription() {
        return "";
    }

    public function getObjectIconUrl() {
        return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/postbox.png";
    }

    public function getCreateNewCommand(IdRequestObject $idEnvironment) {
        return new \Postbox\Commands\NewPostbox();
    }

    public function getCommandByObjectId(IdRequestObject $idRequestObject) {
        $postbox = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
        $postboxType = $postbox->get_attribute("OBJ_TYPE");
        if ($postboxType === "postbox") {
            return new \Postbox\Commands\Index();
        }
        return null;
    }

    public function getCurrentObject(UrlRequestObject $urlRequestObject) {
        $params = $urlRequestObject->getParams();
        $id = $params[0];
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        return $object;

    }

    public function getPriority() {
        return 8;
    }

    public function getIconBarEntries() {
      $path = strtolower($_SERVER["REQUEST_URI"]);
      if (strpos($path, "postbox") !== false && strpos($path, "view") == false) {
        $oldURL = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $newURL = str_replace("postbox", "explorer", $oldURL);
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
        if($currentObjectID != ""){
          $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectID);
          $env = $obj->get_environment();
          $array = array();
    			$array[] = array("name" => "<img title=\"Aufwärts\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/arrow_up_white.png\">", "onclick"=>"location.href='" . PATH_URL . "explorer/index/{$env->get_id()}/'");
          $user = lms_steam::get_current_user();
          $checkAccessAdmin = $obj->check_access(SANCTION_ALL, $user);
          if ($checkAccessAdmin) {
              $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('edit', {'id':{$currentObjectID}}, '', 'popup', null, null, 'postbox');return false;");
              $array[] = array("name" => "<img title=\"In Ordner umwandeln\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/folder_white_convert.png\">", "onclick"=>"if(confirm('Das aktuelle Abgabefach wird in einen Ordner umgewandelt. Dieser Vorgang kann nicht rückgängig gemacht werden!')){sendRequest('Release', {'id':{$currentObjectID}}, '', 'data', null, null, 'postbox');window.open('$newURL', '_self');return false;}");
              $array[] = array("name" => "<img title=\"Rechte\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights_white.png\">", "onclick"=>"sendRequest('Sanctions', {'id':{$currentObjectID}}, '', 'popup', null, null, 'postbox');return false;");
            }
          return $array;
        }
      }
    }

}

?>

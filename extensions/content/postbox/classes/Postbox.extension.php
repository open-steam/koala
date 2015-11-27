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
      if (strpos($path, "postbox") !== false) {
        $arr = explode('/', $path);
        $id = $arr[count($arr)-2];
        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        $checkAccessAdmin = $obj->check_access(SANCTION_ALL);
        $array = array();
        if ($checkAccessAdmin) {
            $array[] = array("name" => "<img title=\"Eigenschaften\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/properties_white.png\">", "onclick"=>"sendRequest('edit', {'id':{$id}}, '', 'popup', null, null, 'postbox');return false;");
            $array[] = array("name" => "<img title=\"In Ordner umwandeln\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/folder_white.png\">", "onclick"=>"if(confirm('Das aktuelle Abgabefach wird in einen Ordner umgewandelt. Dieser Vorgang kann nicht rückgängig gemacht werden!')){sendRequest('Release', {'id':{$id}}, '', 'data', null, null, 'postbox');history.back();return false;}");
            $array[] = array("name" => "<img title=\"Rechte\" src=\"" . \Explorer::getInstance()->getAssetUrl() . "icons/menu/rights_white.png\">", "onclick"=>"sendRequest('Sanctions', {'id':{$id}}, '', 'popup', null, null, 'postbox');return false;");
          }
          return $array;
        }
    }

}

?>

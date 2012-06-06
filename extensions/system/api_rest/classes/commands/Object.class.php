<?php
namespace Rest\Commands;
class Object extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $function;
    private $functionList = array("getAttributes", "getAttribute", "setAttribute", "move", "delete", "getObjectById", "getObjectByPath");

    public function httpAuth(\IRequestObject $requestObject) {
        return true;
    }

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->function = $this->params[0]: "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        if (!in_array(strtolower($this->function), array_map("strtolower",$this->functionList))) {
             $this->function = $this->functionList[0];
        }
        array_shift($this->params);
        $result = call_user_func_array(array(__CLASS__, $this->function), $this->params?:array());
        $resultArray = getSerializedObject($result);
        echo json_encode($resultArray);
        exit;
    }

    public function getAttributes($id) {
        $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamObject instanceof \steam_object) {
            return $steamObject->get_attributes();
        }
        HTTPStatus(400);
    }

    public function getAttribute($id, $attribute) {
        $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamObject instanceof \steam_object) {
            return $steamObject->get_attribute($attribute);
        }
        HTTPStatus(400);
    }

    public function setAttribute($id, $attribute, $value) {
        $value = json_decode($value);
        $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamObject instanceof \steam_object) {
            return $steamObject->set_attribute($attribute,$value);
        }
        HTTPStatus(400);
    }

    public function move($id, $destId) {
        $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamObject instanceof \steam_object) {
            $destSteamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $destId);
            if ($destSteamObject instanceof \steam_container) {
                return $steamObject->move($destSteamObject);
            }
        }
        HTTPStatus(400);
    }

    public function delete($id) {
        $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamObject instanceof \steam_object) {
            return $steamObject->delete();
        }
        HTTPStatus(400);
    }

    public function getObjectById($id) {
        $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamObject instanceof \steam_object) {
            return $steamObject;
        }
        HTTPStatus(400);
    }

    public function getObjectByPath($path) {
        $steamObject = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $path);
        if ($steamObject instanceof \steam_object) {
            return $steamObject;
        }
        HTTPStatus(400);
    }
}
?>
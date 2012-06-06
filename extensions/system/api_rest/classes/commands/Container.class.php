<?php
namespace Rest\Commands;
class Container extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $function;
    private $functionList = array("getInventory", "create");

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
        $result = @call_user_func_array(array(__CLASS__, $this->function), $this->params?:array());
        $resultArray = getSerializedObject($result);
        echo json_encode($resultArray);
        exit;
    }

    public function getInventory($id) {
        $steamContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamContainer instanceof \steam_container) {
            return $steamContainer->get_inventory();
        }
        HTTPStatus(400);
    }

    public function create($name, $destId) {
        $destSteamContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $destId);
        if ($destSteamContainer instanceof \steam_container) {
            return \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $name, $destSteamContainer);
        }
        HTTPStatus(400);
    }
}
?>
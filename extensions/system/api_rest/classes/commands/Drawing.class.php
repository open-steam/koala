<?php
namespace Rest\Commands;
class Drawing extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $function;
    private $functionList = array("create");

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
        die;
    }

    public function create($name, $destId, $type) {
        $destSteamContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $destId);
        if ($destSteamContainer instanceof \steam_container) {
            $obj = \steam_factory::create_drawing($GLOBALS["STEAM"]->get_id(), $name, $destSteamContainer);
        	$obj->unlock_attributes();
            $obj->set_attribute("GRAPHIC_TYPE", $type);
        	return $obj;
        }
        HTTPStatus(400);
    }
    
    
    
}
?>
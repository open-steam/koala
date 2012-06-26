<?php
namespace Rest\Commands;
class Document extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $function;
    private $functionList = array("getContent", "setContent", "create");

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

    public function getContent($id) {
        $steamDocument = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamDocument instanceof \steam_document) {
            die($steamDocument->get_content());
        }
        HTTPStatus(400);
    }

    public function setContent($id) {
        $steamDocument = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamDocument instanceof \steam_document) {
            return $steamDocument->set_content(file_get_contents($_FILES['uploadedfile']['tmp_name']));
        }
        HTTPStatus(400);
    }

    public function create($name, $destId) {
        $destSteamContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $destId);
        if ($destSteamContainer instanceof \steam_container) {
            return \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $name, "", detectMimeType($name), $destSteamContainer);
        }
        HTTPStatus(400);
    }
}
?>
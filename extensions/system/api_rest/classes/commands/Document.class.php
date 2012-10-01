<?php
namespace Rest\Commands;
class Document extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $function;
    private $functionList = array("getContent", "setContent", "create", "getDimensions");

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
        if (!isset($_POST['content'])) {
        	HTTPStatus(500);
        	return "";
        }
        
        if ($steamDocument instanceof \steam_document) {
        	return $steamDocument->set_content(base64_decode($_POST['content']));
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
    
    public function getDimensions($id) {
    	
    	$steamDocument = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
    	if ($steamDocument instanceof \steam_document) {

    		if ($steamDocument->get_attribute("DOC_MIME_TYPE") != "image/jpeg") {
    			return false;
    		}
    		
    		$content = $steamDocument->get_content();
    		$tempFileName = tempnam(sys_get_temp_dir(), 'koalaRestAPI');
    		
    		file_put_contents($tempFileName, $content);
    		
    		$imageIdent = getimagesize($tempFileName);
    		
    		@unlink($tempFileName);
    		
    		return Array(
    			"width" => $imageIdent[0],
    			"height" => $imageIdent[1]
    		);
    		
    	} else return false;
    	HTTPStatus(400);
    }
    
}
?>
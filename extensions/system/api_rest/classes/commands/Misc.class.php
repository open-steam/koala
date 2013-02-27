<?php
namespace Rest\Commands;
class Misc extends \AbstractCommand implements \IResourcesCommand {

	private $params;
	private $function;
	private $functionList = array("getMimeTypeIcon", "getType");

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

	public function resourcesResponse() {
		if (!in_array(strtolower($this->function), array_map("strtolower",$this->functionList))) {
			$this->function = $this->functionList[0];
		}
		array_shift($this->params);
		$result = call_user_func_array(array(__CLASS__, $this->function), $this->params?:array());

		if (is_array($result)) {
			$resultArray = getSerializedObject($result['object']);
			echo json_encode(Array("object" => $resultArray, "type" => $result['type'], "id" => $result['id']));
		} else {
			$resultArray = getSerializedObject($result);
			echo json_encode($resultArray);
		}


		exit;
	}

	public function getMimeTypeIcon($id) {
		$steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		if ($steamObject instanceof \steam_object) {
			$path = getcwd()."/../../../extensions/content/explorer/asset/icons/mimetype/".deriveIcon($steamObject);
			if (!file_exists($path)) {
				HTTPStatus(400); return "Not Found";
			}
			header("Content-Type: image/png");
			readfile($path); die();
		}
		HTTPStatus(400);
	}

	public function getType($id) {
	    $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
	    if ($steamObject instanceof \steam_object) {
	    	echo getObjectType($steamObject); die();
	    }
	    HTTPStatus(400);
	}
	

}
?>
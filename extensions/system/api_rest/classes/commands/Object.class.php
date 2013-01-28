<?php
namespace Rest\Commands;
class Object extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $function;
	private $functionList = array("getAttributes", "duplicate", "setAttributes", "getAttribute", "setAttribute", "move", "delete", "getObjectById", "getObjectByPath", "checkRight");

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

		if (is_array($result)) {
			$resultArray = getSerializedObject($result['object']);
			echo json_encode(Array("object" => $resultArray, "type" => $result['type'], "id" => $result['id']));
		} else {
			$resultArray = getSerializedObject($result);
			echo json_encode($resultArray);
		}


		exit;
	}

	public function getAttributes($id) {

		$steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		if ($steamObject instanceof \steam_object) {
			 
			//get attributes (cumbersome, but get_attributes seems to be broken)
			$resultArray = getSerializedObject($steamObject);
			$resultArray = $resultArray["objects"][$steamObject->get_id()]["attributes"];
			 
			echo json_encode($resultArray);
		}
		HTTPStatus(400);
		die();
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

	public function setAttributes($id, $attributes) {
		 
		//generate attributes from params (avoid uncomplete attributes string if string contains forward slashes)
		$attributes = $this->params;
		array_shift($attributes);
		$attributes = implode("/", $attributes);
		 
		$attributes = json_decode($attributes, true);
		$steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		if ($steamObject instanceof \steam_object) {

			if (is_array($attributes)) {
				
				foreach ($attributes as $key => $value) {
					 
					if (
							$key == "OBJ_WIDTH" ||
							$key == "OBJ_HEIGHT" ||
							$key == "OBJ_POSITION_X" ||
							$key == "OBJ_POSITION_Y" ||
							$key == "OBJ_POSITION_Z" ||
							$key == "DRAWING_WIDTH" ||
							$key == "DRAWING_HEIGHT"
					) {
						$value = floatval($value);
					}
					
					if ($value === "unnamed") $value = "unnamed object"; //value=unnamed will cause error 403

					$steamObject->set_attribute($key,$value);
					 
				}
			}

			return true;
			 
			HTTPStatus(400);

		} else {
			return false;
		}

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
	
	
	public function duplicate($id, $destId) {
		$steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		if ($steamObject instanceof \steam_object) {
			$destSteamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $destId);
			if ($destSteamObject instanceof \steam_container) {
				$newObj = \steam_factory::create_copy($GLOBALS["STEAM"]->get_id(), $steamObject);
				$newObj->move($destSteamObject);
				return $newObj;
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
			return Array("object" => $steamObject, "type" => $steamObject->get_object_class(), "id" => $steamObject->get_id());
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

	public function checkRight($id, $type) {
		$steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		if ($steamObject instanceof \steam_object) {
			$type = strtolower($type);
			if ($type == "read") {
				return $steamObject->check_access_read();	
			} elseif ($type == "write") {
				return $steamObject->check_access_write();   
			} elseif ($type == "insert") {
				return $steamObject->check_access_insert();   
			} else {
				HTTPStatus(404);
				return;
			}
		}
		HTTPStatus(400);
	}

}
?>
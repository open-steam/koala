<?php
class DatabindingURLEncodeName extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $object;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
                $data = array();
		if (isset($this->params["value"])) {
			$oldValue = $this->object->get_attribute("OBJ_NAME");
			try {
				$this->object->set_attribute("OBJ_NAME", rawurlencode(strip_tags($this->params["value"])));
			} catch (steam_exception $e) {
				$data["oldValue"] = rawurldecode($oldValue);
			 	$data["error"] = $e->get_message();
				$data["undo"] = false;
				$ajaxResponseObject->setStatus("ok");
				$ajaxResponseObject->setData($data);
				return $ajaxResponseObject;
			}
			$ajaxResponseObject->setStatus("ok");
			
			$newValue = $this->object->get_attribute("OBJ_NAME");
			
			if ($newValue === rawurlencode($this->params["value"])) {
				$data["oldValue"] = rawurldecode($oldValue);
				$data["newValue"] = rawurldecode($newValue);
				$data["error"] = "none";
				$data["undo"] = true;
			 } else {
                                if ($oldValue !== $newValue) {
                                    $this->object->set_attribute("OBJ_NAME", $oldValue);
                                }
			 	$data["oldValue"] = rawurldecode($oldValue);
			 	$data["error"] = "Data could not be saved.";
				$data["undo"] = false;
			 }
			 $ajaxResponseObject->setData($data);
		} else {
			$ajaxResponseObject->setStatus("error");
		}
		return $ajaxResponseObject;
	}
}
?>
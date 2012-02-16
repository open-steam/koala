<?php
namespace PortletTermplan\Commands;

class DatabindingPortletTermplanEntries extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $object;
	
	private $entryIndex;
	private $value;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		
		$this->id = $this->params["id"];
		$this->entryIndex = $this->params["entryIndex"];
		$this->value = $this->params["value"];
		
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}
	
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		if (isset($this->params["entryIndex"]) && isset($this->params["value"])) {
			$data = array();
			$oldValue = $this->getEntryField($this->object, $this->entryIndex);
			try {
				$this->setEntryField($this->object, $this->entryIndex, $this->value);
			} catch (steam_exception $e) {
				$data["oldValue"] = $oldValue;
			 	$data["error"] = $e->get_message();
				$data["undo"] = false;
				$ajaxResponseObject->setStatus("ok");
				$ajaxResponseObject->setData($data);
				return $ajaxResponseObject;
			}
			$ajaxResponseObject->setStatus("ok");
			
			$newValue = $this->getEntryField($this->object,$this->entryIndex);
			
			if ($newValue === $this->params["value"]) {
				$data["oldValue"] = $oldValue;
				$data["newValue"] = $newValue;
				$data["error"] = "none";
				$data["undo"] = true;
			 } else {
			 	$data["oldValue"] = $oldValue;
			 	$data["error"] = "Data could not be saved.";
				$data["undo"] = false;
			 }
			 $ajaxResponseObject->setData($data);
		} else {
			$ajaxResponseObject->setStatus("error: parameter missing");
		}
		return $ajaxResponseObject;
	}
	
	
	private function setEntryField($object, $entryIndex, $value){
		$objectId = $object->get_id();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		
		//read attribute
		$portletContent = $object->get_attribute("bid:portlet:content");
		$optionsDescription = $portletContent["options"];
		$optionsDescription[$entryIndex]=$value;
		
		//write attribute
		$portletContent["options_votecount"]=array(0,0,0,0,0,0); //avoid notices
		$portletContent["options"] = $optionsDescription;
		$object->set_attribute("bid:portlet:content", $portletContent);
		return true;
	}
	
	
	private function getEntryField($object, $entryIndex){
		$objectId = $object->get_id();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$portletContent = $object->get_attribute("bid:portlet:content");
		$optionsDescription = $portletContent["options"];
		return $optionsDescription[$entryIndex];
	}
}
?>
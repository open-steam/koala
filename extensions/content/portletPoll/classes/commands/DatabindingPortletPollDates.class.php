<?php

namespace PortletPoll\Commands;

class DatabindingPortletPollDates extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $object;
	
	private $field;
	private $value;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		
		$this->id = $this->params["id"];
		$this->field = $this->params["field"];
		$this->value = $this->params["value"];
		
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		if (isset($this->params["field"]) && isset($this->params["value"])) {
			$data = array();
			$oldValue = $this->getEntryField($this->object, $this->field);
			try {
				$this->setEntryField($this->object, $this->field, $this->value);
			} catch (steam_exception $e) {
				$data["oldValue"] = $oldValue;
			 	$data["error"] = $e->get_message();
				$data["undo"] = false;
				$ajaxResponseObject->setStatus("ok");
				$ajaxResponseObject->setData($data);
				return $ajaxResponseObject;
			}
			$ajaxResponseObject->setStatus("ok");
			
			$newValue = $this->getEntryField($this->object,$this->field);
			
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
	
	
	private function setEntryField($object, $field, $value){
		$objectId = $object->get_id();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$content = $object->get_attribute("bid:portlet:content");
		
		$endDate = $content["end_date"];
		$startDate = $content["start_date"];
		
		switch($field){
			case "start_date": 
				$startDate["day"]=substr($value, 0,2);
				$startDate["month"]=substr($value, 3,2);
				$startDate["year"]=substr($value, 6,4);
				break;
			case "end_date": 
				$endDate["day"]=substr($value, 0,2);
				$endDate["month"]=substr($value, 3,2);
				$endDate["year"]=substr($value, 6,4);
				break;
			default: return false;
		}
		
		//write
		$content["end_date"] = $endDate;
		$content["start_date"] = $startDate;
		$object->set_attribute("bid:portlet:content", $content);
		
		return true;
	}
	
	private function getEntryField($object, $field){
		$objectId = $object->get_id();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$portletContent = $object->get_attribute("bid:portlet:content");
		
		$endDate = $portletContent["end_date"];
		$startDate = $portletContent["start_date"];
		
		switch($field){
			//datepicker databinding
			case "start_date":
				return $startDate["day"].".".$startDate["month"].".".$startDate["year"];
			case "end_date": 
				return $endDate["day"].".".$endDate["month"].".".$endDate["year"];
			default: return "Error on field: $field";
		 }
	}
}
?>
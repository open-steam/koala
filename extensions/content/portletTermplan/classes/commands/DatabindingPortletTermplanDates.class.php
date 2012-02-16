<?php
namespace PortletTermplan\Commands;

class DatabindingPortletTermplanDates extends \AbstractCommand implements \IAjaxCommand {
	
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
			//end date
			case "end_day": 
				$endDate["day"]=$value;
				break;
			case "end_month": 
				$endDate["month"]=$value;
				break;
			case "end_year": 
				$endDate["year"]=$value;
				break;
			//start date
			case "start_year": 
				$startDate["year"]=$value;
				break;
			case "start_month": 
				$startDate["month"]=$value;
				break;
			case "start_day": 
				$startDate["day"]=$value;
				break;
				
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
			//end date
			/*
			case "end_day": 
				return $endDate["day"];
			case "end_month": 
				return $endDate["month"];
			case "end_year": 
				return $endDate["year"];
			//start date
			case "start_year": 
				return $startDate["year"];
			case "start_month": 
				return $startDate["month"];
			case "start_day": 
				return $startDate["day"];
			*/	
				
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
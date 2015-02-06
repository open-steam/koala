<?php
namespace PortletTermplan\Commands;

class AttributeDataProviderPortletTermplanDates{
	
	private $field;
	
	public function __construct($field="begin_day") {
		$this->field = $field;
	}
	
	
	public function getData($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		}
		if ($object instanceof \steam_object) {
			$portletContent = $object->get_attribute("bid:portlet:content");
			
			$endDate = $portletContent["end_date"];
			$startDate = $portletContent["start_date"];
			
			switch($this->field){
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
				case "start_date": 
					return $startDate["day"].".".$startDate["month"].".".$startDate["year"];
				case "end_date": 
					return $endDate["day"].".".$endDate["month"].".".$endDate["year"];
					
				default: return "Error on field: $this->field"; 
			}
		}
	}
	
	

	public function getUpdateCode($object, $ownVariableName, $successMethod = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
                
                
                //if the user has an own successMethod, then call both methods, else just call the data-saveFunctionCallback method to wait until everything is saved
                $function = ($successMethod != "") ? ", function(response){dataSaveFunctionCallback(response); {$successMethod}({$ownVariableName}, response);}" : ", function(response){dataSaveFunctionCallback(response);}";
		
                //offer the possibility to use own variablenames
                //standard is 'value'
                $variableName = ($ownVariableName)? $ownVariableName:'value';
                
                return "sendRequest('DatabindingPortletTermplanDates', {'id': {$objectId}, 'field': '{$this->field}', 'value': {$variableName}}, '', 'data'{$function}, '', 'PortletTermplan');";
	}
	
	
	public function isChangeable($steamObject){
		return true;
	}
}
?>
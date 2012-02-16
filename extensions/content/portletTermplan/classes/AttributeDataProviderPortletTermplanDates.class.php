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
	
	

	public function getUpdateCode($object, $elementId, $successMethode = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = ($successMethode != "") ? ", function(response){{$successMethode}({$elementId}, response);}" : ",''";
		return <<< END
sendRequest('DatabindingPortletTermplanDates', {'id': {$objectId}, 'field': '{$this->field}', 'value': value}, '', 'data'{$function}, '', 'PortletTermplan');
END;
	}
	
	
	public function isChangeable($steamObject){
		return true;
	}
}
?>
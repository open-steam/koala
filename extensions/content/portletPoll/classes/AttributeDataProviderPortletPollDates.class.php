<?php
namespace PortletPoll\Commands;

class AttributeDataProviderPortletPollDates{
	
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
sendRequest('DatabindingPortletPollDates', {'id': {$objectId}, 'field': '{$this->field}', 'value': value}, '', 'data'{$function}, '', 'PortletPoll');
END;
	}
	
	
	public function isChangeable($steamObject){
		return true;
	}
}
?>
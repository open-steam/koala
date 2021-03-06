<?php
namespace PortletAppointment\Commands;

class AttributeDataProviderPortletAppointmentTerm {

	private $termIndex;
	private $field;

	public function __construct($termIndex=0, $field="title") {
		$this->termIndex = $termIndex;
		$this->field = $field;
	}


	public function getData($object) {
		if (is_int($object)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
		}
		if ($object instanceof \steam_object) {
			$portletContent = $object->get_attribute("bid:portlet:content");

			$term = $portletContent[$this->termIndex];

			switch($this->field){
				case "topic":
					if(0===$term["topic"]) return ""; //steam bug
					return $term["topic"];
				case "description":
					if(0===$term["description"]) return ""; //steam bug
					return $term["description"];

				//time and date
				case "start_time":
					$startTime = $term["start_time"];
					if($startTime["hour"]=="" && $startTime["minutes"]=="") return "";
					return $startTime["hour"].":".$startTime["minutes"];
				case "start_date":
					$startDate = $term["start_date"];
					return $startDate["day"].".".$startDate["month"].".".$startDate["year"];
				case "end_date":
					$endDate = $term["end_date"];
					if($endDate["day"]=="" && $endDate["month"]=="" && $endDate["year"]=="") return "";
					return $endDate["day"].".".$endDate["month"].".".$endDate["year"];
				case "end_time":
					$endTime = $term["end_time"];
					if($endTime["hour"]=="" && $endTime["minutes"]=="") return "";
					return $endTime["hour"].":".$endTime["minutes"];

				//other
				case "linkurl":
					if(0===$term["linkurl"]) return ""; //steam bug
					return $term["linkurl"];
				case "linkurl_open_extern":
					if(!isset($term["linkurl_open_extern"])) return "";
					if(0===$term["linkurl_open_extern"]) return ""; //steam bug
					return $term["linkurl_open_extern"];
				case "location":
					if(0===$term["location"]) return ""; //steam bug
					return $term["location"];
				default: return "Error in".$this->field;
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

		//offer the possibility to use own variablenames, standard is 'value'
		$variableName = ($ownVariableName)? $ownVariableName:'value';
		return "sendRequest('DatabindingPortletAppointmentTerm', {'id': {$objectId}, 'termIndex': '{$this->termIndex}', 'field': '{$this->field}', 'value': {$variableName}}, '', 'data'{$function}, '', 'PortletAppointment');";
	}


	public function isChangeable($steamObject){
		return true;
	}

}
?>

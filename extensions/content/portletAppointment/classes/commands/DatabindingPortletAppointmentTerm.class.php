<?php

namespace PortletAppointment\Commands;

class DatabindingPortletAppointmentTerm extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $object;
	private $categoryIndex;
	private $entryIndex;
	private $field;
	private $value;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		
		$this->id = $this->params["id"];
		$this->termIndex = $this->params["termIndex"];
		$this->field = $this->params["field"];
		$this->value = $this->params["value"];
		
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		if (isset($this->params["termIndex"]) && isset($this->params["value"])) {
			$data = array();
			$oldValue = $this->getEntryField($this->object,$this->termIndex, $this->field);
			try {
				$this->setEntryField($this->object,$this->termIndex, $this->field, $this->value);
			} catch (steam_exception $e) {
				$data["oldValue"] = $oldValue;
			 	$data["error"] = $e->get_message();
				$data["undo"] = false;
				$ajaxResponseObject->setStatus("ok");
				$ajaxResponseObject->setData($data);
				return $ajaxResponseObject;
			}
			$ajaxResponseObject->setStatus("ok");
			
			$newValue = $this->getEntryField($this->object,$this->termIndex, $this->field);
			
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
	
	
	private function setEntryField($object, $termIndex, $field, $value){
		$objectId = $object->get_id();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$content = $object->get_attribute("bid:portlet:content");
		
                usort($content, "sortPortletAppointments");
                $sortOrder = $object->get_attribute("bid:portlet:app:app_order");
           
                if ($sortOrder === "latest_first"){
                    $content = array_reverse($content);
                }
                
		//read
		$term = $content[$termIndex];
		
		//write
		switch($field){
			case "topic":
				$term["topic"] = $value;
				break;
			case "description": 
				$term["description"] = $value;
				break;
			case "start_date":
				$startDate = $term["start_date"];
				$valueDay = substr($value, 0,2);
				$valueMonth = substr($value, 3,2);
				$valueYear = substr($value, 6,4);
				//write
				if(!(strlen($value) == 10 && $value{2} === "." && $value{5} === "." && $this->validDay($valueDay) && $this->validMonth($valueMonth) && $this->validYear($valueYear)) && $value!="") return false;
                                $startDate["day"]=$valueDay;
				$startDate["month"]=$valueMonth;
				$startDate["year"]=$valueYear;
				$term["start_date"] = $startDate;
				break;
			case "end_date":
				$endDate = $term["end_date"];
				$valueDay = substr($value, 0,2);
				$valueMonth = substr($value, 3,2);
				$valueYear = substr($value, 6,4);
				//write
				if(!(strlen($value) == 10 && $value{2} === "." && $value{5} === "." && $this->validDay($valueDay) && $this->validMonth($valueMonth) && $this->validYear($valueYear)) && $value!="" ) return false;
				$endDate["day"]=$valueDay;
				$endDate["month"]=$valueMonth;
				$endDate["year"]=$valueYear;
				//empty case
				if($value==""){$endDate["day"]="";$endDate["month"]="";$endDate["year"]="";}
				$term["end_date"] = $endDate;
				break;
			case "start_time":
				$minutes = substr($value, 3,2);
				$hour = substr($value, 0,2);
				if(!(strlen($value) == 5 && $value{2} === ":" && $this->validMinute($minutes) && $this->validHour($hour)) && $value!="") return false;
				$startTime = $term["start_time"];
				$startTime["minutes"]=$minutes;
				$startTime["hour"]= $hour;
				//empty case
				if($value==""){$startTime["hour"]="";$startTime["minutes"]="";}
				$term["start_time"] = $startTime;
				break;	
			case "description":
				$term["description"] = $value;
				break;
			case "linkurl": 
				$term["linkurl"] = $value;
				break;
			case "linkurl_open_extern": 
				$term["linkurl_open_extern"] = $value;
				break;
			case "location":
				$term["location"] = $value;
				break;
			default:; 
		}
		
		
		$content[$termIndex] = $term;
		$object->set_attribute("bid:portlet:content", $content);
		
		return true;
	}
	
	private function getEntryField($object, $termIndex, $field){
		$objectId = $object->get_id();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$portletContent = $object->get_attribute("bid:portlet:content");
                
                usort($portletContent, "sortPortletAppointments");
                $sortOrder = $object->get_attribute("bid:portlet:app:app_order");
           
                if ($sortOrder === "latest_first"){
                    $portletContent = array_reverse($portletContent);
                }
                
		$term = $portletContent[$this->termIndex];
		
		//fields
		$startDate = $term["start_date"];
		$startTime = $term["start_time"];
		$endDate = $term["end_date"];
		
		switch($field){
			case "topic":
				if(0===$term["topic"]) return ""; //steam bug
				return $term["topic"];
			case "description":
				if(0===$term["description"]) return ""; //steam bug
				return $term["description"];
			case "linkurl":
				if(0===$term["linkurl"]) return ""; //steam bug 
				return $term["linkurl"];
			case "location":
				if(0===$term["location"]) return ""; //steam bug 
				return $term["location"];
			
			//datepicker databinding
			case "start_date":
				return $startDate["day"].".".$startDate["month"].".".$startDate["year"];
			case "end_date":
				if($endDate["day"]=="" && $endDate["month"]=="" && $endDate["year"]=="") return ""; 
				return $endDate["day"].".".$endDate["month"].".".$endDate["year"];
			case "start_time":
				if($startTime["hour"]=="" && $startTime["minutes"]=="") return "";
				return $startTime["hour"].":".$startTime["minutes"];
				
			default: return "Error Databinding ".$field; 
		}
	}
	
	
	//validators
	private function validDay($day){
                if (!(is_numeric($day) && is_int($day + 0))) return false;
		$day = intval($day);
		if(0<$day && $day<=31){
			return true;
			//return str_pad($day,2,"0",STR_PAD_LEFT);
		}
		return false;
	}
	
	private function validMonth($month){
                if (!(is_numeric($month) && is_int($month + 0))) return false;
		$month = intval($month);
		if(0<$month && $month<=12){
			return true;
			//return str_pad($month,2,"0",STR_PAD_LEFT);
		}
		return false;
	}
	
	private function validYear($year){
                if (!(is_numeric($year) && is_int($year + 0))) return false;
		$year = intval($year);
		if(0<$year && $year<=9999){
			return true;
			//return str_pad($year,4,"0",STR_PAD_LEFT);
		}
		return false;
	}
	
	private function validMinute($minute){
                if (!(is_numeric($minute) && is_int($minute + 0))) return false;
		$minute = intval($minute);
		if(0<=$minute && $minute<=59){
			return true;
			//return str_pad($minute,2,"0",STR_PAD_LEFT);
		}
		return false;
	}
	
	private function validHour($hour){
                if (!(is_numeric($hour) && is_int($hour + 0))) return false;
		$hour = intval($hour);
		if(0<$hour && $hour<=23){
			return true;
			//return str_pad($hour,2,"0",STR_PAD_LEFT);
		}
		return false;
	}
	
}
?>
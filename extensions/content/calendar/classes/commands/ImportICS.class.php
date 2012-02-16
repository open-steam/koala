<?php
namespace Calendar\Commands;
class ImportICS extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;
	private $date;


	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
//TODO: TEST IT WITH PARAMS
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
		
		$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		$steamCalendar=$obj->get_attribute("GROUP_CALENDAR");
		
		$icsString = $obj->get_attribute("CALENDAR_ICS_FILE_IN")->get_content();
		
		$icsString = strstr($icsString, "BEGIN:VEVENT");
		$contentArray = array();
		$contentArray = explode("\n", $icsString);
		$data=array();
		for($i=0;$i<count($contentArray)-2;$i++){
			$row = array();
			$row = explode(":",$contentArray[$i]);
			
			if($row[0] === "SUMMARY" ){
				$data["DATE_TITLE"]=$row[1];
			}else if($row[0] === "DESCRIPTION"){
				$data["DATE_DESCRIPTION"]=$row[1];
			}else if($row[0] ==="DTSTART"){
				$dateTime=self::getTransformedDateTime($row[1]);
				$data["DATE_START_DATE"]=$dateTime;
			}else if ($row[0] ==="DTEND"){
				$dateTime=self::getTransformedDateTime($row[1]);
				$data["DATE_END_DATE"]=$dateTime;
			}else if($row[0] === "LOCATION"){
				$data["DATE_LOCATION"]=$row[1];
			}else if($row[0] === "END"){
				$steamCalendar->add_entry($data);
				$data=array();
			}
			
		} 
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {		
		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;

	}
	private static function getTransformedDateTime($dateTime){		
		$year = substr($dateTime, 0, 4);
		$month = substr($dateTime,4,2 );
		$day = substr($dateTime, 6, 2);
		$hour =substr($dateTime, 9,2);
		$minute=substr($dateTime, 11,2);
		$sec = substr($dateTime,13,2);
		$date = $year."-".$month."-".$day;
		$time = $hour.":". $minute;
		return str_to_timestamp( $date, $time );
		
	}

}
?>
<?php
namespace Calendar\Commands;
class GenerateICS extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;
	private $calendar;

	public function httpAuth(\IRequestObject $requestObject) {
		return true;
	}

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {


		//TODO: NUR FILES GENERIEREN, WELCHE MAN AUCH SEHEN DARF

		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";

		//GET CURRENT CALENDAR
		if($this->id===""){
			throw new \Exception("objectid not set");
		}
		$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		if(!($obj instanceof \steam_room || $obj->get_attribute("OBJTYPE") == "calendar")){
			throw new \Exception("Current Instance is not a calendar");
		}
		//GET ALL DATES FOR THE CURRENT USER
		$currentUser = \lms_steam::get_current_user();
		$subscriptionWrapper = new SubscriptionWrapper();
		$subscriptionWrapper->setCalendar($obj);
		$result = $subscriptionWrapper->getSubscriptions();
		$extensions = array();
		$extensions = $result["extensions"];
		$calendars = array();
		$calendars = $result["result"];
		$sanctionWrapper= new SanctionWrapper();
		$sanctionWrapper->setExtensions($calendars);
		$sanctions = $sanctionWrapper->getSanction();




		$internSubscriptions = array();
		$externSubscriptions = array();
		foreach ($calendars as $calID => $cal){
			if($cal instanceof \steam_docextern){
				$externSubscriptions[$calID] = $cal;
			}else{
				$internSubscriptions[$calID] = $cal;
			}
		}
		$internDates = array();
		foreach ($internSubscriptions as $id => $sub){
			if($sanctions[$id] >= 1){
				$internDates[$id]=$sub->get_date_objects();
			}
		}

		$icsFile="BEGIN:VCALENDAR\n";
		$icsFile.="VERSION:2.0\n";
		$icsFile.="PRODID:koaLA 3.0\n";
		$icsFile.="METHOD:PUBLISH\n";

		foreach ($internDates as $calID => $cal){
			foreach ($cal as $date){
				if($date instanceof \steam_date){
					$icsFile.=self::getEventData($date);
				}
			}
		}

		foreach($externSubscriptions as $ex){
			$string = file_get_contents($ex->get_url());
			if($string !== 0){
				$string = strstr($string, "BEGIN:VEVENT");
				$string = str_replace("END:VCALENDAR", "", $string);
				$icsFile .= $string;
			}
		}
		$icsFile.="END:VCALENDAR\n";
		header("Content-type: text/calendar");
		echo $icsFile;die;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {


		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;

	}
	private static function getEventData(\steam_date $date){
		$dtStart=$date->get_attribute("DATE_START_DATE");
		$dtStart=strftime("%Y%m%d", $dtStart)."T".strftime("%H%M%S",$dtStart);
		$dtEnd = $date->get_attribute("DATE_END_DATE");
		$dtEnd=strftime("%Y%m%d", $dtEnd)."T".strftime("%H%M%S",$dtEnd);
		$summary = $date->get_attribute("DATE_TITLE");
		$description=$date->get_attribute("DATE_DESCRIPTION");
		$location=$date->get_attribute("DATE_LOCATION");
		$event="BEGIN:VEVENT\n";
		$event.="UID:".$date->get_id()."\n";
		$event.="SUMMARY:".$summary."\n";
		$event.="DESCRIPTION:".$description."\n";
		$event.="DTSTART:".$dtStart."\n";
		$event.="DTEND:".$dtEnd."\n";
		$event.="LOCATION:".$location."\n";
		$event.="END:VEVENT\n";
		return $event;
	}
}
?>


<?php
namespace Calendar\Commands;
class GenerateICS extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;
	private $calendar;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		
		
		//TODO: NUR FILES GENERIEREN, WELCHE MAN AUCHS EHEN DARF
		
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
		$objId=$this->id;
		$steam=$GLOBALS["STEAM"];
		$steamId=$steam->get_id();
		if($objId==""){
			throw new \Exception("id not set");
		}
		$obj=\steam_factory::get_object($steamId, $objId);
		$icsFile="BEGIN:VCALENDAR\n";
		$icsFile.="VERSION:2.0\n";
		$icsFile.="PRODID:koaLA 3.0 / ".$obj->get_name()." \n";
		$icsFile.="METHOD:PUBLISH\n";
		$objCalendar=$obj->get_attribute("USER_CALENDAR");
		$objCalendarSubscriptions=$obj->get_attribute("CALENDAR_SUBSCRIPTIONS");
		if($objCalendar===0 && $objCalendarSubscriptions===0){
			throw new \Exception("there is no calendar");
		}
		if($objCalendar!==0 && $objCalendar instanceof \steam_calendar){
			$dates=$objCalendar->get_date_objects();
			foreach($dates as $date){
				if($date instanceof \steam_date){
					$icsFile.=self::getEventData($date);
				}

			}
		}
		if($objCalendarSubscriptions!==0 && !empty($objCalendarSubscriptions)){
			foreach($objCalendarSubscriptions as $calendar){
				if($calendar instanceof \steam_calendar){
					$dates=$objCalendar->get_date_objects();
					foreach($dates as $date){
						if($date instanceof \steam_date){
							$icsFile.=self::getEventData($date);
						}
					}
				}
			}
		}
		$icsFile.="END:VCALENDAR";
		$existingIcsFile = $obj->get_attribute("CALENDAR_ICS_FILE_OUT");
		if($existingIcsFile===0){
			$icsFileObj=\steam_factory::create_textdoc($GLOBALS["STEAM"]->get_id(), $obj->get_name().".ics", $icsFile,$obj);
			$obj->set_attribute("CALENDAR_ICS_FILE_OUT", $icsFileObj);
		}else{
			$existingIcsFile->set_content($icsFile);
		}
		return $requestObject;
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


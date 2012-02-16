<?php
namespace Calendar\Commands;
class SubscriptionWrapper{
	private $calendar;
	private $extensions = array();
	private $result = array();

	public function setCalendar(\steam_room $cal){
		if($cal->get_attribute("OBJ_TYPE") == "calendar"){
			$this->calendar=$cal;
		}else{
			throw new \Exception("variable cal isn't a calendar.");
		}

	}
	//TODO:BISHER NUR 2 STUFIG GETESTET,ABER FUNKTIONIERT
	public function getSubscriptions(){
		if(!isset($this->calendar)){
			throw new \Exception("calendar must be set");
		}
		$steamCalendar=$this->calendar->get_attribute("GROUP_CALENDAR");
		$calendarSubscriptions=$this->calendar->get_attribute("CALENDAR_SUBSCRIPTIONS");
		$this->result[$steamCalendar->get_id()]=$steamCalendar;
		$this->extensions[$steamCalendar->get_id()]=$steamCalendar->get_attribute("CALENDAR_OWNER");
		foreach ($calendarSubscriptions as $cs){
			$this->result[$cs->get_id()]=$cs;
		}
		foreach ($this->result as $id=>$calendar){
			$this->extensions[$id] = $calendar->get_attribute("CALENDAR_OWNER");
		}
		foreach($this->extensions as $id => $extension){
			if($id != $steamCalendar->get_id() && $extension instanceof \steam_room && ($extension->get_attribute("OBJ_TYPE") == "calendar")){
				$this->getCalendarSubscriptions($extension);
			}
		}
		$array = array();
		$array["result"]=$this->result;
		$array["extensions"]=$this->extensions;
		return $array;
	}
	private function getCalendarSubscriptions(\steam_room $cal){
		$calendarSubscriptions=$cal->get_attribute("CALENDAR_SUBSCRIPTIONS");
		foreach ($calendarSubscriptions as $calendarSubscription){
			$calendarSubscriptionId=$calendarSubscription->get_id();
			if(!isset($this->extensions[$calendarSubscriptionId])){
				$this->result[$calendarSubscriptionId]=$calendarSubscription;
				$this->extensions[$calendarSubscriptionId]=$calendarSubscription->get_attribute("CALENDAR_OWNER");
			}
		}
	}


}
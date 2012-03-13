<?php
namespace Calendar\Commands;
class AddSubscriptions extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;
	private $calendar;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	//TODO:FUNKTIONIERT FÜR KALENDER, GRUPPEN UND USERKALENDER
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
			isset($this->params[1]) ? $this->calendar = $this->params[1] : "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
			isset($this->params["calendar"]) ? $this->calendar = $this->params["calendar"] : "";
		}

		$currentUser=\lms_steam::get_current_user();
		$currentUserId=$currentUser->get_id();
		$steam=$GLOBALS["STEAM"];
		$steamId=$steam->get_id();

		$objSubscriptionId=$this->id;
		$objSubscription = \steam_factory::get_object($steamId, $objSubscriptionId);

		$objCalendarId = $this->calendar;
		$objCalendar = \steam_factory::get_object($steamId, $objCalendarId);
		$calendarCreatorId = $objCalendar->get_creator()->get_id();
		if(!($objCalendar instanceof \steam_room && $objCalendar->get_attribute("OBJ_TYPE") === "calendar")){
			throw new \Exception("param 1 is not instance of calendar!");
		}

		//Unterscheide, ob Gruppe, Semester oder Kurs
		if($objSubscription instanceof \steam_group){
			$objType = $objSubscription->get_attribute("OBJ_TYPE");
			//DO IT FOR COURSE
			//if($objType=="course"){

			//}else if($objType=="group_tutorial_koala")
			$parent = $objSubscription->get_parent_group();
			$parentName = $parent->get_name();
			if($parentName == "Courses"){
				$steamCalendar=$objSubscription->get_attribute("GROUP_CALENDAR");
				if(self::isSteamCalendarEmpty($steamCalendar)){
					$semesterStart = $objSubscription->get_attribute("SEMESTER_START_DATE");
					$semesterEnd = $objSubscription->get_attribute("SEMESTER_END_DATE");

					$data = array();
					$data["DATE_END_DATE"] = $semesterStart;
					$data["DATE_START_DATE"]= $semesterStart;
					$data["DATE_TITLE"] = "Beginn des Semesters";
					$data["DATE_DESCRIPTION"]= "Semesterbeginn";
					$steamCalendar->add_entry($data);

					$data = array();
					$data["DATE_END_DATE"] = $semesterEnd;
					$data["DATE_START_DATE"]= $semesterEnd;
					$data["DATE_TITLE"] = "Ende des Semesters";
					$data["DATE_DESCRIPTION"]= "Semesterende";
					$steamCalendar->add_entry($data);

				}
				$subscriptions=$objCalendar->get_attribute("CALENDAR_SUBSCRIPTIONS");
				$subscriptions[]=$steamCalendar;
				$objCalendar->set_attribute("CALENDAR_SUBSCRIPTIONS", $subscriptions);
			}else if($parentName == "PublicGroups"){
				$steamCalendar=$objSubscription->get_attribute("GROUP_CALENDAR");
				$subscriptions=$objCalendar->get_attribute("CALENDAR_SUBSCRIPTIONS");
				if(!self::isInSubscriptionArray($steamCalendar, $subscriptions)){
					$subscriptions[]=$steamCalendar;
					$objCalendar->set_attribute("CALENDAR_SUBSCRIPTIONS", $subscriptions);
				}else{
					echo "Already subscribed";
				}

			}else{
				throw new \Exception("Kind of Group is not defined");
			}
		}else if($objSubscription instanceof \steam_room){
			$objType = $objSubscription->get_attribute("OBJ_TYPE");
			if($objType == "calendar"){
				$steamCalendar=$objSubscription->get_attribute("GROUP_CALENDAR");
				$subscriptions=$objCalendar->get_attribute("CALENDAR_SUBSCRIPTIONS");
				if(!self::isInSubscriptionArray($steamCalendar, $subscriptions)){
					$subscriptions[]=$steamCalendar;
					$objCalendar->set_attribute("CALENDAR_SUBSCRIPTIONS", $subscriptions);

				}
			} //INSERT NEW ROOM TYPES HERE

			//} else if($objSubscription instanceof \steam_container){

		}else if($objSubscription instanceof \steam_user){
			if($currentUser->get_id() == $objSubscriptionId){
				$steamCalendar=$objSubscription->get_attribute("USER_CALENDAR");
				$subscriptions = $objCalendar->get_attribute("CALENDAR_SUBSCRIPTIONS");
				if(!self::isInSubscriptionArray($steamCalendar, $subscriptions)){
					$subscriptions[]=$steamCalendar;
					$objCalendar->set_attribute("CALENDAR_SUBSCRIPTIONS", $subscriptions);
				}else{
					echo "Already subscribed";
				}
			}
			else{
				throw new \Exception("Extensiontype can not be subscribed!");
			}
			return $requestObject;

		}
	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;

	}
	private static function isSteamCalendarEmpty(\steam_calendar $steamCalendar){
		return count($steamCalendar->get_date_objects()) == 0;
	}
	private static function isInSubscriptionArray(\steam_calendar $steamCalendar, $subscriptions){
		$steamCalendarId=$steamCalendar->get_id();
		foreach ($subscriptions as $subscription){
			$id=$subscription->get_id();
			if($id == $steamCalendarId){
				return true;
			}
		}
		return false;
	}
}
?>
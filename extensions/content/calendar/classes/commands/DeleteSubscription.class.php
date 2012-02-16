<?php
namespace Calendar\Commands;
class DeleteSubscription extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;
	private $calendar;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

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
		if($calendarCreatorId != $currentUserId){
			throw new \Exception("User is not allowed to subscribe events");
		}
		if($objSubscription instanceof \steam_user){
			$steamCalendar= $objSubscription->get_attribute("USER_CALENDAR");
		}else{
			$steamCalendar= $objSubscription->get_attribute("GROUP_CALENDAR");
		}
		if(!($steamCalendar instanceof \steam_calendar)){
			throw new \Exception("steam_calendar is not set.");
		}
		$steamCalendarId=$steamCalendar->get_id();
		$subscriptions = $objCalendar->get_attribute("CALENDAR_SUBSCRIPTIONS");
		$deletedSubscription=false;
		foreach($subscriptions as $index => $subscription){
			if(($subscription->get_id()) == $steamCalendarId){
				unset($subscriptions[$index]);
				$deletedSubscription=true;
			}
		}
		if(!$deletedSubscription){
			throw new \Exception("Subcription could not deleted. steam_calendar is not subscribed by the calendar!");
		}
		$objCalendar->set_attribute("CALENDAR_SUBSCRIPTIONS", $subscriptions);




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
}
?>
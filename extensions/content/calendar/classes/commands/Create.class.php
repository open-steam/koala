<?php
namespace Calendar\Commands;
class Create extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
		
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		
		$env_room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$calendar= \steam_factory::create_room($GLOBALS["STEAM"]->get_id(),$this->params["name"],$env_room);
		$calendar->set_attribute("OBJ_TYPE", "calendar");
		$steamCalendar= \steam_factory::create_calendar($GLOBALS["STEAM"]->get_id(), $this->params["name"]."_calendar", null);
		$calendar->set_attribute("GROUP_CALENDAR",$steamCalendar);
		$steamCalendar->set_attribute("CALENDAR_OWNER", $calendar);
		$calendar->set_attribute("CALENDAR_SUBSCRIPTIONS", array());
		
		
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		closeDialog();
		location.reload();
		
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>
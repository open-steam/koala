<?php
namespace Calendar\Commands;
class Index extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;

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

	public function frameResponse(\FrameResponseObject $frameResponseObject) {	
		
	
		$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$currentUser = \lms_steam::get_current_user();
		$subscriptionWrapper = new SubscriptionWrapper();
		$subscriptionWrapper->setCalendar($obj);
		$result = $subscriptionWrapper->getSubscriptions();
		$extensions = array();
		$extensions = $result["extensions"];
		$calendars = array();
		$calendars = $result["result"];
		
		$sanctionWrapper= new SanctionWrapper();
		$sanctionWrapper->setExtensions("extensions");
		$sanctions = $sanctionWrapper->getSanction();
		
		//RECHTE UND DATEN KÖNNEN ZUR KALENDERAUSGABE VERWENDET WERDEN
		
		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;

	}
	private static function getSanctionForSemester(\steam_group $semester){
		
	}
}
?>
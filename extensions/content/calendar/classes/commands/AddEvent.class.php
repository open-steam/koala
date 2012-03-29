<?php
namespace Calendar\Commands;
class AddEvent extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand{

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
		$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		if(!($obj instanceof \steam_calendar)){
			throw new \Exception("current object is not type of steam_calendar");
		}
		//Specify your attributes for new date here
		$title = "Fussballtraining";
		$description = "Ausdauerlauf";
		$location = "Fussballplatz";
		$startDate = "2012-03-12";
		$startTime = "17:00";
		$endDate = "2012-03-12";
		$endTime = "17:01";

		$pData = array();


		$pData["DATE_START_DATE"] = str_to_timestamp($startDate, $startTime);
		$pData["DATE_END_DATE"] = str_to_timestamp($endDate, $endTime);
		$pData["DATE_TITLE"] = $title;
		$pData["DATE_DESCRIPTION"] = $description;
		$pData["DATE_LOCATION"] = $location;

		$obj->add_entry($pData);

		return $requestObject;

	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;

	}

}
?>
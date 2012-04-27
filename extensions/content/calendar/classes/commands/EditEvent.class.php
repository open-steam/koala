<?php
namespace Calendar\Commands;
class EditEvent extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand{

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
		if(!($obj instanceof \steam_date)){
			throw new \Exception("current object is not type of steam_date");
		}
		//Specify your attributes for new date here
		$title = "";
		$description = "";
		$location = "";
		$startDate = "";
		$startTime = "";
		$endDate = "";
		$endTime = "";

		if($title !== "" ){
			$obj->set_attribute("DATE_TITLE", $title);
		}
		if($description !== "" ){
			$obj->set_attribute("DATE_DESCRIPTION", $description);
		}
		if($location !== "" ){
			$obj->set_attribute("DATE_LOCATION", $location);
		}
		if($startDate !== "" || $startTime !== "" ){
			$dtStart = str_to_timestamp($startDate, $startTime);
			$obj->set_attribute("DATE_START_DATE", $dtStart);
		}
		if($endDate !== "" || $endTime !== "" ){
			$dtEnd = str_to_timestamp($endDate, $endTime);
			$obj->set_attribute("DATE_START_DATE", $dtEnd);
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

}

?>
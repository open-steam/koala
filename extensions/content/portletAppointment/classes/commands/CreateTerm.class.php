<?php
namespace PortletAppointment\Commands;
class CreateTerm extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $content;
	private $dialog;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["id"];

		$appointmentObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$terms = $appointmentObject->get_attribute("bid:portlet:content");

		$startDateArray = (strpos($params["start_date"], "."))? explode(".", $params["start_date"]) : array("", "", "");
		$endDateArray = (strpos($params["end_date"], "."))? explode(".", $params["end_date"]) : array("", "", "");
		$startTimeArray = (strpos($params["start_time"], ":"))? explode(":", $params["start_time"]) : array("", "");
		$endTimeArray = (strpos($params["end_time"], ":"))? explode(":", $params["end_time"]) : array("", "");

		$startDate = array("day"=> $startDateArray[0], "month" => $startDateArray[1], "year" => $startDateArray[2]);
		$startTime = array("hour" => $startTimeArray[0], "minutes" => $startTimeArray[1]);
		$endDate = array("day"=> $endDateArray[0], "month" => $endDateArray[1], "year" => $endDateArray[2]);
		$endTime = array("hour" => $endTimeArray[0], "minutes" => $endTimeArray[1]);

		if($params["linkurl_open_extern"] == "true"){
			$checkbox = "checked";
		}
		else{
			$checkbox = "";
		}

		//compose term
		$newTerm = array(	"description" => $params["description"],
							"linkurl" => $params["linkurl"],
							"linkurl_open_extern" => $checkbox,
							"location" => $params["location"],
							"start_date" => $startDate,
							"start_time" => $startTime,
							"end_date" => $endDate,
							"end_time" => $endTime,
							"topic" => $topic = $params["topic"] );

		if($terms==""){
			$terms = array();
		}
		$terms[] = $newTerm;
		$appointmentObject->set_attribute("bid:portlet:content", $terms);
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		window.location.reload();
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>

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
                $startDateParam = $params["startDate"];
                $startDateArray = explode(".", $startDateParam);
                                            
                $desc = $params["desc"];
                
		$appointmentObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$terms = $appointmentObject->get_attribute("bid:portlet:content");
		
		
		//get date             
                $currentDay = $startDateArray[0];
                $currentMonth = $startDateArray[1];
                $currentYear = $startDateArray[2];
		
		//new term parts
		$description = "Beschreibung";
		$endDate = array("day"=> "", "month" => "", "year" => "");
		$linkurl = "";
		$location = "";
		$startDate = array("day"=> $currentDay, "month" => $currentMonth, "year" => $currentYear);
		$startTime = array("hour" => "12", "minutes" => "00");
		$topic = "Neuer Termin";
		
		//compose term
		$newTerm = array(	"description" => $description ,
							"end_date" => $endDate,
							"linkurl" => $linkurl,
							"location" => $location,
							"start_date" => $startDate,
							"start_time" => $startTime,
							"topic" => $topic );
		
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
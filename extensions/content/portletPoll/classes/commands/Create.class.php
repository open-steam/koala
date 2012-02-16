<?php
namespace PortletPoll\Commands;
class Create extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $rawHtmlWidget;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		
		$name = $params["title"];
		$column = $params["parent"];
		$version = "1.0";		
		
		//check diffrent types of parameter
		if(is_string($column)){
			$columnObject =  \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$column);
		}else{
			$columnObject = $column;
		}
		
		//get date
		$currentYear = date("Y")."";
		$nextYear = (date("Y")+1)."";
		
		//create
		$pollObject = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $name, $columnObject);
	    
		$pollTopic = "Beschreibung der Abstimmung";
	    $startDate = array("day" => "01","month" => "01","year" => $currentYear,);
	    $endDate = array("day" => "01","month" => "01","year" => $nextYear,);
	    $options = array("Eintrag A","Eintrag B","Eintrag C","Eintrag D","Eintrag E","Eintrag F");
	    $optionsVotecount = array(0,0,0,0,0,0);
	    
	    
	    $pollContent = array(	"end_date" => $endDate,
	    						"options" => $options,
	    						"options_votecount" => $optionsVotecount,
	    						"poll_topic" => $pollTopic,
	    						"start_date" => $startDate,
	    );
	    
	    
	    $pollObject->set_attributes(array(
	        OBJ_DESC => $name,
	        OBJ_TYPE => "container_portlet_bid",
	        "bid:portlet" => "poll",
	        "bid:portlet:version" => $version,
	        "bid:portlet:content" => $pollContent,
	    ));
		
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
		
	}

	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
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
<?php
namespace PortletChronic\Commands;

class Create extends \AbstractCommand implements \IAjaxCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
            $params = $requestObject->getParams();
            $column = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $params["id"]);
		
            //create object
            $chronicPortlet = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "Verlauf", $column);
	    
	    $chronicPortlet->set_attributes(array(
	        OBJ_DESC => "Verlauf",
	        OBJ_TYPE => "container_portlet_bid",
	        "bid:portlet" => "chronic",
	        "bid:portlet:version" => "3.0",
                "PORTLET_CHRONIC_COUNT" => $params["elements"]
	    ));
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
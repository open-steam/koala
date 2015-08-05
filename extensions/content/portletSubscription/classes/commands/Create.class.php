<?php
namespace PortletSubscription\Commands;

class Create extends \AbstractCommand implements \IAjaxCommand, \IIdCommand, \IFrameCommand {

        
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
            $params = $requestObject->getParams();
            if (isset($params["parent"])) {
                $column = $params["parent"];
            } else {
                $column = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $params["id"]);
            }
            
            //create object
            $subscriptionPortlet = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "Änderungen", $column);

            $desc = "Abonnement";
            if (isset($params["title"]) && $params["title"] != "") {
                $desc = $params["title"];
            }
	    $subscriptionPortlet->set_attributes(array(
	        OBJ_DESC => $desc,
	        OBJ_TYPE => "container_portlet_bid",
	        "bid:portlet" => "subscription",
	        "bid:portlet:version" => "3.0",
                "PORTLET_SUBSCRIPTION_OBJECTID" => $params["objectid"],
                "PORTLET_SUBSCRIPTION_TYPE" => $params["type"],
                "PORTLET_SUBSCRIPTION_TIMESTAMP" => time(),
                "PORTLET_SUBSCRIPTION_FILTER" => array(),
                "PORTLET_SUBSCRIPTION_ORDER" => $params["sort"]
	    ));
            
            \ExtensionMaster::getInstance()->getExtensionById("HomePortal")->updateSubscriptions($column->get_environment()->get_id());
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
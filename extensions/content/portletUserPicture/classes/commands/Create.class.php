<?php
namespace PortletUserPicture\Commands;

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
            $chronicPortlet = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "Benutzerbild", $column);
	    
            //username
            $user = $GLOBALS["STEAM"]->get_current_steam_user();
            $username = $user->get_full_name();
            
            if (isset($params["title"])) {
                $title = $params["title"];
            } else {
                $title = $username;
            }
            
	    $chronicPortlet->set_attributes(array(
	        OBJ_DESC => $title,
	        OBJ_TYPE => "container_portlet_bid",
	        "bid:portlet" => "userpicture",
	        "bid:portlet:version" => "3.0"
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
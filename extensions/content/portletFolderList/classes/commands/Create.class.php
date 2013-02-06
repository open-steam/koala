<?php
namespace PortletFolderList\Commands;

class Create extends \AbstractCommand implements \IAjaxCommand, \IIdCommand, \IFrameCommand {

        private $error = false;
        
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
            
            try {
                $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $params["folderid"]);
                if (!$object->check_access_read() || getObjectType($object) !== "room") {
                    $this->error = true;
                    $params["folderid"] = 0;
                }
            } catch (\steam_exception $ex) {
                $this->error = true;
                $params["folderid"] = 0;
            }
            
            //create object
            $folderListPortlet = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "Ordnerinhalt", $column);
	    
            $desc = "Ordnerinhalt";
            if (isset($params["title"])) {
                $desc = $params["title"];
            }
            //if (isset($params["changedate"])) {
            //    if ($params["changedate"] == "on") {
                    $changedate = "true";
            //    } else {
            //        $changedate = "false";
            //    }
            //} else {
            //    $changedate = "false";
            //}
	    $folderListPortlet->set_attributes(array(
	        OBJ_DESC => $desc,
	        OBJ_TYPE => "container_portlet_bid",
	        "bid:portlet" => "folderlist",
	        "bid:portlet:version" => "3.0",
                "PORTLET_FOLDERLIST_FOLDERID" => $params["folderid"],
                "PORTLET_FOLDERLIST_ITEMCOUNT" => $params["elements"],
                "PORTLET_FOLDERLIST_CHANGEDATE" => $changedate
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
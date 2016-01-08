<?php

namespace Postbox\Commands;

class Create extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        if ($this->id === "") {
            $envRoom = $GLOBALS["STEAM"]->get_current_steam_user()->get_workroom();
        } else {
            $envRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        }

        $postboxObject = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $this->params["name"], $envRoom);
        $postboxObject->set_attribute("OBJ_TYPE", "postbox");
        //set this parameter to '' to avoid displaying a '0'
        $postboxObject->set_attribute("postbox:advice", "");
        //if the checkbox (no deadline) is checked
        if ($this->params["checkVal"] === "true") {
            $postboxObject->set_attribute("bid:postbox:deadline", "");
        } else { 
            //check if the given date is of the corect form e.g. '25.08.2014 00:10'
            if(preg_match("/^\d{1,2}\.\d{1,2}\.\d{4} \d{2}:\d{2}/isU", $this->params["deadline"])){
                $postboxObject->set_attribute("bid:postbox:deadline", $this->params["deadline"]);
            } else {
                $postboxObject->set_attribute("bid:postbox:deadline", "");
            }
        }
        
        $innerContainer = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "postbox_container", $postboxObject);
        $innerContainer->set_acquire(false);
        $postboxObject->set_attribute("bid:postbox:container", $innerContainer);
        $postboxObject->set_acquire(false);
        $steamGroupId = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "sTeam")->get_id();
        
        //configure sanctions for inner container
        $requiredSanctionsForInnerContainer =  SANCTION_INSERT | SANCTION_READ;
        if (defined("API_DOUBLE_FILENAME_NOT_ALLOWED") && !API_DOUBLE_FILENAME_NOT_ALLOWED){
            //if API_DOUBLE_FILENAME_NOT_ALLOWED is false, we only need INSERT rights (and don't need to check whether there already exists a file with the same name)
            $requiredSanctionsForInnerContainer = SANCTION_INSERT;
        }
        

        //unset the rights for every oher user then the creator
        //$sanction = $innerContainer->get_sanction();
        //$currentUserId = \lms_steam::get_current_user()->get_id();
        /*foreach ($sanction as $userOrGroupId => $sanct) {
            echo $userOrGroupId;
                $innerContainer->sanction(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $userOrGroupId));
                $innerContainer->sanction_meta(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $userOrGroupId));
        }*/
        
        //$innerContainer->sanction(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $steamGroupId));
        //$innerContainer->sanction_meta(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $steamGroupId));
        
        //the explixitly set the new insert rights
        //$innerContainer->sanction($requiredSanctionsForInnerContainer, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $steamGroupId));
        //$innerContainer->sanction_meta($requiredSanctionsForInnerContainer, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $steamGroupId));
        
        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setJs("closeDialog();
                           location.reload();"
                        );
        
        $ajaxResponseObject->addWidget($jswrapper);
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

}

?>
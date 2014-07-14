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
        if ($this->params["checkVal"] === "true") {
            $postboxObject->set_attribute("bid:postbox:deadline", "");
        } else {
            $postboxObject->set_attribute("bid:postbox:deadline", $this->params["deadline"]);
        }
        
        $innerContainer = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "postbox_container", $postboxObject);
        $innerContainer->set_acquire(false);
        $postboxObject->set_attribute("bid:postbox:container", $innerContainer);
        $postboxObject->set_acquire(false);
        $steamGroupId = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "sTeam")->get_id();
        
        //configure sanctions for inner container
        $requiredSanctionsForInnerContainer = SANCTION_READ | SANCTION_INSERT;
        if (defined("API_DOUBLE_FILENAME_NOT_ALLOWED") && (!(API_DOUBLE_FILENAME_NOT_ALLOWED))){
            $requiredSanctionsForInnerContainer = SANCTION_INSERT;
        }
        
        $innerContainer->sanction($requiredSanctionsForInnerContainer, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $steamGroupId, CLASS_OBJECT));
        $innerContainer->sanction_meta($requiredSanctionsForInnerContainer, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $steamGroupId, CLASS_OBJECT));
        
        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setJs(<<<END
		closeDialog();
		location.reload();
		
END
        );
        $ajaxResponseObject->addWidget($jswrapper);
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

}

?>
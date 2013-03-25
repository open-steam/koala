<?php

namespace Postbox\Commands;

class Release extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
        $postbox = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $container = $postbox->get_attribute("bid:postbox:container");
        $containerInventory = $container->get_inventory();
        foreach ($containerInventory as $ele){
            $ele->move($postbox);
        }
        $container->delete();
        $postbox->set_attribute("OBJ_TYPE", 0);
        
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

}



?>
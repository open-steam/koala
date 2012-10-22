<?php

namespace Forum\Commands;

class CreateTopic extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $objectId = $object->get_id();
        $title = $this->params["title"];
        $content = $this->params["content"];


        if ($title != "") {
            $newTopic = $object->add_thread(rawurlencode($title), stripslashes($content));
        } else {
            $newTopic = $object->add_thread(rawurlencode("Neues Thema"), stripslashes($content));
        }

        // TODO: Add other information
        if (!empty($newTopic)) {
            $newTopic->set_attributes(array(
                "OBJ_DESC" => $title,
                "OBJ_TYPE" => "text_forumthread_bid"
                    ), 0);
        }
        $ajaxResponseObject->setStatus("ok");
        $widget = new \Widgets\JSWrapper();
        $widget->setJs("location.reload();");
        $ajaxResponseObject->addWidget($widget);
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

}

?>
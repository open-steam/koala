<?php

namespace PortletSubscriptionOld\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IIdCommand {

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject->setTitle("Portal");
        $objectId = $requestObject->getId();
        $portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $url = $portlet->get_attribute("OBJ_URL");
        $widget = new \Widgets\RawHtml();
        $widget->setHtml($url);
        $frameResponseObject->addWidget($widget);
        return $frameResponseObject;
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $idResponseObject->addWidget($this->rawHtmlWidget);
        return $idResponseObject;
    }

}

?>
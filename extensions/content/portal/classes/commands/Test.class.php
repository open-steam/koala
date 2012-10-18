<?php

namespace Portal\Commands;

class Test extends \AbstractCommand implements \IFrameCommand {

    

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $pgb = new \Widgets\Progressbar();
        $pgb->setId("2");
        $pgb->setValue(50);
        $pgb->setWidth("200px");
        $widget = new \Widgets\Slider();
        $widget->setId("1");
        $widget->setMax(20);
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($pgb->getHtml());
        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
        
    }
}

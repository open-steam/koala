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

        $colorpicker = new \Widgets\ColorPicker();
        $colorpicker->setId("cp");
        $colorpicker->setLabel("label");
        $colorpicker->setOnChange("alert(1);");
        $colorpicker->setValue("#111111");
        
        $frameResponseObject->addWidget($colorpicker);

        return $frameResponseObject;
    }

    

}

?>
<?php

namespace Explorer\Commands;

class Dummy extends \AbstractCommand implements \IAjaxCommand {

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>

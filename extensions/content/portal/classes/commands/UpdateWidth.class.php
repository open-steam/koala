<?php

namespace Portal\Commands;

class UpdateWidth extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $value;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->value = $this->params["value"];
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $object->set_attribute("bid:portal:column:width", $this->value);
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}
?>


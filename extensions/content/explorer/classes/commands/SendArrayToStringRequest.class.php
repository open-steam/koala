<?php

namespace Explorer\Commands;

class SendArrayToStringRequest extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $value = $oldValue = $this->params["value"];
        $attribute = $this->params["attribute"];

        $value = html_entity_decode($value);

        $array = explode(" ", $value);
        $object->set_attribute($attribute, $array);

        $data["oldValue"] = $oldValue;
        $data["newValue"] = $oldValue;
        $data["error"] = "none";
        $data["undo"] = false;

        $ajaxResponseObject->setData($data);
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>

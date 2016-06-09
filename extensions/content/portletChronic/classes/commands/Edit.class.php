<?php

namespace PortletChronic\Commands;

class Edit extends \AbstractCommand implements \IAjaxCommand {

    private $dialog;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $params = $requestObject->getParams();
        $objectId = $params["portletId"];

        $clearer = new \Widgets\Clearer();

        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Bearbeiten von " . $object->get_attribute("OBJ_DESC"));

        $numberInput = new \Widgets\TextInput();
        $numberInput->setLabel("Sichtbare Objekte");
        $numberInput->setData($object);
        $numberInput->setType("number");
        $numberInput->setMin(1);
        $numberInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_CHRONIC_COUNT"));

        $dialog->addWidget($numberInput);
        $dialog->addWidget($clearer);

        $this->dialog = $dialog;
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($this->dialog);
        return $ajaxResponseObject;
    }

}

?>

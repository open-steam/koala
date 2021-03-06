<?php

namespace PortletMsg\Commands;

class Edit extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {

    private $params;
    private $id;
    private $content;
    private $dialog;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $params = $requestObject->getParams();
        $objectId = $params["portletId"];

        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Bearbeiten der " . $object->get_attribute("OBJ_DESC"));

        $dialog->setPositionX($this->params["mouseX"]);
        $dialog->setPositionY($this->params["mouseY"]);

        $titel = new \Widgets\TextInput();
        $clearer = new \Widgets\Clearer();

        $titel->setLabel("Überschrift");
        $titel->setData($object);
        $titel->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));

        $numberInput = new \Widgets\TextInput();
        $numberInput->setLabel("Sichtbare Meldungen");
        $numberInput->setType("number");
        $numberInput->setMin(1);
        $numberInput->setData($object);
        $numberInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_MSG_COUNT"));

        $startValue = $object->get_attribute("PORTLET_MSG_DISPLAY");
        $displayOption = new \Widgets\DropDownList();
        $displayOption->setLabel("Anzeige");
        $displayOption->setName("displayOption");
        $displayOption->setId("displayOptionId");
        $displayOption->setSaveFunction("sendRequest('databinding', {'id': ".$objectId.", 'attribute': 'PORTLET_MSG_DISPLAY', 'value': ".$displayOption->getId()."}, '', 'data', function(response){dataSaveFunctionCallback(response);});");
        
        $displayOption->addDataEntries(["whole_message" => "Ganze Meldungen", "only_subheadline" => "Überschrift + Unterüberschrift", "only_headline" => "Überschrift"]);
        if (trim($startValue) != "0") {
            $displayOption->setStartValue($startValue);
        } else {
            $displayOption->setStartValue("whole_message");
        }

        $jsWrapper = new \Widgets\RawHtml();
        $jsWrapper->setPostJsCode("$('.widgets_textinput > [type=\"text\"]').css('width', '203px'); $('.widgets_dropdownlist select').css('width', '205px');");
        $dialog->addWidget($jsWrapper);

        $dialog->addWidget($titel);
        $dialog->addWidget($clearer);
        $dialog->addWidget($numberInput);
        $dialog->addWidget($clearer);
        $dialog->addWidget($displayOption);
        $this->dialog = $dialog;
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $idResponseObject->setContent($this->content);
        return $idResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject->setTitle("Portal");
        $frameResponseObject->setContent($this->content);
        return $frameResponseObject;
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        $ajaxResponseObject->addWidget($this->dialog);
        return $ajaxResponseObject;
    }

}

?>

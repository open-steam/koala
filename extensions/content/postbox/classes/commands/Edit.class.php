<?php

namespace Postbox\Commands;

class Edit extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        //compute current datetime
        $currentDay = date("d") . "";
        $currentMonth = date("m") . "";
        $currentYear = date("Y") . "";
        $time = date("H:i") . "";
        $currentDateTime = $currentDay . "." . $currentMonth . "." . $currentYear . " " . $time;

        $dialog = new \Widgets\Dialog();
        $clearer = new \Widgets\Clearer();

        $dialog->setTitle("Eigenschaften");
        $dialog->setWidth(400);

        $dataNameInput = new \Widgets\TextInput();
        $dataNameInput->setLabel("Name der Abgabe");
        $dataNameInput->setData($object);
        $dataNameInput->setContentProvider(new \Widgets\NameAttributeDataProvider("OBJ_NAME", getCleanName($object, -1)));
        $dialog->addWidget($dataNameInput);
        $dialog->addWidget($clearer);

        $attr = $object->get_attribute("bid:postbox:deadline");
        $noDeadline = false;
        if ($attr == "" || $attr == 0) {
            $noDeadline = true;
        }
        $checkbox = new \Widgets\Checkbox();
        $checkbox->setName("noDeadline");
        $checkbox->setLabel("Keine Abgabefrist:");
        $dialog->addWidget($checkbox);

        $datepickerStart = new \Widgets\DatePicker();
        $datepickerStart->setName("deadline");
        $datepickerStart->setLabel("Abgabefrist:");
        $datepickerStart->setTimePicker(true);
        $datepickerStart->setData($object);
        $datepickerStart->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:postbox:deadline"));
        $dialog->addWidget($datepickerStart);
        $dialog->addWidget($clearer);


        $rawHtml = new \Widgets\RawHtml();
        $datepickerStart->setPostJsCode("$('#{$checkbox->getId()}').change(function(){".
                              "if(!this.checked){ ".
                                  "$('#{$datepickerStart->getId()}').show(); ".

                                  "$('#{$datepickerStart->getId()}_label').show(); ".
                                  "$('#{$datepickerStart->getId()}').prop('value', $('#{$datepickerStart->getId()}').attr('data-oldValue'));".
                                  "$('#{$datepickerStart->getId()}').change();".
                                  "$('#{$checkbox->getId()}').removeClass('changed');".

                              "}else{".
                                  "$('#{$datepickerStart->getId()}_label').hide(); ".
                                  "$('#{$datepickerStart->getId()}').hide();".
                                  "$('#{$datepickerStart->getId()}').prop('value', '0');".
                                  "$('#{$datepickerStart->getId()}').change();".
                                  "$('#{$checkbox->getId()}').removeClass('changed');".


                              "}".
                          "}".
                          ");"

            );


        $dialog->addWidget($rawHtml);

        if ($noDeadline) {
            $jsWrapper = new \Widgets\RawHtml();
            $jsWrapper->setPostJsCode("$('#{$datepickerStart->getId()}_label').hide(); ".
                                  "$('#{$datepickerStart->getId()}').hide();".
                                 "$('#{$checkbox->getId()}').prop('checked', true);"
            );

            $dialog->addWidget($jsWrapper);
        }




        $textAreaAdvice = new \Widgets\Textarea();
        $textAreaAdvice->setLabel("Hinweistext");
        $textAreaAdvice->setData($object);
        $textAreaAdvice->setContentProvider(\Widgets\DataProvider::attributeProvider("postbox:advice"));
        $textAreaAdvice->setHeight(100);
        $textAreaAdvice->setWidth(201);

        $dialog->addWidget($textAreaAdvice);

        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {

    }

}

?>

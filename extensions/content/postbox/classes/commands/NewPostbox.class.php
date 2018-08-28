<?php

namespace Postbox\Commands;

class NewPostbox extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
        //current time + one week
        $deadline = time() + 604800;
        $currentDateTime = date("d.m.Y H:i", $deadline) . "";

        $ajaxForm = new \Widgets\AjaxForm();
        $ajaxForm->setSubmitCommand("Create");
        $ajaxForm->setSubmitNamespace("Postbox");
        $html = '<input type="hidden" name="id" value="'.$this->id.'">
                 <input id="cb" type="hidden" name="checkVal" value="false">
                 <script>
                    $(".widgets_textinput_save_button").hide();

                    $(".widgets_datepicker input").val("'.$currentDateTime.'");

                    $(".widgets_checkbox input").change(function(){
                        if(!this.checked){
                            $("#datepicker_overlay").show();
                            $("#cb").val("false");
                        }else{
                            $("#datepicker_overlay").hide();
                            $("#cb").val("true");
                        }
                    });

                    $("input[name=\"noDeadline\"]").css("margin-left", "3px");

                </script>';

        $textInput = new \Widgets\TextInput();
        $textInput->setName("name");
        $textInput->setLabel("Name");
        $textInput->checkIfExisting(true);

        $datePicker = new \Widgets\DatePicker();
        $datePicker->setName("deadline");
        $datePicker->setLabel("Abgabefrist:");
        $datePicker->setTimePicker(true);

        $checkbox = new \Widgets\Checkbox();
        $checkbox->setName("noDeadline");
        $checkbox->setLabel("Keine Abgabefrist:");

        $ajaxForm->setHtml($textInput->getHtml() .'<div style="clear:both;">'.$checkbox->getHtml() ."</div>".'<div id="datepicker_overlay" style="clear:both;">'. $datePicker->getHtml()."</div>".$html."");

        $ajaxForm->setPostJsCode('setTimeout(function(){$("input:text:visible:first").focus();}, 1300);');

        $ajaxResponseObject->setStatus("ok");

        $ajaxResponseObject->addWidget($ajaxForm);

        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        return $frameResponseObject;
    }

}
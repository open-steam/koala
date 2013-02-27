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
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        
        $currentDay = date("d") . "";
        $currentMonth = date("m") . "";
        $currentYear = date("Y") . "";
        $time = $uhrzeit = date("H:i")."";
        
        
        
        $currentDateTime = $currentDay.".".$currentMonth.".".$currentYear." ".$time;
        

        $ajaxForm = new \Widgets\AjaxForm();
        $ajaxForm->setSubmitCommand("Create");
        $ajaxForm->setSubmitNamespace("Postbox");
        $html = '          


<input type="hidden" name="id" value="{'.$this->id.'}">
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
                }
            );
            
</script>
        
<br>		';

        $textInput = new \Widgets\TextInput();
        $textInput->setName("name");
        $textInput->setLabel("Name");

        $datePicker = new \Widgets\DatePicker();
        $datePicker->setName("deadline");
        $datePicker->setLabel("Abgabefrist");
        $datePicker->setTimePicker(true);

        $checkbox = new \Widgets\Checkbox();
        $checkbox->setName("noDeadline");
        $checkbox->setLabel("Keine Abgabefrist:");
       

        $ajaxForm->setHtml($textInput->getHtml() .$checkbox->getHtml() .'<div id="datepicker_overlay">'. $datePicker->getHtml()."</div>".$html."");

        $ajaxForm->setPostJsCode('setTimeout(function(){$("input:text:visible:first").focus();}, 1300);');

        $ajaxResponseObject->setStatus("ok");

        $ajaxResponseObject->addWidget($ajaxForm);


        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        return $frameResponseObject;
    }

}

?>
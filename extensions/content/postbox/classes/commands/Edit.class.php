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
        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);

        //compute current datetime
        $currentDay = date("d") . "";
        $currentMonth = date("m") . "";
        $currentYear = date("Y") . "";
        $time = date("H:i") . "";
        $currentDateTime = $currentDay . "." . $currentMonth . "." . $currentYear . " " . $time;

        $dialog = new \Widgets\Dialog();
        $clearer = new \Widgets\Clearer();

        $dataNameInput = new \Widgets\TextInput();
        $dataNameInput->setLabel("Name der Abgabe");
        $dataNameInput->setData($obj);
        $dataNameInput->setContentProvider(new NameAttributeDataProvider("OBJ_NAME", getCleanName($obj, -1)));
        $dialog->addWidget($dataNameInput);
        $dialog->addWidget($clearer);

        $attr = $obj->get_attribute("bid:postbox:deadline");
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
        $datepickerStart->setLabel("Abgabefrist");
        $datepickerStart->setTimePicker(true);
        $datepickerStart->setData($obj);
        //$datepickerStart->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:postbox:deadline"));
        if ($noDeadline) {
            $datepickerValue = $currentDateTime;
        } else {
            $datepickerValue = trim($attr);
        }
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml(<<<END
                <div id="datepicker-box">{$datepickerStart->getHtml()}</div>
                <script> $(".widgets_datepicker input").val("{$datepickerValue}");
                    $("#datepicker-box .widgets_datepicker input").change(function(){
                        sendRequest('databinding', {'id': {$this->id}, 'attribute': 'bid:postbox:deadline', 'value': this.value}, '', 'data');
                            $(this).addClass('changed')});
                    $(".widgets_checkbox input").change(function(){
                if(!this.checked){ 
                    $("#datepicker-box").show();                  
                 }else{
                    $("#datepicker-box").hide();  
                    sendRequest('databinding', {'id': {$this->id}, 'attribute': 'bid:postbox:deadline', 'value': ''}, '', 'data');
                         
                 }
                }
            );   
                </script>
END
        );
        $dialog->addWidget($rawHtml);
   //     $dialog->addWidget($clearer);
        if ($noDeadline) {
            $jsWrapper = new \Widgets\RawHtml();
            $jsWrapper->setPostJsCode(<<<END
            $("input[name=noDeadline]").attr("checked", true);
            $("#datepicker-box").hide();
           
END
            );

            $dialog->addWidget($jsWrapper);
            $dialog->setTitle("Eigenschaften von »" . getCleanName($obj) . "«<br> (Abgabefach)");
        }
        $adviceText = $obj->get_attribute("postbox:advice");
        $textarea = new \Widgets\RawHtml();
        $textarea->setHtml(<<<END
                 <div style="margin:3px;">Hinweistext:</div>
            <textarea id="adviceArea" onchange="updateText();return false;" style="margin:3px;width:325px;height:100px;"></textarea>
                <script>$('#adviceArea').val("{$adviceText}");
                        function updateText(){
                            var value = $('#adviceArea').val();
                 sendRequest('databinding', {'id': {$this->id}, 'attribute': 'postbox:advice', 'value': value}, '', 'data');
                            
                }
   </script>
END
        );
        $dialog->addWidget($textarea);

        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

}

class NameAttributeDataProvider extends \Widgets\AttributeDataProvider {

    public function getUpdateCode($object, $elementId, $successMethode = "") {
        if (is_int($object)) {
            $objectId = $object;
        } else {
            $objectId = $object->get_id();
        }
        $function = ($successMethode != "") ? ", function(response){{$successMethode}({$elementId}, response);}" : ",''";
        return <<< END
	sendRequest('databinding', {'id': {$objectId}, 'attribute': 'OBJ_DESC', 'value': ''}, '', 'data');
	sendRequest('databinding', {'id': {$objectId}, 'attribute': '{$this->getAttribute()}', 'value': value}, '', 'data'{$function});
END;
    }

}

?>
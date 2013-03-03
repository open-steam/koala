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
        $js = '';
        if ($noDeadline) {
            $js .= '$("input[name=' . 'noDeadline' . ']").attr("checked", true);';
            $js .= '$("input[name=' . 'deadline' . ']").attr("disabled", true);';
            
        }
        $dialog->addWidget($checkbox);


        $datepickerStart = new \Widgets\DatePicker();
        $datepickerStart->setName("deadline");
        $datepickerStart->setLabel("Abgabefrist");
        $datepickerStart->setData($obj);
        $datepickerStart->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:postbox:deadline"));
        $dialog->addWidget($datepickerStart);
        $dialog->addWidget($clearer);
        $jsWrapper = new \Widgets\RawHtml();
        $jsWrapper->setPostJsCode(<<<END
            $("input[name=noDeadline]").attr("checked", true);
            $("input[name=deadline]").attr("disabled", true);
                
END
                
                
                );
        $dialog->addWidget($jsWrapper);

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
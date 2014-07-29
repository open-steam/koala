<?php

namespace Ellenberg\Commands;

class NewEllenberg extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
        $ajaxForm = new \Widgets\AjaxForm();
        //who we are and where we want to go
        $ajaxForm->setSubmitNamespace("Ellenberg");
        $ajaxForm->setSubmitCommand("Create");
        
        //add the id of the folder where we are
        $html = '<input type="hidden" name="id" value="{'.$this->id.'}">';

        //generate the input field for the name
        $textInput = new \Widgets\TextInput();
        $textInput->setName("name");
        $textInput->setLabel("Name");
        
        //and put both together
        $ajaxForm->setHtml($html." ".$textInput->getHtml());

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
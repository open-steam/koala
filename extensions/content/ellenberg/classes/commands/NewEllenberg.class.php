<?php

namespace Ellenberg\Commands;
//this class provides the popup that shows up if you want to create a new ellenberg-object in the explorer
class NewEllenberg extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

    private $params;
    private $id;
    private $ajaxNamespace = "Ellenberg";
    private $ajaxCommand ="Create";

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
        $ajaxForm->setSubmitNamespace($this->ajaxNamespace);
        $ajaxForm->setSubmitCommand($this->ajaxCommand);
        
        //add the id of the folder where we are at the moment
        $html = '<input type="hidden" name="id" value="'.$this->id.'">';

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
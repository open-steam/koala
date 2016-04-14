<?php

namespace Explorer\Commands;

class Sanctions extends \AbstractCommand implements \IAjaxCommand {




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

        $loader = new \Widgets\Loader();
        $loader->setWrapperId("sanctionsWrapper");
        $loader->setMessage("Lade Rechtedialog ...");
        $loader->setCommand("sanctionsContent");
        $loader->setParams(array("id" => $this->id));
        $loader->setElementId("sanctionsContent");
        $loader->setNamespace("Explorer");
        $loader->setType("popup");

        $rawHtml = new \Widgets\RawHtml();
        
        $rawHtml->setHtml("<div id=\"sanctionsContent\">" . $loader->getHtml() . "</div>");
        $rawHtml->addWidget($loader);
        
        
        $ajaxResponseObject->addWidget($rawHtml);
        $ajaxResponseObject->setStatus("ok");
        
        return $ajaxResponseObject;
    }

}

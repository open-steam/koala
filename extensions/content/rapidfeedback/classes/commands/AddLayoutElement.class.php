<?php

namespace Rapidfeedback\Commands;

class AddLayoutElement extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        switch (intval($this->params["layoutType"])) {
            case "7":
                $newelement = new \Rapidfeedback\Model\DescriptionLayoutElement();
                $newelement->setDescription(rawurldecode($this->params["description"]));
                break;
            case "8":
                $newelement = new \Rapidfeedback\Model\HeadlineLayoutElement();
                $newelement->setHeadline(rawurldecode($this->params["headline"]));
                break;
            case "9":
                $newelement = new \Rapidfeedback\Model\PageBreakLayoutElement();
                break;
            case "10":
                $newelement = new \Rapidfeedback\Model\JumpLabel();
                $newelement->setFrom($this->params["from"]);
                $newelement->setTo($this->params["to"]);
                break;
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($newelement->getEditHTML($this->params["layoutID"]));

        $ajaxResponseObject->addWidget($rawHtml);
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>
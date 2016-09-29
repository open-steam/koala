<?php

namespace Questionnaire\Commands;

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
                $newelement = new \Questionnaire\Model\DescriptionLayoutElement();
                $newelement->setDescription(rawurldecode($this->params["description"]));
                break;
            case "8":
                $newelement = new \Questionnaire\Model\HeadlineLayoutElement();
                $newelement->setHeadline(rawurldecode($this->params["headline"]));
                break;
            case "9":
                $newelement = new \Questionnaire\Model\PageBreakLayoutElement();
                break;
            case "10":
                $newelement = new \Questionnaire\Model\JumpLabel();
                $newelement->setText(rawurldecode($this->params["text"]));
                $newelement->setTo(rawurldecode($this->params["to"]));
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

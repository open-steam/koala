<?php

namespace PortletSubscriptionOld\Commands;

class CreateNewForm extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $content;
    private $dialog;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        $ajaxResponseObject->setStatus("ok");

        $ajaxForm = new \Widgets\AjaxForm();
        $ajaxForm->setSubmitCommand("Create");
        $ajaxForm->setSubmitNamespace("PortletSubscription");

        $titelInput = new \Widgets\TextInput();
        $titelInput->setLabel("Überschrift");
        $titelInput->setName("name");
        $html = $titelInput->getHtml();
        
        $descriptionInput = new \Widgets\TextInput();
        $descriptionInput->setLabel("Name des Kalenders");
        $descriptionInput->setName("calendar");
        $html .= $descriptionInput->getHtml();
        
        $html .= '<input type="hidden" name="parentId" value="' . $this->id . '">';

        $ajaxForm->setHtml($html);
        $ajaxResponseObject->addWidget($ajaxForm);
        return $ajaxResponseObject;
    }

}

?>
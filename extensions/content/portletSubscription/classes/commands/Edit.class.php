<?php

namespace PortletSubscription\Commands;

//Das edit menü auch auf ein AJAX Form umbasteln, damit ich überprüfen kann, ob die neue ID gültig ist, befor sie gesetzt wird und nicht erst in der success methode
class Edit extends \AbstractCommand implements \IAjaxCommand {

    private $dialog;
    private $params;
    private $ajaxResponseObject;
    private $content;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        $this->portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["portletId"]);

        $this->dialog = new \Widgets\Dialog();

        $this->dialog->setWidth(400);
        $this->dialog->setTitle("Bearbeiten von »" . getCleanName($this->portlet) . "«");

        $this->content = \PortletSubscription::getInstance()->loadTemplate("form.template.html");
        $css = \PortletSubscription::getInstance()->readCSS("form.css");
        $this->content->setVariable("CSS", $css);

        $this->content->setVariable("FORM_TITLE", $this->portlet->get_attribute("OBJ_NAME"));
        $this->content->setVariable("FORM_OBJECT_ID", $this->portlet->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID"));

        $subscriptionType = array(
            604800 => "1 Woche",
            1209600 => "2 Wochen",
            1814400 => "3 Wochen",
            2419200 => "4 Wochen",
            0 => "Unbegrenzt"
        );
        foreach ($subscriptionType as $key => $value) {
            $this->content->setCurrentBlock("FORM_SUBSCRIPTION_TYPE");
            $this->content->setVariable("KEY", $key);
            $this->content->setVariable("VALUE", $value);
            if ($this->portlet->get_attribute("PORTLET_SUBSCRIPTION_TYPE") == $key)
                $this->content->setVariable("SELECTED", "selected=\"selected\"");
            $this->content->parse("FORM_SUBSCRIPTION_TYPE");
        }

        $subscriptionOrder = array(
            0 => "Älteste Änderungen zuerst anzeigen",
            1 => "Neueste Änderungen zuerst anzeigen"
        );

        foreach ($subscriptionOrder as $key => $value) {
            $this->content->setCurrentBlock("FORM_SUBSCRIPTION_ORDER");
            $this->content->setVariable("KEY", $key);
            $this->content->setVariable("VALUE", $value);
            if ($this->portlet->get_attribute("PORTLET_SUBSCRIPTION_ORDER") == $key)
                $this->content->setVariable("SELECTED", "selected=\"selected\"");
            $this->content->parse("FORM_SUBSCRIPTION_ORDER");
        }

        $this->dialog->setSaveAndCloseButtonJs("sendRequest('EditSubscribedElement', "
                . "{'newSubscribedElementId': $('#objectId').val(), "
                . "'portletSubscriptionTitle': $('#title').val(), "
                . "'portletSubscriptionElementId':{$this->portlet->get_id()}, "
                . "'portletSubscriptionType': $('#portletSubscriptionType').val(),"
                . "'portletSubscriptionOrder': $('#portletSubscriptionOrder').val()"
                . "}, 'wizard', 'wizard', function(response){location.reload()}, null, 'portletSubscription');");

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($this->content->get());
        $this->dialog->addWidget($rawHtml);
        $ajaxResponseObject->setStatus("ok");

        $ajaxResponseObject->addWidget($this->dialog);

        return $ajaxResponseObject;
    }

}

<?php

namespace PortletSubscription\Commands;

class EditSubscribedElement extends \AbstractCommand implements \IAjaxCommand {

    private $dialog;
    private $newSubscribedElementId;
    private $portletSubscriptionElementId;
    private $portletSubscriptionElement;
    private $portletSubscriptionTitle;
    private $portletSubscriptionType;
    private $portletSubscriptionOrder;

    public function validateData(\IRequestObject $requestObject) {

        $params = $requestObject->getParams();

        $this->newSubscribedElementId = trim($params["newSubscribedElementId"]);
        $this->portletSubscriptionElementId = trim($params["portletSubscriptionElementId"]);
        $this->portletSubscriptionTitle = trim($params["portletSubscriptionTitle"]);
        $this->portletSubscriptionType = trim($params["portletSubscriptionType"]);
        $this->portletSubscriptionOrder = trim($params["portletSubscriptionOrder"]);

        return true;
    }

    public function processData(\IRequestObject $requestObject) {

        $this->portletSubscriptionElement = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->portletSubscriptionElementId);

        //the subscribed object has has changed, now reset the subscription object
        if ($this->portletSubscriptionElement->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID") != $this->newSubscribedElementId) {

            $this->portletSubscriptionElement->set_attribute("PORTLET_SUBSCRIPTION_OBJECTID", $this->newSubscribedElementId);
            $this->portletSubscriptionElement->set_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP", time());
            $this->portletSubscriptionElement->set_attribute("PORTLET_SUBSCRIPTION_FILTER", array());

            $currentContent = \PortletSubscription\Commands\Create::getCurrentContent(\steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->newSubscribedElementId));
            $this->portletSubscriptionElement->set_attribute("PORTLET_SUBSCRIPTION_CONTENT", $currentContent);

        }


        $this->portletSubscriptionElement->set_attribute("OBJ_NAME", $this->portletSubscriptionTitle);


        if ($this->portletSubscriptionType != "" && is_numeric($this->portletSubscriptionType)) {
            $this->portletSubscriptionElement->set_attribute("PORTLET_SUBSCRIPTION_TYPE", $this->portletSubscriptionType);
        }

        if ($this->portletSubscriptionOrder == "0" || $this->portletSubscriptionOrder == "1") {
            $this->portletSubscriptionElement->set_attribute("PORTLET_SUBSCRIPTION_ORDER", $this->portletSubscriptionOrder);
        }
    }

    private function securityChecks() {
        if (!$this->portletSubscriptionElement->check_access_read())
            return false;

        $this->newPortletSubscriptionElement = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->newSubscribedElementId);
        if (!$this->newPortletSubscriptionElement instanceof \steam_object)
            return false;

        if (!$this->newPortletSubscriptionElement->check_access_read())
            return false;

        return true;
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>
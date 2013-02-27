<?php
namespace PortletSubscription\Commands;

class Index extends \AbstractCommand implements \IIdCommand, \IFrameCommand {

    private $contentHtml;
    private $portlet;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $objectId = $requestObject->getId();
        $portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $this->portlet = $portlet;
        $params = $requestObject->getParams();
        $column = $portlet->get_environment();
        $width = $column->get_attribute("bid:portal:column:width");
        if (strpos($width, "px") == TRUE) {
            $width = substr($width, 0, count($width)-3);
        }
        
        //icon
        $referIcon = \Portal::getInstance()->getAssetUrl() . "icons/refer_white.png";

        //reference handling
        if (isset($params["referenced"]) && $params["referenced"] == true) {
            $portletIsReference = true;
            $referenceId = $params["referenceId"];
        } else {
            $portletIsReference = false;
        }

        $portletName = getCleanName($portlet);
        $portletInstance = \PortletSubscription::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletPath . "/ui/html/index.template.html");
        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());
        
        //headline
        $tmpl->setCurrentBlock("BLOCK_FOLDER_HEADLINE");
        $tmpl->setVariable("HEADLINE", $portletName);

        //refernce icon
        if ($portletIsReference) {
            $envId = $portlet->get_environment()->get_environment()->get_id();
            $envUrl = PATH_URL . "portal/index/" . $envId;
            $tmpl->setVariable("REFERENCE_ICON", "<a href='{$envUrl}' target='_blank'><img src='{$referIcon}'></a>");
        }

        if (!$portletIsReference) {
            $popupmenu = new \Widgets\PopupMenu();
            $popupmenu->setData($portlet);
            $popupmenu->setNamespace("PortletSubscription");
            $popupmenu->setElementId("portal-overlay");
            $popupmenu->setParams(array(array("key" => "portletObjectId", "value" => $portlet->get_id())));
            $popupmenu->setCommand("GetPopupMenuHeadline");
            $tmpl->setVariable("POPUPMENU_HEADLINE", $popupmenu->getHtml());
        } else {
            $popupmenu = new \Widgets\PopupMenu();
            $popupmenu->setData($portlet);
            $popupmenu->setNamespace("Portal");
            $popupmenu->setElementId("portal-overlay");
            $popupmenu->setParams(array(array("key" => "sourceObjectId", "value" => $portlet->get_id()),
                array("key" => "linkObjectId", "value" => $referenceId)
            ));
            $popupmenu->setCommand("PortletGetPopupMenuReference");
            $tmpl->setVariable("POPUPMENU_HEADLINE", $popupmenu->getHtml());
        }

        if (trim($portletName) == "") {
            $tmpl->setVariable("HEADLINE_CLASS", "headline editbutton");
        } else {
            $tmpl->setVariable("HEADLINE_CLASS", "headline");
        }
        $tmpl->parse("BLOCK_FOLDER_HEADLINE");
        
        try {
            $subscriptionObjectID = $portlet->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID");
            $subscriptionObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $subscriptionObjectID);
        } catch (\steam_exception $ex) {
            $subscriptionObject = "";
        }
        
        if ($subscriptionObject instanceof \steam_object && $subscriptionObject->check_access_read()) {
            $updates = $this->collectUpdates($subscriptionObject, $portlet);
            if (count($updates) === 0) {
                $tmpl->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
                $tmpl->setVariable("SUBSCRIPTION_ELEMENT_HTML", "<h3>Keine Neuigkeiten</h3>");
                $tmpl->parse("BLOCK_SUBSCRIPTION_ELEMENT");
            } else {
                foreach ($updates as $update) {
                    $tmpl->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
                    $tmpl->setVariable("SUBSCRIPTION_ELEMENT_HTML", $update[2]);
                    $tmpl->parse("BLOCK_SUBSCRIPTION_ELEMENT");
                }
            }
        } else {
            $tmpl->setCurrentBlock("BLOCK_SUBSCRIPTION_ELEMENT");
            $tmpl->setVariable("SUBSCRIPTION_ELEMENT_HTML", "Error Objekt ID");
            $tmpl->parse("BLOCK_SUBSCRIPTION_ELEMENT");
        }
        
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($tmpl->get());
        $this->contentHtml = $rawHtml;
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $idResponseObject->addWidget($this->contentHtml);
        return $idResponseObject;
    }
    
    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject->addWidget($this->contentHtml);
	return $frameResponseObject;
    }
    
    private function collectUpdates($subscriptionObject, $portlet) {
        if ($portlet->get_attribute("PORTLET_SUBSCRIPTION_TYPE") == "0") {
            if ($portlet->check_access_write()) {
                $private = TRUE;
                $timestamp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP");
                $filterHelp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_FILTER");
                $filter = array();
                foreach ($filterHelp as $filterElement) {
                    $filter[] = $filterElement[1];
                }
            } else {
                $private = FALSE;
                $timestamp = "1209600";
                $filter = array();
            }
        } else {
            $private = FALSE;
            $timestamp = time() - intval($portlet->get_attribute("PORTLET_SUBSCRIPTION_TYPE"));
            $filter = array();
        }
        $updates = array();
        if (getObjectType($subscriptionObject) === "forum") {
            $forumSubscription = new \PortletSubscription\Subscriptions\ForumSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter);
            $updates = $forumSubscription->getUpdates();
        } else if (getObjectType($subscriptionObject) === "wiki") {
            $wikiSubscription = new \PortletSubscription\Subscriptions\WikiSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter);
            $updates = $wikiSubscription->getUpdates();
        }
        
        usort($updates, "sortSubscriptionElements");
        if ($portlet->get_attribute("PORTLET_SUBSCRIPTION_ORDER") == "1") {
            $updates = array_reverse($updates);
        }
        return $updates;
    }
}
?>
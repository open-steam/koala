<?php
namespace PortletSubscription\Commands;

class HideItem extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
        private $timestamp;
        private $objectID;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
                $this->timestamp = $this->params["timestamp"];
                $this->objectID = $this->params["objectID"];
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
            $portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            if ($portlet instanceof \steam_object && $portlet->check_access_write()) {
                try {
                    $subscriptionObjectID = $portlet->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID");
                    $subscriptionObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $subscriptionObjectID);
                } catch (\steam_exception $ex) {
                    $subscriptionObject = "";
                }

                if ($subscriptionObject instanceof \steam_object && $subscriptionObject->check_access_read()) {
                    if ($portlet->get_attribute("PORTLET_SUBSCRIPTION_TYPE") == "0") {
                        if ($portlet->check_access_write()) {
                            $private = TRUE;
                            $timestamp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP");
                            $filter = $portlet->get_attribute("PORTLET_SUBSCRIPTION_FILTER");
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
                    $type = getObjectType($subscriptionObject);
                    if ($type === "forum") {
                        $forumSubscription = new \PortletSubscription\Subscriptions\ForumSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter);
                        $updates = $forumSubscription->getUpdates();
                    } else if ($type === "wiki") {
                        $wikiSubscription = new \PortletSubscription\Subscriptions\WikiSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter);
                        $updates = $wikiSubscription->getUpdates();
                    } else if ($type === "room") {
                        $folderSubscription = new \PortletSubscription\Subscriptions\FolderSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter);
                        $updates = $folderSubscription->getUpdates();
                    } else if ($type === "document" && strstr($subscriptionObject->get_attribute(DOC_MIME_TYPE), "text")) {
                        $documentSubscription = new \PortletSubscription\Subscriptions\DocumentSubscription($portlet, $subscriptionObject, $private, $timestamp, $filter);
                        $updates = $documentSubscription->getUpdates();
                    }

                    usort($updates, "sortSubscriptionElements");
                    
                    $filter = $portlet->get_attribute("PORTLET_SUBSCRIPTION_FILTER");
                    $timestamp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP");
                    $filter[] = array($this->timestamp, $this->objectID);
                    usort($filter, "sortSubscriptionElements");
                    
                    $count = 0;
                    while (count($filter) > 0 && count($updates) > 0 && ($filter[$count][1] == $updates[$count][1])) {
                        $timestamp = $filter[$count][0];
                        unset($filter[$count]);
                        $count++;
                    }
                    $filter = array_values($filter);
                    
                    $portlet->set_attribute("PORTLET_SUBSCRIPTION_FILTER", $filter);
                    $portlet->set_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP", $timestamp);
                }
            }
        
            $jsWrapper = new \Widgets\JSWrapper();
            $js= "$('#" . $this->params["hide"] . "').hide();";
            //$js .= "if ($('#" . $this->id . " div').children('div:visible').length == 1) $('#" . $this->id . "').append('<h3>Keine Neuigkeiten</h3>');";
            $jsWrapper->setJs($js);
            
            $ajaxResponseObject->addWidget($jsWrapper);
            $ajaxResponseObject->setStatus("ok");
            return $ajaxResponseObject;
        }
}
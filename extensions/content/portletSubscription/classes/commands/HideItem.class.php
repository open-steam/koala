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
            $portletInstance = \PortletSubscription::getInstance();
            $portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
            if ($portlet instanceof \steam_object && $portlet->check_access_write()) {
                try {
                    $subscriptionObjectID = $portlet->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID");
                    $subscriptionObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $subscriptionObjectID);
                } catch (\steam_exception $ex) {
                    $subscriptionObject = "";
                }

                if ($subscriptionObject instanceof \steam_object && $subscriptionObject->check_access_read()) {
                    $updates = $portletInstance->calculateUpdates($subscriptionObject, $portlet, false);

                    usort($updates, "sortSubscriptionElements");
                    
                    $filter = $portlet->get_attribute("PORTLET_SUBSCRIPTION_FILTER");
                    $timestamp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP");
                    $filter[] = array($this->timestamp, $this->objectID);
                    usort($filter, "sortSubscriptionElements");
                    
                    $count = 0;
                    while (isset($filter[$count]) && isset($updates[$count]) && ($filter[$count][0] == $updates[$count][0]) && ($filter[$count][1] == $updates[$count][1])) {
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
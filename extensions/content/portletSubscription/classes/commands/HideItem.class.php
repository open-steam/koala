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
            
            //if we can edit the portlet
            if ($portlet instanceof \steam_object && $portlet->check_access_write()) {
                //try to generate the object from the parameter PORTLET_SUBSCRIPTION_OBJECTID
                try {
                    $subscriptionObjectID = $portlet->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID");
                    $subscriptionObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $subscriptionObjectID);
                } catch (\steam_exception $ex) {
                    $subscriptionObject = "";
                }
                //if the generation worked
                if ($subscriptionObject instanceof \steam_object && $subscriptionObject->check_access_read()) {
                    
                    //get the updates without any filtering
                    $updates = $portletInstance->calculateUpdates($subscriptionObject, $portlet, false);

                    //sort them with our own strategy (depending on the first value in the array)
                    usort($updates, "sortSubscriptionElements");
                    
                    //get the filter and the last timestamp before which everything is filtered out
                    $filter = $portlet->get_attribute("PORTLET_SUBSCRIPTION_FILTER");
                    $timestamp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP");
                    
                    //add the new filtering to the filters
                    $filter[] = array($this->timestamp, $this->objectID);
                    usort($filter, "sortSubscriptionElements");
                    
                    //clean up the filter list (if the timestamp and the object id is equal in the filter and in the calculated updates, increase the timestamp for the object and remove the filder)
                    
                    $count = 0;
                    while (isset($filter[$count]) && isset($updates[$count]) && ($filter[$count][0] == $updates[$count][0]) && ($filter[$count][1] == $updates[$count][1])) {
                        $timestamp = $filter[$count][0];
                        unset($filter[$count]);
                        $count++;
                    }
                    //if a newer notification should be hidden while older notifications should still exist, filter the newer out
                    $filter = array_values($filter);
                   
                    //save back the variables to the object
                    $portlet->set_attribute("PORTLET_SUBSCRIPTION_FILTER", $filter);
                    $portlet->set_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP", $timestamp);
                    
                    
                    //now we try to remove a notification for a deleted object, if the user wants to hide it
                    $formerContent = $portlet->get_attribute("PORTLET_SUBSCRIPTION_CONTENT");
                    //if the objectID is in the folderlist and the timestamp of the notofication is -1 (not possible for changes, but only for deletions)
                    if(array_key_exists($this->objectID, $formerContent) && $this->timestamp == -1){
                        unset($formerContent[$this->objectID]);
                    }
                    //save back the modified folderlist
                    $portlet->set_attribute("PORTLET_SUBSCRIPTION_CONTENT", $formerContent);
                }
                    
                
            }
        
            //hide the html-item 
            $jsWrapper = new \Widgets\JSWrapper();
            $js= "$('#" . $this->params["hide"] . "').hide();";
            //$js .= "if ($('#" . $this->id . " div').children('div:visible').length == 1) $('#" . $this->id . "').append('<h3>Keine Neuigkeiten</h3>');";
            $jsWrapper->setJs($js);
            
            $ajaxResponseObject->addWidget($jsWrapper);
            $ajaxResponseObject->setStatus("ok");
            return $ajaxResponseObject;
        }
}
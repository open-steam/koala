<?php

namespace PortletSubscription\Commands;

class HideItem extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $itemTimestamp;
    private $objectID;
    private $portletID;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->portletID = $this->params["portletID"];
        $this->itemTimestamp = $this->params["timestamp"];
        $this->objectID = $this->params["objectID"];
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $portletInstance = \PortletSubscription::getInstance();
        $portlet = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->portletID);

        //if we can edit the portlet
        if ($portlet instanceof \steam_object && $portlet->check_access_write()) {
            //try to generate the object from the parameter PORTLET_SUBSCRIPTION_OBJECTID
            try {

                $subscriptionObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $portlet->get_attribute("PORTLET_SUBSCRIPTION_OBJECTID"));
            } catch (\steam_exception $ex) {
                $subscriptionObject = "";
            }
            //if the generation worked
            if ($subscriptionObject instanceof \steam_object && $subscriptionObject->check_access_read()) {

                //get the updates without any filtering
                $updates = $portletInstance->calculateUpdates($subscriptionObject, $portlet, false);

                //sort them with our own strategy (depending on the first value in the array which is the timestamp of the change)
                usort($updates, "sortSubscriptionElements");

                //get the filter and the last timestamp before which everything is filtered out
                $filter = $portlet->get_attribute("PORTLET_SUBSCRIPTION_FILTER");
                $timestamp = $portlet->get_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP");
                $formerContent = $portlet->get_attribute("PORTLET_SUBSCRIPTION_CONTENT");

                //the user wants to hide all notifications for this subscribed object
                if ($this->objectID == -1) {
                    $filter = array();
                    $timestamp = $this->itemTimestamp;
                    $formerContent = \PortletSubscription\Commands\Create::getCurrentContent($subscriptionObject);
                }

                //add the new filtering to the filters if it is an normal notification, no deletion
                if ($this->itemTimestamp > 1 && $this->objectID > 0) {
                    //simply add the to be hidden notification to the filter
                    //we do not clean up the filter here but only when the user views the updte page and there is no update to show we clean the whole filter
                    //and sace a lot of sorting strategies, overhead and complexity
                    $filter[] = array($this->itemTimestamp, $this->objectID);
                    
                    //if a newer notification should be hidden while older notifications of other objects should still exist, filter the newer out
                    $filter = array_values($filter);
                }


                //now we try to remove a notification for a deleted object, if the user wants to hide it
                //if the objectID is in the folderlist and the timestamp of the notofication is -1 (not possible for changes, but only for deletions)
                if (array_key_exists($this->objectID, $formerContent) && $this->itemTimestamp == -1) {
                    //delete a possible entry in the filter, not necessary, but okay here
                    foreach ($filter as $id => $filterElement) {
                        if ($filterElement[1] == $this->objectID) {
                            unset($filter[$id]);
                        }
                    }
                    unset($formerContent[$this->objectID]);
                } else if (!array_key_exists($this->objectID, $formerContent) && $this->itemTimestamp == -1) {

                    //if the object is not in the folderlist but it is in the inventory of the container, it is a new object
                    //we add it to the known inventory of the folder
                    $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->objectID);
                    $formerContent[$this->objectID] = array("name" => $object->get_attribute(OBJ_NAME));
                }

                //save back the variables to the object
                $portlet->set_attribute("PORTLET_SUBSCRIPTION_FILTER", $filter);
                $portlet->set_attribute("PORTLET_SUBSCRIPTION_TIMESTAMP", $timestamp);
                $portlet->set_attribute("PORTLET_SUBSCRIPTION_CONTENT", $formerContent);

                //hide the html-item 
                $jsWrapper = new \Widgets\JSWrapper();
                $js = "";

                if ($this->objectID == -1) {
                    $jsSelector = "$('#" . $portlet->get_id() . "').children('div').hide();"
                            . "$('#" . $portlet->get_id() . "').children('h1').children('a').hide();"
                            . "$('#" . $this->portletID . "').append('<h3>Keine Neuigkeiten</h3>');";
                    //$(\"[id*='subscription1376_']\").hide();
                } else {


                    $jsSelector = "$('#" . $this->params["hide"] . "').hide(); "
                                . "if($('#" . $portlet->get_id() . "').children('div').children('div:visible').length == 0){"
                                    . "$('#" . $portlet->get_id() . "').children('h1').children('a').hide();"
                                . "}";
                    $js .= "if ($('#" . $this->portletID . "').children('div').children('div:visible').length == 0) $('#" . $this->portletID . "').append('<h3>Keine Neuigkeiten</h3>');";
                }

                $jsWrapper->setJs($jsSelector . $js);
            }
        }




        $ajaxResponseObject->addWidget($jsWrapper);
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

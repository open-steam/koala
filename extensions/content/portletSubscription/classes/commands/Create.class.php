<?php

namespace PortletSubscription\Commands;

class Create extends \AbstractCommand implements \IAjaxCommand, \IIdCommand, \IFrameCommand {

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $params = $requestObject->getParams();
        if (isset($params["parent"])) {
            $column = $params["parent"];
        } else {
            $column = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $params["id"]);
        }

        //get subscribed object
        $subscribedObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $params["objectid"]);

        //create object// if($portlet->get_name() !== "Änderungen in ".$subscriptionObject){

        if (trim($params["title"]) == "") {
            $followedObjectTitel = "Änderungen in " . getCleanName($subscribedObject);
        } else {
            $followedObjectTitel = $params["title"];
        }

        $subscriptionPortlet = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $followedObjectTitel, $column);


        $desc = "Abonnement $followedObjectTitel";

        /*
          if (isset($params["title"]) && $params["title"] != "") {
          $desc = $params["title"];
          }
         */

        $subscriptionPortlet->set_attributes(array(
            OBJ_DESC => $desc,
            OBJ_TYPE => "container_portlet_bid",
            "bid:portlet" => "subscription",
            "bid:portlet:version" => "3.0",
            "PORTLET_SUBSCRIPTION_OBJECTID" => $params["objectid"],
            "PORTLET_SUBSCRIPTION_TYPE" => $params["type"],
            "PORTLET_SUBSCRIPTION_TIMESTAMP" => time(),
            "PORTLET_SUBSCRIPTION_FILTER" => array(),
            "PORTLET_SUBSCRIPTION_ORDER" => $params["sort"]
        ));

        $currentContent = Create::getCurrentContent($subscribedObject);

        $subscriptionPortlet->set_attribute("PORTLET_SUBSCRIPTION_CONTENT", $currentContent);


        \ExtensionMaster::getInstance()->getExtensionById("HomePortal")->updateSubscriptions($column->get_environment()->get_id());
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        
    }

    /**
     * 
     * @param type $object steam_object like folder or portal
     * @return type returns an array with the content or -1 if the object type is not supported
     */
    public static function getCurrentContent($subscribedObject) {
        switch (getObjectType($subscribedObject)) {
            case "room":
            case "userHome":
            case "gallery":
                foreach ($subscribedObject->get_inventory() as $element) {
                    $currentContent[$element->get_id()] = array("name" => $element->get_attribute(OBJ_NAME));
                }
                break;

            case "portal":
                foreach ($subscribedObject->get_inventory() as $column) {
                    foreach ($column->get_inventory() as $portlet) {
                        $currentContent[$portlet->get_id()] = array("name" => $portlet->get_attribute(OBJ_NAME));
                    }
                }
                break;

            default: return array(); //no object with an inventory
        }

        return $currentContent;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setJs(<<<END
		window.location.reload();
END
        );
        $ajaxResponseObject->addWidget($jswrapper);
        return $ajaxResponseObject;
    }

}

?>
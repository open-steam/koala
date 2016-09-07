<?php

namespace PortletSubscription\Subscriptions;

class PortalSubscription extends AbstractSubscription {

    private $columns;

    public function getUpdates() {

        $this->columns = $this->object->get_inventory();

        foreach ($this->columns as $column) {
            foreach ($column->get_inventory() as $portlet) {
                $this->content[] = $portlet;
            }
        }

        //build an array with the existing ids to compare ids and not objects later on
        $portletIds = Array();
        foreach ($this->content as $portlet) {
            $portletIds[$portlet->get_id()] = true;
        }


        if (!is_array($this->formerContent)) {
            $this->formerContent = array();
        }

        foreach ($this->formerContent as $id => $notUsed) {
            if (!array_key_exists($id, $portletIds)) { //the object existed in this portal but isn't there anymore, display an info that it is deleted / moved
                $this->updates[] = array(
                    PHP_INT_MAX - 1, //display the deleted files at the end
                    $id,
                    $this->getElementHtml(
                            $id, 
                            $id . "_" . $this->count, 
                            $this->private, 
                            "In letzter Zeit", 
                            "Das Portlet " . $this->formerContent[$id]["name"] . " ist nicht mehr vorhanden", 
                            "", 
                            ""
                    )
                );
            }
            $this->count++;
        }

        foreach ($this->content as $id => $object) { //there is a new object in this portal, show an info if it is not created recently
            if (!array_key_exists($object->get_id(), $this->formerContent) && $object->get_attribute("OBJ_CREATION_TIME") < $this->timestamp) {
                $this->updates[] = array(
                    PHP_INT_MAX - 1, //display the deleted files at the end
                    $object->get_id(),
                    $this->getElementHtml(
                            $object->get_id(), 
                            $object->get_id() . "_" . $this->count, 
                            $this->private, 
                            "In letzter Zeit", 
                            "Neues Portlet ",
                            \PortletSubscription::getNameForSubscription($object), 
                            PATH_URL . "portal/Index/" . $this->object->get_id() . "/"
                    )
                );
                $this->count++;
            }
        }


        foreach ($this->columns as $column) {
            if ($column instanceof \steam_container) {
                $portlets = $column->get_inventory();
                foreach ($portlets as $portlet) {
                    if ($portlet instanceof \steam_container && $portlet->get_attribute("bid:portlet") != "msg") {
                        $this->getUpdatesForPortlet($portlet, $column);
                    }
                    //evtl. einen switch-case block für die unterschiedlichen Portlets, wenns mehr werden?
                    //treat special portlets that are excluded in the first if-else statement
                    //messageportlets have to be treated differently because we have to observe the notification-objects inside the messageportlet too
                    if ($portlet->get_attribute("bid:portlet") == "msg") {
                        $latestUpdate = 0;
                        if (is_array($portlet->get_attribute("bid:portlet:content"))) {
                            foreach ($portlet->get_attribute("bid:portlet:content") as $message) {
                                $messageObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $message);

                                if ($messageObject instanceof \steam_object) {
                                    
                                    $messageObjectCreationTime = $messageObject->get_attribute("OBJ_CREATION_TIME");
                                    $messageObjectLastChanged = $messageObject->get_attribute("OBJ_LAST_CHANGED");

                                    
                                    if ($messageObjectLastChanged > $latestUpdate) {
                                        $latestUpdate = $messageObjectLastChanged;
                                    }
                                    
                                    if ($messageObjectCreationTime > $this->timestamp && !(isset($this->filter[$messageObject->get_id()]) && in_array($messageObjectCreationTime, $this->filter[$messageObject->get_id()]))) {
                                        $this->updates[] = array(
                                            $messageObjectCreationTime,
                                            $messageObject->get_id(),
                                            $this->getElementHtml(
                                                    $messageObject->get_id(), 
                                                    $messageObject->get_id() . "_" . $this->count, 
                                                    $this->private, 
                                                    $messageObjectCreationTime, 
                                                    "Neue Meldung ", 
                                                    \PortletSubscription::getNameForSubscription($messageObject), 
                                                    PATH_URL . "portal/Index/" . $this->object->get_id() . "/", 
                                                    " in Spalte " . $column->get_name() . ""
                                            )
                                        );
                                    }
                                    
                                    if ($messageObjectLastChanged > $this->timestamp && $messageObjectLastChanged > $messageObjectCreationTime+1 && $this->object->get_attribute("OBJ_LAST_CHANGED") < $messageObjectLastChanged && !(isset($this->filter[$messageObject->get_id()]) && in_array($messageObjectLastChanged, $this->filter[$messageObject->get_id()]))) {
                                        $this->updates[] = array(
                                            $messageObjectLastChanged,
                                            $messageObject->get_id(),
                                            $this->getElementHtml(
                                                    $messageObject->get_id(), 
                                                    $messageObject->get_id() . "_" . $this->count, 
                                                    $this->private, 
                                                    $messageObjectLastChanged, 
                                                    "Die Meldung ", 
                                                    \PortletSubscription::getNameForSubscription($messageObject), 
                                                    PATH_URL . "portal/Index/" . $this->object->get_id() . "/", 
                                                    " in Spalte " . $column->get_name() . " wurde geändert"
                                            )
                                        );
                                    }
                                }
                            }
                        }
                        //if the notification for the whole message portlet is not evoked by a new message
                        if ($latestUpdate < $portlet->get_attribute("OBJ_LAST_CHANGED")) {
                            $this->getUpdatesForPortlet($portlet, $column);
                        }
                    }
                }
            }
            $this->count++;
        }

        //if the object change doesn't come from the modified content, the object itself was modified
        if ($this->object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && $this->object->get_attribute("CONT_LAST_MODIFIED") != $this->object->get_attribute("OBJ_LAST_CHANGED") && !(isset($this->filter[$this->object->get_id()]) && in_array($this->object->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$this->object->get_id()]))) {

            $this->updates[] = array(
                $this->object->get_attribute("OBJ_LAST_CHANGED"),
                $this->object->get_id(),
                $this->getElementHtml(
                        $this->object->get_id(), 
                        $this->object->get_id() . "_0", 
                        $this->private, 
                        $this->object->get_attribute("OBJ_LAST_CHANGED"), 
                        "Die Portaleigenschaften wurden geändert", 
                        "", 
                        \ExtensionMaster::getInstance()->getUrlForObjectId($this->object->get_id(), "view")
                )
            );
        }

        //save back all changes to the objects in this container
        if ($this->changedFormerContent) {
            $this->portlet->set_attribute("PORTLET_SUBSCRIPTION_CONTENT", $this->formerContent);
        }

        return $this->updates;
    }

    public function getUpdatesForPortlet($portlet, $column) {
        //check, if the portlet is new and if it was created after the portal (to skip the initially created portlets)
        $creationTime = $portlet->get_attribute("OBJ_CREATION_TIME");
        $lastChanged = $portlet->get_attribute("OBJ_LAST_CHANGED");

        if ($creationTime > $this->timestamp && $this->object->get_attribute("OBJ_CREATION_TIME") + 1 < $creationTime && !(isset($this->filter[$portlet->get_id()]) && in_array($creationTime, $this->filter[$portlet->get_id()]))) {
            $this->updates[] = array(
                $creationTime,
                $portlet->get_id(),
                $this->getElementHtml(
                        $portlet->get_id(), 
                        $portlet->get_id() . "_" . $this->count, 
                        $this->private, 
                        $creationTime, 
                        "Neues ".\PortletSubscription::getPortletTypeForSubscription($portlet)."Portlet", 
                        \PortletSubscription::getNameForSubscription($portlet), 
                        PATH_URL . "portal/Index/" . $this->object->get_id() . "/", 
                        " in Spalte " . $column->get_name()
                )
            );

            //if the object is newer than the container mark it as a new object and add immediatly it to the known objects
            if (!array_key_exists($portlet->get_id(), $this->formerContent) || $this->formerContent[$portlet->get_id()]["name"] !== $portlet->get_attribute(OBJ_NAME)) {
                $this->formerContent[$portlet->get_id()] = array("name" => $portlet->get_attribute(OBJ_NAME));
                $this->changedFormerContent = true;
            }
        }

        if ($lastChanged > $this->timestamp && $lastChanged > $creationTime + 1 && $this->object->get_attribute("OBJ_LAST_CHANGED") < $lastChanged && !(isset($this->filter[$portlet->get_id()]) && in_array($lastChanged, $this->filter[$portlet->get_id()]))) {
            $this->updates[] = array(
                $lastChanged,
                $portlet->get_id(),
                $this->getElementHtml(
                        $portlet->get_id(), 
                        $portlet->get_id() . "_" . $this->count, 
                        $this->private, 
                        $lastChanged, 
                        "Geändertes ".\PortletSubscription::getPortletTypeForSubscription($portlet)."Portlet",
                        \PortletSubscription::getNameForSubscription($portlet), 
                        PATH_URL . "portal/Index/" . $this->object->get_id() . "/", 
                        " in Spalte " . $column->get_name()
                )
            );

            //update the name of this object if it has changed
            if (array_key_exists($portlet->get_id(), $this->formerContent) && $this->formerContent[$portlet->get_id()]["name"] !== $portlet->get_attribute(OBJ_NAME)) {
                $this->formerContent[$portlet->get_id()] = array("name" => $portlet->get_attribute(OBJ_NAME));
                $this->changedFormerContent = true;
            }
        }
    }

}

?>

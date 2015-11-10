<?php
namespace PortletSubscription\Subscriptions;

class PortalSubscription extends AbstractSubscription {
    private $updates;
    private $columns;
    private $count;
    private $formerContent;
    private $content;
    
    public function getUpdates() {
        $this->updates = array();
        $this->columns = $this->object->get_inventory();
        $this->formerContent = $this->portlet->get_attribute("PORTLET_SUBSCRIPTION_CONTENT");
        $this->count = 0;
        
        foreach($this->columns as $column){
            foreach($column->get_inventory() as $portlet){
                $this->content[] = $portlet;
            };
        }
        
        //build an array with the existing ids to compare ids and not objects later on
        $portletIds = Array();
        foreach($this->content as $portlet)
        {
            $portletIds[$portlet->get_id()] = true;
        }
        
        
        if(!is_array($this->formerContent)) {$this->formerContent = array();}
        
        foreach($this->formerContent as $id => $notUsed){
            if(!array_key_exists($id,$portletIds)){ //the object existed in this folder but isn't there anymore, display an info that it is deleted / moved
                $this->updates[] = array(
                                    0, 
                                    $id,
                                    $this->getElementHtml(
                                        $id, 
                                        $id . "_" . $this->count,
                                        $this->private,
                                        "In letzter Zeit",
                                        "Nicht mehr vorhandenes Objekt: ".$this->formerContent[$id]["name"],
                                        "",
                                        ""
                                    )
                                );
            }
            $this->count++;
        }
        
        foreach($this->content as $id => $object){ //there is a new object in this folder, show an info if it is not created recently
            if(!array_key_exists($object->get_id(),$this->formerContent) && $object->get_attribute("OBJ_CREATION_TIME") < $this->timestamp){ 
                $this->updates[] = array(
                                    0, 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(),
                                        $object->get_id() . "_" . $this->count,
                                        $this->private,
                                        "In letzter Zeit",
                                        "Neu vorhandenes Objekt: ".\PortletSubscription::getNameForSubscription($object),
                                        "",
                                        ""
                                    )
                                );
            }
            $this->count++;
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
                        foreach($portlet->get_attribute("bid:portlet:content") as $message){
                            $messageObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $message);
                            
                            if($messageObject->get_attribute("OBJ_LAST_CHANGED") > $latestUpdate) {$latestUpdate = $messageObject->get_attribute("OBJ_LAST_CHANGED");}
                            
                            if ($messageObject instanceof \steam_object && $messageObject->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$messageObject->get_id()]) && in_array($messageObject->get_attribute("OBJ_CREATION_TIME"), $this->filter[$messageObject->get_id()]))) {
                                $this->updates[] = array(
                                    $messageObject->get_attribute("OBJ_CREATION_TIME"), 
                                    $messageObject->get_id(),
                                    $this->getElementHtml(
                                        $messageObject->get_id(), 
                                        $messageObject->get_id() . "_" . $this->count,
                                        $this->private,
                                        $messageObject->get_attribute("OBJ_CREATION_TIME"),
                                        "Neue Meldung (in Portal <a href=\"" . PATH_URL . "portal/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        \PortletSubscription::getNameForSubscription($messageObject),
                                        PATH_URL . "portal/Index/" . $this->object->get_id() . "/",
                                        " (Spalte " . $column->get_name() . ")"
                                    )
                                );
                            } else if ($messageObject instanceof \steam_object && $messageObject->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && $this->object->get_attribute("OBJ_LAST_CHANGED") < $messageObject->get_attribute("OBJ_LAST_CHANGED") && !(isset($this->filter[$messageObject->get_id()]) && in_array($messageObject->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$messageObject->get_id()]))) {
                                $this->updates[] = array(
                                    $messageObject->get_attribute("OBJ_LAST_CHANGED"), 
                                    $messageObject->get_id(),
                                    $this->getElementHtml(
                                        $messageObject->get_id(), 
                                        $messageObject->get_id() . "_" . $this->count,
                                        $this->private,
                                        $messageObject->get_attribute("OBJ_LAST_CHANGED"),
                                        "Geänderte Meldung (in Portal <a href=\"" . PATH_URL . "portal/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        \PortletSubscription::getNameForSubscription($messageObject),
                                        PATH_URL . "portal/Index/" . $this->object->get_id() . "/",
                                        " (Spalte " . $column->get_name() . ")"
                                    )
                                );
                            }
                        }
                        
                        //if the notification for the whole message portlet is not evoked by a new message
                        if($latestUpdate < $portlet->get_attribute("OBJ_LAST_CHANGED") ){
                            $this->getUpdatesForPortlet($portlet, $column);
                        }
                    }
                }
            }
            $this->count++;
        }
        
        //save back all changes to the objects in this container
        $this->portlet->set_attribute("PORTLET_SUBSCRIPTION_CONTENT", $this->formerContent);
        
        return $this->updates;
    }
    
    public function getUpdatesForPortlet($portlet, $column){
        //check, if the portlet is new and if it was created after the portal (to skip the initially created portlets)
        if ($portlet->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && $this->object->get_attribute("OBJ_CREATION_TIME")+1 < $portlet->get_attribute("OBJ_CREATION_TIME") && !(isset($this->filter[$portlet->get_id()]) && in_array($portlet->get_attribute("OBJ_CREATION_TIME"), $this->filter[$portlet->get_id()]))) {
                                $this->updates[] = array(
                                    $portlet->get_attribute("OBJ_CREATION_TIME"), 
                                    $portlet->get_id(),
                                    $this->getElementHtml(
                                        $portlet->get_id(), 
                                        $portlet->get_id() . "_" . $this->count,
                                        $this->private,
                                        $portlet->get_attribute("OBJ_CREATION_TIME"),
                                        "Neues Portlet (in Portal <a href=\"" . PATH_URL . "portal/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        \PortletSubscription::getNameForSubscription($portlet),
                                        PATH_URL . "portal/Index/" . $this->object->get_id() . "/",
                                        " (Spalte " . $column->get_name() . ")"
                                    )
                                );
                                
                                //if the object is newer than the container mark it as a new object and add immediatly it to the known objects
                                if(!array_key_exists($portlet->get_id(), $this->formerContent)){
                                    $this->formerContent[$portlet->get_id()] = array("name"=>$portlet->get_name());
                                }
                                
                            } else if ($portlet->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && $this->object->get_attribute("OBJ_LAST_CHANGED") < $portlet->get_attribute("OBJ_LAST_CHANGED") && !(isset($this->filter[$portlet->get_id()]) && in_array($portlet->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$portlet->get_id()]))) {
                                $this->updates[] = array(
                                    $portlet->get_attribute("OBJ_LAST_CHANGED"), 
                                    $portlet->get_id(),
                                    $this->getElementHtml(
                                        $portlet->get_id(), 
                                        $portlet->get_id() . "_" . $this->count,
                                        $this->private,
                                        $portlet->get_attribute("OBJ_LAST_CHANGED"),
                                        "Geändertes Portlet (in Portal <a href=\"" . PATH_URL . "portal/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        \PortletSubscription::getNameForSubscription($portlet),
                                        PATH_URL . "portal/Index/" . $this->object->get_id() . "/",
                                        " (Spalte " . $column->get_name() . ")"
                                    )
                                );
                            }
        
    }
}
?>
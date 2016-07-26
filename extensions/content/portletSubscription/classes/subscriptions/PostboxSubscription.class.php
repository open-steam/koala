<?php
namespace PortletSubscription\Subscriptions;

class PostboxSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $portletInstance = \PortletSubscription::getInstance();
        $updates = array();
        $objects = $this->object->get_attribute("bid:postbox:container")->get_inventory();
        
        
        $objectIds = Array();
        foreach($objects as $object)
        {
            $objectIds[$object->get_id()] = true;
        }
        
        $formerContent = $this->portlet->get_attribute("PORTLET_SUBSCRIPTION_CONTENT");
        if(!is_array($formerContent)) {$formerContent = array();}
        
        $count = 0;
        foreach($formerContent as $id => $unUsed){
            if(!array_key_exists($id,$objectIds)){ //the object existed in this folder but isn't there anymore, display an info that it is deleted / moved
                $updates[] = array(
                    PHP_INT_MAX, //display the deleted files at the end
                    $id,
                    $this->getElementHtml(
                        $id, 
                        $id . "_" . $count,
                        $this->private,
                        "In letzter Zeit",
                        "Nicht mehr vorhandene Abgabe: ".$formerContent[$id]["name"],
                        "",
                        ""
                    )
                );
            }
            $count++;
        }
        
        foreach($objects as $id => $object){ //there is a new object in this folder, show an info if it is not created recently (eg. moved here)
            if(!array_key_exists($object->get_id(),$formerContent) && $object->get_attribute("OBJ_CREATION_TIME") < $this->timestamp){ 
                $updates[] = array(
                    PHP_INT_MAX-1, //display the deleted files at the end
                    $object->get_id(),
                    $this->getElementHtml(
                        $object->get_id(),
                        $object->get_id() . "_" . $count,
                        $this->private,
                        "In letzter Zeit",
                        "Neu vorhandene Abgabe: ",
                        \PortletSubscription::getNameForSubscription($object),
                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                    )
                );
            }
            $count++;
        }
        
        
        foreach ($objects as $object) {
            if ($object instanceof \steam_object) {
                if ($object->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($object->get_attribute("OBJ_CREATION_TIME"), $this->filter[$object->get_id()]))) {
                    $updates[] = array(
                                    $object->get_attribute("OBJ_CREATION_TIME"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $count,
                                        $this->private,
                                        $object->get_attribute("OBJ_CREATION_TIME"),
                                        "Neue Abgabe (im Briefkasten <a href=\"" . PATH_URL . "postbox/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        \PortletSubscription::getNameForSubscription($object),
                                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                                    )
                                );
                    
                    //if the object is newer than the container mark it as a new object and add immediatly it to the known objects
                    $formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                    
                    //if ($this->depth < 1) {
                    //    $updates = array_merge($updates, $portletInstance->collectUpdates(array(), $this->portlet, $object, $this->private, $this->timestamp, $this->filter, $this->depth + 1));
                    //}
                } 
                /*
                else if ($this->depth < 1) {
                    $updates = array_merge($updates, $portletInstance->collectUpdates(array(), $this->portlet, $object, $this->private, $this->timestamp, $this->filter, $this->depth + 1));
                // folder in depth = 1 (only show new or changed message depending on timestamp)
                } else
                 
                */ else if ($object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($object->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$object->get_id()]))) {
                
                    $updates[] = array(
                                    $object->get_attribute("OBJ_LAST_CHANGED"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $count,
                                        $this->private,
                                        $object->get_attribute("OBJ_LAST_CHANGED"),
                                        "Geänderte Abgabe (im Briefkasten <a href=\"" . PATH_URL . "postbox/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        \PortletSubscription::getNameForSubscription($object),
                                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                                    )
                                );
                    
                    if(array_key_exists($object->get_id(), $formerContent)){
                        $formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                    }
                }
            }
            $count++;
        }
        
        $this->portlet->set_attribute("PORTLET_SUBSCRIPTION_CONTENT", $formerContent);
        
        return $updates;
    }
}
?>
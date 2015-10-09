<?php
namespace PortletSubscription\Subscriptions;

class FolderSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $portletInstance = \PortletSubscription::getInstance();
        $updates = array();
        $objects = $this->object->get_inventory();
        
        //build an array with the existing ids to compare ids and not objects later on
        $objectIds = Array();
        foreach($objects as $object)
        {
            $objectIds[$object->get_id()] = true;
        }
        //save the current content in an separat array into the portlet to recognize deleted objects
        
        $formerContent = $this->portlet->get_attribute("PORTLET_SUBSCRIPTION_CONTENT");
        if(!is_array($formerContent)) {$formerContent = array();}
        
        $count = 0;
        foreach($formerContent as $id => $notUsed){
            if(!array_key_exists($id,$objectIds)){ //the object existed in this folder but isn't there anymore, display an info that it is deleted / moved
                $updates[] = array(
                                    0, 
                                    $id,
                                    $this->getElementHtml(
                                        $id, 
                                        $id . "_" . $count,
                                        $this->private,
                                        "in letzter Zeit",
                                        "Nicht mehr vorhandenes Objekt: ".$formerContent[$id]["name"],
                                        "",
                                        ""
                                    )
                                );
            }
            $count++;
        }
        foreach($objects as $object){
            //if the object is in the folder but not yet in the saved list, add it to the list to monitor it
            if(!array_key_exists($object->get_id(), $formerContent)){
                $formerContent[$object->get_id()] = array("name"=>$object->get_name ());
            }
        }
        
        
        $this->portlet->set_attribute("PORTLET_SUBSCRIPTION_CONTENT", $formerContent);
        
        
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
                                        "Neue". \PortletSubscription::getObjectTypeForSubscription($object),
                                        \PortletSubscription::getNameForSubscription($object),
                                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                                    )
                                );
                    //if ($this->depth < 1) {
                    //    $updates = array_merge($updates, $portletInstance->collectUpdates(array(), $this->portlet, $object, $this->private, $this->timestamp, $this->filter, $this->depth + 1));
                    //}
                }
                
                
                //$containerLastModified = $object->get_attribute("CONT_LAST_MODIFIED");
                
                else if ($object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($object->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$object->get_id()]))) {
                    $updates[] = array(
                                    $object->get_attribute("OBJ_LAST_CHANGED"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $count,
                                        $this->private,
                                        $object->get_attribute("OBJ_LAST_CHANGED"),
                                        "Geänderte". \PortletSubscription::getObjectTypeForSubscription($object),
                                        \PortletSubscription::getNameForSubscription($object),
                                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                                    )
                                );
                }
                
                
                
                else if ($object->get_attribute("CONT_LAST_MODIFIED") > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($object->get_attribute("CONT_LAST_MODIFIED"), $this->filter[$object->get_id()]))) {
                    $updates[] = array(
                                    $object->get_attribute("CONT_LAST_MODIFIED"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $count,
                                        $this->private,
                                        $object->get_attribute("CONT_LAST_MODIFIED"),
                                        "Geänderter Ordner:",
                                        \PortletSubscription::getNameForSubscription($object),
                                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                                    )
                                );
                }
                
                
                //recursion
                if ($this->depth < 1) {
                    $updates = array_merge($updates, $portletInstance->collectUpdates(array(), $this->portlet, $object, $this->private, $this->timestamp, $this->filter, $this->depth + 1));
                // folder in depth = 1 (only show new or changed message depending on timestamp)
                }
                
                
                
                
            }
            $count++;
        }
        return $updates;
    }
}
?>
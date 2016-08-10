<?php
namespace PortletSubscription\Subscriptions;

class FolderSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        
        $updates = array();
        $objects = $this->object->get_inventory();
        
        //build an array with the existing ids to compare ids and not objects later on to find new and deleted elements
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
                        "Nicht mehr vorhandenes Objekt: ".$formerContent[$id]["name"],
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
                        "Neu vorhandenes Objekt: ",
                        \PortletSubscription::getNameForSubscription($object),
                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                    )
                );
            }
            $count++;
        }
        
        
        
        
        foreach ($objects as $object) {
            //this extension is also used to monitor homePortals. As every change in the user's home portal or any user action causes a change of the user object (e.g. update history), this is not what one wants to see.
            if ($object instanceof \steam_object && getObjectType($object) !== "user") {
                $creationTime = $object->get_attribute("OBJ_CREATION_TIME");
                $lastChanged = $object->get_attribute("OBJ_LAST_CHANGED");
                $contLastModified = $object->get_attribute("CONT_LAST_MODIFIED");
                if ($creationTime > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($creationTime, $this->filter[$object->get_id()]))) {
                    $updates[] = array(
                        $creationTime, 
                        $object->get_id(),
                        $this->getElementHtml(
                            $object->get_id(), 
                            $object->get_id() . "_" . $count,
                            $this->private,
                            $creationTime,
                            "Neue". \PortletSubscription::getObjectTypeForSubscription($object),
                            \PortletSubscription::getNameForSubscription($object),
                            \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                        )
                    );

                    //if the object is newer than the container mark it as a new object and add immediatly it to the known objects
                    $formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                }
                
                
                if ($lastChanged > $this->timestamp && $lastChanged > $creationTime+1 && !(isset($this->filter[$object->get_id()]) && in_array($lastChanged, $this->filter[$object->get_id()]))) {
                    $updates[] = array(
                        $lastChanged, 
                        $object->get_id(),
                        $this->getElementHtml(
                            $object->get_id(), 
                            $object->get_id() . "_" . $count,
                            $this->private,
                            $lastChanged,
                            "Geänderte". \PortletSubscription::getObjectTypeForSubscription($object),
                            \PortletSubscription::getNameForSubscription($object),
                            \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                        )
                    );
                    
                    if(array_key_exists($object->get_id(), $formerContent)){
                        $formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                    }
                }
                
                
                /*
                else if (!$newObjectCausedUpdate && $contLastModified > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($contLastModified, $this->filter[$object->get_id()]))) {
                    $updates[] = array(
                        $contLastModified, 
                        $object->get_id(),
                        $this->getElementHtml(
                            $object->get_id(), 
                            $object->get_id() . "_" . $count,
                            $this->private,
                            $contLastModified,
                            "Geänderter Ordner:",
                            \PortletSubscription::getNameForSubscription($object),
                            \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                        )
                    );
                    
                    if(array_key_exists($object->get_id(), $formerContent)){
                        $formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                    }
                }
                
                //$portletInstance = \PortletSubscription::getInstance();
                /*recursion
                if ($this->depth < 1) {
                    $updates = array_merge($updates, $portletInstance->collectUpdates(array(), $this->portlet, $object, $this->private, $this->timestamp, $this->filter, $this->depth + 1));
                 folder in depth = 1 (only show new or changed message depending on timestamp)
                }
                */
            }
            $count++;
        }
        //if the object change doesn't come from the modified content, the object itself was modified
        if ($this->object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && $this->object->get_attribute("CONT_LAST_MODIFIED") != $this->object->get_attribute("OBJ_LAST_CHANGED") && !(isset($this->filter[$this->object->get_id()]) && in_array($this->object->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$this->object->get_id()]))) {
            
            $updates[] = array(
                            $this->object->get_attribute("OBJ_LAST_CHANGED"), 
                            $this->object->get_id(), 
                            $this->getElementHtml(
                                $this->object->get_id(), 
                                $this->object->get_id() . "_0",
                                $this->private,
                                $this->object->get_attribute("OBJ_LAST_CHANGED"),
                                "Die Ordnereigenschaften wurden geändert",
                                "",
                                \ExtensionMaster::getInstance()->getUrlForObjectId($this->object->get_id(), "view")
                            )
            );
        }
        
        //save back all changes to the objects in this container
        $this->portlet->set_attribute("PORTLET_SUBSCRIPTION_CONTENT", $formerContent);
        
        return $updates;
    }
}
?>
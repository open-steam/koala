<?php
namespace PortletSubscription\Subscriptions;

class PostboxSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        
        $this->content = $this->object->get_attribute("bid:postbox:container")->get_inventory();
        
        if(!is_array($this->formerContent)) {
            $this->formerContent = array();
            $this->changedFormerContent = true;
        }
        
        //build an array with the existing ids to compare ids and not objects later on to find new and deleted elements
        $objectIds = Array();
        foreach($this->content as $object)
        {
            $objectIds[$object->get_id()] = true;
        }
        
        foreach($this->formerContent as $id => $unUsed){
            if(!array_key_exists($id,$objectIds)){ //the object existed in this folder but isn't there anymore, display an info that it is deleted / moved
                $this->updates[] = array(
                    PHP_INT_MAX, //display the deleted files at the end
                    $id,
                    $this->getElementHtml(
                        $id, 
                        $id . "_" . $this->count,
                        $this->private,
                        "In letzter Zeit",
                        "Nicht mehr vorhandene Abgabe: ".$this->formerContent[$id]["name"],
                        "",
                        ""
                    )
                );
            }
            $this->count++;
        }
        
        foreach($this->content as $id => $object){ //there is a new object in this folder, show an info if it is not created recently (eg. moved here)
            if(!array_key_exists($object->get_id(),$this->formerContent) && $object->get_attribute("OBJ_CREATION_TIME") < $this->timestamp){ 
                $this->updates[] = array(
                    PHP_INT_MAX-1, //display the deleted files at the end
                    $object->get_id(),
                    $this->getElementHtml(
                        $object->get_id(),
                        $object->get_id() . "_" . $this->count,
                        $this->private,
                        "In letzter Zeit",
                        "Neu vorhandene Abgabe: ",
                        \PortletSubscription::getNameForSubscription($object),
                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                    )
                );
            }
            $this->count++;
        }
        
        
        foreach ($this->content as $object) {
            if ($object instanceof \steam_object) {
                if ($object->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($object->get_attribute("OBJ_CREATION_TIME"), $this->filter[$object->get_id()]))) {
                    $this->updates[] = array(
                                    $object->get_attribute("OBJ_CREATION_TIME"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $this->count,
                                        $this->private,
                                        $object->get_attribute("OBJ_CREATION_TIME"),
                                        "Neue Abgabe (im Briefkasten <a href=\"" . PATH_URL . "postbox/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        \PortletSubscription::getNameForSubscription($object),
                                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                                    )
                                );
                    
                    //if the object is newer than the container mark it as a new object and add immediatly it to the known objects
                    if(!array_key_exists($object->get_id(), $this->formerContent) || $this->formerContent[$object->get_id()]["name"] !== $object->get_attribute(OBJ_NAME)){
                        $this->formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                        $this->changedFormerContent = true;
                    }

                } 

                 
                else if ($object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($object->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$object->get_id()]))) {
                
                    $this->updates[] = array(
                                    $object->get_attribute("OBJ_LAST_CHANGED"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $this->count,
                                        $this->private,
                                        $object->get_attribute("OBJ_LAST_CHANGED"),
                                        "Geänderte Abgabe (im Briefkasten <a href=\"" . PATH_URL . "postbox/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        \PortletSubscription::getNameForSubscription($object),
                                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                                    )
                                );
                    
                    //update the name of this object if it has changed
                    if(array_key_exists($object->get_id(), $this->formerContent) &&  $this->formerContent[$object->get_id()]["name"] !== $object->get_attribute(OBJ_NAME)){
                        $this->formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                        $this->changedFormerContent = true;
                    }
                }
            }
            $this->count++;
        }
        
        if ($this->object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && $this->object->get_attribute("CONT_LAST_MODIFIED") != $this->object->get_attribute("OBJ_LAST_CHANGED") && !(isset($this->filter[$this->object->get_id()]) && in_array($this->object->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$this->object->get_id()]))) {
            
            $this->updates[] = array(
                            $this->object->get_attribute("OBJ_LAST_CHANGED"), 
                            $this->object->get_id(), 
                            $this->getElementHtml(
                                $this->object->get_id(), 
                                $this->object->get_id() . "_0",
                                $this->private,
                                $this->object->get_attribute("OBJ_LAST_CHANGED"),
                                "Die Objekteigenschaften wurden geändert",
                                "",
                                \ExtensionMaster::getInstance()->getUrlForObjectId($this->object->get_id(), "view")
                            )
            );
        }
        
        //save back all changes to the objects in this container
        if($this->changedFormerContent){
            $this->portlet->set_attribute("PORTLET_SUBSCRIPTION_CONTENT", $this->formerContent);
        }
        
        return $this->updates;
    }
}
?>
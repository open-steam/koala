<?php
namespace PortletSubscription\Subscriptions;

class GallerySubscription extends AbstractSubscription {
    
    public function getUpdates() {
        
        $this->content = $this->object->get_inventory();
        
        
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
                        $id . "_" . $this->count++,
                        $this->private,
                        "In letzter Zeit",
                        "Nicht mehr vorhandenes Objekt: ".$this->formerContent[$id]["name"],
                        "",
                        ""
                    )
                );
            }
        }
        
        
        
        foreach ($this->content as $id => $object) {
            $creationTime = $object->get_attribute("OBJ_CREATION_TIME");
            $lastChanged = $object->get_attribute("OBJ_LAST_CHANGED");
            $objectName = $object->get_attribute(OBJ_NAME);

            //there is a new object in this folder, show an info if it is not created recently (eg. moved here)
            if(!array_key_exists($object->get_id(),$this->formerContent) && $creationTime < $this->timestamp){ 
                $this->updates[] = array(
                    PHP_INT_MAX-1, //display the deleted files at the end
                    $object->get_id(),
                    $this->getElementHtml(
                        $object->get_id(),
                        $object->get_id() . "_" . $this->count++,
                        $this->private,
                        "In letzter Zeit",
                        "Neu vorhandenes Objekt: ",
                        \PortletSubscription::getNameForSubscription($object),
                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                    )
                );
            }
            
            if ($object instanceof \steam_document) {
                if ($creationTime > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($creationTime, $this->filter[$object->get_id()]))) {
                    $this->updates[] = array(
                                    $creationTime, 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $this->count++,
                                        $this->private,
                                        $creationTime,
                                        "Neues Bild: <a href=\"" . PATH_URL . "explorer/ViewDocument/" . $object->get_id() . "/" . "\">".\PortletSubscription::getNameForSubscription($object)."</a> (in Fotoalbum <a href=\"" . PATH_URL . "photoAlbum/Index/" . $this->object->get_id() . "/" . "\">" . \PortletSubscription::getNameForSubscription($this->object) . "</a>)",
                                        "",
                                        PATH_URL . "gallery/Index/" . $this->object->get_id() . "/"
                                    )
                                );
                    
                    //if the object is newer than the container mark it as a new object and add immediatly it to the known objects
                    if(!array_key_exists($object->get_id(), $this->formerContent) || $this->formerContent[$object->get_id()]["name"] !== $objectName){
                        $this->formerContent[$object->get_id()] = array("name"=>$objectName);
                        $this->changedFormerContent = true;
                    }
                    
                } 
                if ($lastChanged > $this->timestamp && $lastChanged > $creationTime && !(isset($this->filter[$object->get_id()]) && (in_array($lastChanged, $this->filter[$object->get_id()])))) {
                    $this->updates[] = array(
                                    $lastChanged, 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $this->count++,
                                        $this->private,
                                        $lastChanged,
                                        "Geändertes Bild: <a href=\"" . PATH_URL . "explorer/ViewDocument/" . $object->get_id() . "/" . "\">".\PortletSubscription::getNameForSubscription($object)."</a> (in Fotoalbum <a href=\"" . PATH_URL . "photoAlbum/Index/" . $this->object->get_id() . "/" . "\">" . \PortletSubscription::getNameForSubscription($this->object) . "</a>)",
                                        "",
                                        PATH_URL . "gallery/Index/" . $this->object->get_id() . "/"
                                    )
                                );
                    
                    //update the name of this object if it has changed
                    if(array_key_exists($object->get_id(), $this->formerContent) &&  $this->formerContent[$object->get_id()]["name"] !== $objectName){
                        $this->formerContent[$object->get_id()] = array("name"=>$objectName);
                        $this->changedFormerContent = true;
                    }
                    
                }
            } 
            
        }
        
        $objectLastChanged = $this->object->get_attribute("OBJ_LAST_CHANGED");
        $objectContentLastModified = $this->object->get_attribute("CONT_LAST_MODIFIED");
        
        if ($objectLastChanged > $this->timestamp && $objectContentLastModified != $objectLastChanged && !(isset($this->filter[$this->object->get_id()]) && in_array($objectLastChanged, $this->filter[$this->object->get_id()]))) {
            
            $this->updates[] = array(
                            $objectLastChanged, 
                            $this->object->get_id(), 
                            $this->getElementHtml(
                                $this->object->get_id(), 
                                $this->object->get_id() . "_".$this->count++,
                                $this->private,
                                $objectLastChanged,
                                "Die Albumeigenschaften wurden geändert",
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

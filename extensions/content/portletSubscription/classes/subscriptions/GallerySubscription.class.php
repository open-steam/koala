<?php
namespace PortletSubscription\Subscriptions;

class GallerySubscription extends AbstractSubscription {
    
    public function getUpdates() {
        
        $updates = array();
        $count = 0;
        $objects = $this->object->get_inventory();
        
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
        
        
        
        foreach ($objects as $id => $object) {
            //there is a new object in this folder, show an info if it is not created recently (eg. moved here)
            if(!array_key_exists($object->get_id(),$formerContent) && $object->get_attribute("OBJ_CREATION_TIME") < $this->timestamp){ 
                $updates[] = array(
                    PHP_INT_MAX-1, //display the deleted files at the end
                    $object->get_id(),
                    $this->getElementHtml(
                        $object->get_id(),
                        $object->get_id() . "_" . $count,
                        $this->private,
                        "In letzter Zeit",
                        "Neu vorhandenes Objekt: ".\PortletSubscription::getNameForSubscription($object),
                        "",
                        ""
                    )
                );
            }
            $count++;
            
            if ($object instanceof \steam_document) {
                if ($object->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($object->get_attribute("OBJ_CREATION_TIME"), $this->filter[$object->get_id()]))) {
                    $updates[] = array(
                                    $object->get_attribute("OBJ_CREATION_TIME"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $count,
                                        $this->private,
                                        $object->get_attribute("OBJ_CREATION_TIME"),
                                        "Neues Bild: ". \PortletSubscription::getNameForSubscription($object) ." (in Fotoalbum <a href=\"" . PATH_URL . "photoAlbum/Index/" . $this->object->get_id() . "/" . "\">" . \PortletSubscription::getNameForSubscription($this->object) . "</a>)",
                                        "",
                                        PATH_URL . "gallery/Index/" . $this->object->get_id() . "/"
                                    )
                                );
                    //if the object is newer than the container mark it as a new object and add immediatly it to the known objects
                    if(!array_key_exists($object->get_id(), $formerContent)){
                        $formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                    }
                    
                } else if ($object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($object->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$object->get_id()]))) {
                    $updates[] = array(
                                    $object->get_attribute("OBJ_LAST_CHANGED"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $count,
                                        $this->private,
                                        $object->get_attribute("OBJ_LAST_CHANGED"),
                                        "Geändertes Bild: ". \PortletSubscription::getNameForSubscription($object) ." (in Fotoalbum <a href=\"" . PATH_URL . "photoAlbum/Index/" . $this->object->get_id() . "/" . "\">" . \PortletSubscription::getNameForSubscription($this->object) . "</a>)",
                                        "",
                                        PATH_URL . "gallery/Index/" . $this->object->get_id() . "/"
                                    )
                                );
                }
            } 
            $count++;
        }
        
        if ($this->object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && $this->object->get_attribute("CONT_LAST_MODIFIED") != $this->object->get_attribute("OBJ_LAST_CHANGED") && !(isset($this->filter[$this->object->get_id()]) && in_array($this->object->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$this->object->get_id()]))) {
            
            $updates[] = array(
                            $this->object->get_attribute("OBJ_LAST_CHANGED"), 
                            $this->object->get_id(), 
                            $this->getElementHtml(
                                $this->object->get_id(), 
                                $this->object->get_id() . "_0",
                                $this->private,
                                $this->object->get_attribute("OBJ_LAST_CHANGED"),
                                "Die Albumeigenschaften wurden geändert",
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

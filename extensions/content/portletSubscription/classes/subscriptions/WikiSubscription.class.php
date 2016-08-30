<?php
namespace PortletSubscription\Subscriptions;

class WikiSubscription extends AbstractSubscription {
    
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
                $name = substr($this->formerContent[$id]["name"], 0, strpos($this->formerContent[$id]["name"], ".wiki"));
                $this->updates[] = array(
                    PHP_INT_MAX, //display the deleted files at the end
                    $id,
                    $this->getElementHtml(
                        $id, 
                        $id . "_" . $this->count,
                        $this->private,
                        "In letzter Zeit",
                        "Nicht mehr vorhandener Artikel: ".$name,
                        "",
                        ""
                    )
                );
            }
            $this->count++;
        }
        
        //as there is no possibility to compy and paste an article into the wiki that is older then the current timestamp we do not have to track if there is an inserted object

        
        foreach ($this->content as $object) {
            $mime = $object->get_attribute("DOC_MIME_TYPE");
            $creationTime = $object->get_attribute("OBJ_CREATION_TIME");
            $lastChanged = $object->get_attribute("DOC_LAST_MODIFIED");
            
            if ($object instanceof \steam_document && $mime === "text/wiki") {
                if ($creationTime > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($creationTime, $this->filter[$object->get_id()]))) {
                    $this->updates[] = array(
                                    $creationTime, 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $this->count,
                                        $this->private,
                                        $creationTime,
                                        "Neuer Artikel (in Wiki <a href=\"" . PATH_URL . "wiki/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        substr($object->get_name(), 0, strpos($object->get_name(), ".wiki")),
                                        PATH_URL . "wiki/entry/" . $object->get_id() . "/"
                                    )
                                );
                    
                    //if the object is newer than the container mark it as a new object and add immediatly it to the known objects
                    if(!array_key_exists($object->get_id(), $this->formerContent) || $this->formerContent[$object->get_id()]["name"] !== $object->get_attribute(OBJ_NAME)){
                        $this->formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                        $this->changedFormerContent = true;
                    }
                } 
                
                if ($lastChanged > $this->timestamp && $lastChanged > $creationTime+1&& !(isset($this->filter[$object->get_id()]) && in_array($lastChanged, $this->filter[$object->get_id()]))) {
                    $this->updates[] = array(
                                    $lastChanged, 
                                    $object->get_id(), 
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $this->count,
                                        $this->private,
                                        $lastChanged,
                                        "Geänderter Artikel (in Wiki <a href=\"" . PATH_URL . "wiki/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        substr($object->get_name(), 0, strpos($object->get_name(), ".wiki")),
                                        PATH_URL . "wiki/entry/" . $object->get_id() . "/"
                                    )
                                );
                    
                    //update the name of this object if it has changed
                    if(array_key_exists($object->get_id(), $this->formerContent) &&  $this->formerContent[$object->get_id()]["name"] !== $object->get_attribute(OBJ_NAME)){
                        $this->formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                        $this->changedFormerContent = true;
                    }
                }
            } else if ($object instanceof \steam_document && ($mime === "image/jpg" || $mime === "image/jpeg" || $mime === "image/gif" || $mime === "image/png")) {
                $lastChanged = $object->get_attribute("OBJ_LAST_CHANGED");
                if ($creationTime > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($creationTime, $this->filter[$object->get_id()]))) {
                    $this->updates[] = array(
                                    $creationTime, 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $this->count,
                                        $this->private,
                                        $creationTime,
                                        "Neues Bild (in Wikimediathek <a href=\"" . PATH_URL . "wiki/mediathek/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        $object->get_name(),
                                        PATH_URL . "wiki/mediathek/". $this->object->get_id() . "/"
                                    )
                                );
                    
                    //if the object is newer than the container mark it as a new object and add immediatly it to the known objects
                    if(!array_key_exists($object->get_id(), $this->formerContent) || $this->formerContent[$object->get_id()]["name"] !== $object->get_attribute(OBJ_NAME)){
                        $this->formerContent[$object->get_id()] = array("name"=>$object->get_attribute(OBJ_NAME));
                        $this->changedFormerContent = true;
                    }
                    
                }
                if ($lastChanged > $this->timestamp && $lastChanged > $creationTime && !(isset($this->filter[$object->get_id()]) && in_array($lastChanged, $this->filter[$object->get_id()]))) {
                    $this->updates[] = array(
                                    $lastChanged, 
                                    $object->get_id(), 
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $this->count,
                                        $this->private,
                                        $lastChanged,
                                        "Geändertes Bild (in Wikimediathek <a href=\"" . PATH_URL . "wiki/mediathek/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        $object->get_name(),
                                        PATH_URL . "wiki/mediathek/" . $this->object->get_id() . "/"
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
                                "Die Wikieigenschaften wurden geändert",
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
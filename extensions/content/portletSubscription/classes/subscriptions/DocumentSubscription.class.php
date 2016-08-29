<?php
namespace PortletSubscription\Subscriptions;

class DocumentSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        
        //scan the existing updates for an entry with this object id and if it exists, don't show another notifyevent 
        //(as we only can say that something has changed but not what exactly, one notification is enough)
        foreach($this->updates as $key1 => $value) { 
		if($value[1] == $this->object->get_id()){ 
                    return $this->updates;
                } 
            } 
            
        $docLastModified = $this->object->get_attribute("DOC_LAST_MODIFIED");
        $objLastChanged = $this->object->get_attribute("OBJ_LAST_CHANGED");
       
        if ($docLastModified > $this->timestamp && !(isset($this->filter[$this->object->get_id()]) && in_array($docLastModified, $this->filter[$this->object->get_id()]))) {
            
            $this->updates[] = array(
                            $docLastModified, 
                            $this->object->get_id(), 
                            $this->getElementHtml(
                                $this->object->get_id(), 
                                $this->object->get_id() . "_0",
                                $this->private,
                                $docLastModified,
                                "Dokumenteninhalt wurde geändert:",
                                \PortletSubscription::getNameForSubscription($this->object),
                                \ExtensionMaster::getInstance()->getUrlForObjectId($this->object->get_id(), "view")
                            )
            );
        }
        //the change of the content also updates the objLastChanged attribute. 
        if ($objLastChanged > $this->timestamp && $objLastChanged > $docLastModified+1 && !(isset($this->filter[$this->object->get_id()]) && in_array($objLastChanged, $this->filter[$this->object->get_id()]))) {
            
            $this->updates[] = array(
                            $objLastChanged, 
                            $this->object->get_id(), 
                            $this->getElementHtml(
                                $this->object->get_id(), 
                                $this->object->get_id() . "_0",
                                $this->private,
                                $objLastChanged,
                                "Die Dokumenteneigenschaften wurden geändert",
                                "",
                                \ExtensionMaster::getInstance()->getUrlForObjectId($this->object->get_id(), "view")
                            )
            );
        }
        return $this->updates;
    }
}
?>
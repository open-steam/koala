<?php
namespace PortletSubscription\Subscriptions;

class DocumentSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $updates = array();
        $document = $this->object;
        
        //scan the existing updates for an entry with this object id and if it exists, don't show another notifyevent 
        //(as we only can say that something has changed but not what exactly, one notification is enough)
        foreach($updates as $key1 => $value) { 
		if($value[1] == $document->get_id()){ 
                    return $updates;
                } 
            } 
       
        if ($document->get_attribute("DOC_LAST_MODIFIED") > $this->timestamp && !(isset($this->filter[$document->get_id()]) && in_array($document->get_attribute("DOC_LAST_MODIFIED"), $this->filter[$document->get_id()]))) {
            
            $updates[] = array(
                            $document->get_attribute("DOC_LAST_MODIFIED"), 
                            $document->get_id(), 
                            $this->getElementHtml(
                                $document->get_id(), 
                                $document->get_id() . "_0",
                                $this->private,
                                $document->get_attribute("DOC_LAST_MODIFIED"),
                                "Dokumenteninhalt wurde geändert:",
                                \PortletSubscription::getNameForSubscription($document),
                                \ExtensionMaster::getInstance()->getUrlForObjectId($document->get_id(), "view")
                            )
            );
        } else if ($document->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && !(isset($this->filter[$document->get_id()]) && in_array($document->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$document->get_id()]))) {
            
            $updates[] = array(
                            $document->get_attribute("OBJ_LAST_CHANGED"), 
                            $document->get_id(), 
                            $this->getElementHtml(
                                $document->get_id(), 
                                $document->get_id() . "_0",
                                $this->private,
                                $document->get_attribute("OBJ_LAST_CHANGED"),
                                "Dokumenteneigenschaften wurden geändert:",
                                \PortletSubscription::getNameForSubscription($document),
                                \ExtensionMaster::getInstance()->getUrlForObjectId($document->get_id(), "view")
                            )
            );
        }
        return $updates;
    }
}
?>
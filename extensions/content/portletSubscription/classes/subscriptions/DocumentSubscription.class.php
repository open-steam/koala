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
            
        $docLastModified = $document->get_attribute("DOC_LAST_MODIFIED");
        $objLastChanged = $document->get_attribute("OBJ_LAST_CHANGED");
       
        if ($docLastModified > $this->timestamp && !(isset($this->filter[$document->get_id()]) && in_array($docLastModified, $this->filter[$document->get_id()]))) {
            
            $updates[] = array(
                            $docLastModified, 
                            $document->get_id(), 
                            $this->getElementHtml(
                                $document->get_id(), 
                                $document->get_id() . "_0",
                                $this->private,
                                $docLastModified,
                                "Dokumenteninhalt wurde geändert:",
                                \PortletSubscription::getNameForSubscription($document),
                                \ExtensionMaster::getInstance()->getUrlForObjectId($document->get_id(), "view")
                            )
            );
        }
        //the change of the content also updates the objLastChanged attribute. 
        if ($objLastChanged > $this->timestamp && $objLastChanged > $docLastModified+1 && !(isset($this->filter[$document->get_id()]) && in_array($objLastChanged, $this->filter[$document->get_id()]))) {
            
            $updates[] = array(
                            $objLastChanged, 
                            $document->get_id(), 
                            $this->getElementHtml(
                                $document->get_id(), 
                                $document->get_id() . "_0",
                                $this->private,
                                $objLastChanged,
                                "Die Dokumenteneigenschaften wurden geändert",
                                "",
                                \ExtensionMaster::getInstance()->getUrlForObjectId($document->get_id(), "view")
                            )
            );
        }
        return $updates;
    }
}
?>
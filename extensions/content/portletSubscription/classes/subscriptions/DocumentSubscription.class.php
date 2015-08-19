<?php
namespace PortletSubscription\Subscriptions;

class DocumentSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $updates = array();
        $document = $this->object;
       
        if ($document->get_attribute("DOC_LAST_MODIFIED") > $this->timestamp && !(isset($this->filter[$document->get_id()]) && in_array($document->get_attribute("DOC_LAST_MODIFIED"), $this->filter[$document->get_id()]))) {
            
            //scan the existing updates for an eintry with this object id and if it exists, don't show another notifyevent
            $alreadyContained=false;
            foreach($updates as $key1 => $value1) { 
		if($value[1] == $document->get_id()){ 
                    $alreadyContained=true;
                    break;
                } 
            } 
            if($alreadyContained) return $updates;
            
            //else show this change
            $updates[] = array(
                            $document->get_attribute("DOC_LAST_MODIFIED"), 
                            $document->get_id(), 
                            $this->getElementHtml(
                                $document->get_id(), 
                                $document->get_id() . "_0",
                                $this->private,
                                $document->get_attribute("DOC_LAST_MODIFIED"),
                                "Dokument wurde geändert:",
                                \PortletSubscription::getNameForSubscription($document),
                                \ExtensionMaster::getInstance()->getUrlForObjectId($document->get_id(), "view")
                            )
            );
        }
        return $updates;
    }
}
?>
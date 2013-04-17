<?php
namespace PortletSubscription\Subscriptions;

class DocumentSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $updates = array();
        $document = $this->object;
        if ($document->get_attribute("DOC_LAST_MODIFIED") > $this->timestamp) {
            $updates[] = array(
                            $document->get_attribute("DOC_LAST_MODIFIED"), 
                            $document->get_id(), 
                            $this->getElementHtml(
                                $document->get_id(), 
                                $document->get_id() . "_0",
                                $this->private,
                                $document->get_attribute("DOC_LAST_MODIFIED"),
                                "Dokument wurde geändert:",
                                getCleanName($document),
                                \ExtensionMaster::getInstance()->getUrlForObjectId($document->get_id(), "view")
                            )
            );
        }
        return $updates;
    }
}
?>
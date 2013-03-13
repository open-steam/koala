<?php
namespace PortletSubscription\Subscriptions;

class FolderSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $updates = array();
        $objects = $this->object->get_inventory();
        $count = 0;
        foreach ($objects as $object) {
            if ($object instanceof \steam_document && !in_array($object->get_id(), $this->filter)) {
                if ($object->get_attribute("OBJ_CREATION_TIME") > $this->timestamp) {
                    $updates[] = array(
                                    $object->get_attribute("OBJ_CREATION_TIME"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $count,
                                        $this->private,
                                        $object->get_attribute("OBJ_CREATION_TIME"),
                                        "Neues Objekt:",
                                        getCleanName($object),
                                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                                    )
                                );
                } else if ($object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp) {
                    $updates[] = array(
                                    $object->get_attribute("OBJ_LAST_CHANGED"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $count,
                                        $this->private,
                                        $object->get_attribute("OBJ_LAST_CHANGED"),
                                        "Geändertes Objekt:",
                                        getCleanName($object),
                                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                                    )
                                );
                }
            }
            $count++;
        }
        return $updates;
    }
}
?>
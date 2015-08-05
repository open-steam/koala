<?php
namespace PortletSubscription\Subscriptions;

class PostboxSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $portletInstance = \PortletSubscription::getInstance();
        $updates = array();
        $objects = $this->object->get_attribute("bid:postbox:container")->get_inventory();
        $count = 0;
        foreach ($objects as $object) {
            if ($object instanceof \steam_object) {
                if ($object->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($object->get_attribute("OBJ_CREATION_TIME"), $this->filter[$object->get_id()]))) {
                    $updates[] = array(
                                    $object->get_attribute("OBJ_CREATION_TIME"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $count,
                                        $this->private,
                                        $object->get_attribute("OBJ_CREATION_TIME"),
                                        "Neue Abgabe:",
                                        \PortletSubscription::getNameForSubscription($object),
                                        \ExtensionMaster::getInstance()->getUrlForObjectId($object->get_id(), "view")
                                    )
                                );
                    //if ($this->depth < 1) {
                    //    $updates = array_merge($updates, $portletInstance->collectUpdates(array(), $this->portlet, $object, $this->private, $this->timestamp, $this->filter, $this->depth + 1));
                    //}
                } else if ($this->depth < 1) {
                    $updates = array_merge($updates, $portletInstance->collectUpdates(array(), $this->portlet, $object, $this->private, $this->timestamp, $this->filter, $this->depth + 1));
                // folder in depth = 1 (only show new or changed message depending on timestamp)
                } else if ($object->get_attribute("CONT_LAST_MODIFIED") > $this->timestamp && !(isset($this->filter[$object->get_id()]) && in_array($object->get_attribute("CONT_LAST_MODIFIED"), $this->filter[$object->get_id()]))) {
                    $updates[] = array(
                                    $object->get_attribute("CONT_LAST_MODIFIED"), 
                                    $object->get_id(),
                                    $this->getElementHtml(
                                        $object->get_id(), 
                                        $object->get_id() . "_" . $count,
                                        $this->private,
                                        $object->get_attribute("CONT_LAST_MODIFIED"),
                                        "Geänderter Ordner:",
                                        \PortletSubscription::getNameForSubscription($object),
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
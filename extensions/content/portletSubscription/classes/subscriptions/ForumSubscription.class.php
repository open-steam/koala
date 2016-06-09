<?php
namespace PortletSubscription\Subscriptions;

class ForumSubscription extends AbstractSubscription {

    public function getUpdates() {
        $updates = array();
        $count = 0;
        
        if ($this->object->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && $this->object->get_attribute("OBJ_LAST_CHANGED") !== $this->object->get_attribute("OBJ_ANNOTATIONS_CHANGED") && !(isset($this->filter[$this->object->get_id()]) && in_array($this->object->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$this->object->get_id()]))) {
                    $updates[] = array(
                                    $this->object->get_attribute("OBJ_LAST_CHANGED"), 
                                    $this->object->get_id(),
                                    $this->getElementHtml(
                                        $this->object->get_id(), 
                                        $this->object->get_id() . "_" . $count,
                                        $this->private,
                                        $this->object->get_attribute("OBJ_LAST_CHANGED"),
                                        "Die Eigenschaften vom Forum wurden geändert",
                                        "",
                                        PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $this->object->get_id()
                                    )
                                );
        }
        
        foreach ($this->object->get_annotations() as $thread) {
            if ($thread instanceof \steam_document) {
                if ($thread->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$thread->get_id()]) && in_array($thread->get_attribute("OBJ_CREATION_TIME"), $this->filter[$thread->get_id()]))) {
                    $updates[] = array(
                                    $thread->get_attribute("OBJ_CREATION_TIME"), 
                                    $thread->get_id(), 
                                    $this->getElementHtml(
                                        $thread->get_id(), 
                                        $thread->get_id() . "_" . $count,
                                        $this->private,
                                        $thread->get_attribute("OBJ_CREATION_TIME"),
                                        "Neues Thema (im Forum <a href=\"" . PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        \PortletSubscription::getNameForSubscription($thread),
                                        PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                    )
                                );
                    //                                                  //+1 as the thread might be created a second later then the forum object
                } else if ($thread->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp + 1 && $thread->get_attribute("OBJ_LAST_CHANGED") !== $thread->get_attribute("OBJ_ANNOTATIONS_CHANGED") && !(isset($this->filter[$thread->get_id()]) && in_array($thread->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$thread->get_id()]))) {
                    $updates[] = array(
                                    $thread->get_attribute("OBJ_LAST_CHANGED"), 
                                    $thread->get_id(),
                                    $this->getElementHtml(
                                        $thread->get_id(), 
                                        $thread->get_id() . "_" . $count,
                                        $this->private,
                                        $thread->get_attribute("OBJ_LAST_CHANGED"),
                                        "Geändertes Thema (im Forum <a href=\"" . PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        \PortletSubscription::getNameForSubscription($thread),
                                        PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                    )
                                );
                }
                //if ($thread->get_attribute("OBJ_ANNOTATIONS_CHANGED") > $this->timestamp) { changes on a headline fo an existing reply did not affect the attribute OBJ_ANNOTATIONS_CHANGED of the superior forum
                    foreach ($thread->get_annotations() as $msg) {
                        if ($msg instanceof \steam_document) {
                            if ($msg->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$msg->get_id()]) && in_array($msg->get_attribute("OBJ_CREATION_TIME"), $this->filter[$msg->get_id()]))) {
                                $updates[] = array(
                                                $msg->get_attribute("OBJ_CREATION_TIME"), 
                                                $msg->get_id(), 
                                                $this->getElementHtml(
                                                    $msg->get_id(), 
                                                    $msg->get_id() . "_" . $count,
                                                    $this->private,
                                                    $msg->get_attribute("OBJ_CREATION_TIME"),
                                                    "Neuer Beitrag (im Thema <a href=\"" . PATH_URL . "forum/showTopic/" . $this->object->get_id()."/".$thread->get_id(). "/" . "\">" . \PortletSubscription::getNameForSubscription($thread) . "</a> im Forum <a href=\"" . PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                                    \PortletSubscription::getNameForSubscription($msg),
                                                    PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                                )
                                            );
                            } else if ($msg->get_attribute("OBJ_LAST_CHANGED") > $this->timestamp && !(isset($this->filter[$msg->get_id()]) && in_array($msg->get_attribute("OBJ_LAST_CHANGED"), $this->filter[$msg->get_id()]))) {
                                $updates[] = array(
                                                $msg->get_attribute("OBJ_LAST_CHANGED"), 
                                                $msg->get_id(), 
                                                $this->getElementHtml(
                                                    $msg->get_id(), 
                                                    $msg->get_id() . "_" . $count,
                                                    $this->private,
                                                    $msg->get_attribute("OBJ_LAST_CHANGED"),
                                                    "Geänderter Beitrag (im Thema <a href=\"" . PATH_URL . "forum/showTopic/" . $this->object->get_id()."/".$thread->get_id(). "/" . "\">" . \PortletSubscription::getNameForSubscription($thread) . "</a> im Forum <a href=\"" . PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                                    \PortletSubscription::getNameForSubscription($msg),
                                                    PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                                )
                                            );
                            }
                        }
                        $count++;
                    }
                //}
            }
            $count++;
        }
        return $updates;
    }
}
?>
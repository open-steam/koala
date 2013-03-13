<?php
namespace PortletSubscription\Subscriptions;

class ForumSubscription extends AbstractSubscription {

    public function getUpdates() {
        $updates = array();
        $threads = $this->object->get_annotations();
        $count = 0;
        foreach ($threads as $thread) {
            if ($thread instanceof \steam_document) {
                if ($thread->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !in_array($thread->get_id(), $this->filter)) {
                    $updates[] = array(
                                    $thread->get_attribute("OBJ_CREATION_TIME"), 
                                    $thread->get_id(), 
                                    $this->getElementHtml(
                                        $thread->get_id(), 
                                        $count,
                                        $this->private,
                                        $thread->get_attribute("OBJ_CREATION_TIME"),
                                        "Neues Thema:",
                                        $thread->get_attribute("OBJ_DESC"),
                                        PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                    )
                                );
                } else if ($thread->get_attribute("DOC_LAST_MODIFIED") > $this->timestamp && !in_array($thread->get_id(), $this->filter)) {
                    $updates[] = array(
                                    $thread->get_attribute("DOC_LAST_MODIFIED"), 
                                    $thread->get_id(),
                                    $this->getElementHtml(
                                        $thread->get_id(), 
                                        $count,
                                        $this->private,
                                        $thread->get_attribute("DOC_LAST_MODIFIED"),
                                        "Geändertes Thema:",
                                        $thread->get_attribute("OBJ_DESC"),
                                        PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                    )
                                );
                }
                if ($thread->get_attribute("OBJ_ANNOTATIONS_CHANGED") > $this->timestamp) {
                    $msgs = $thread->get_annotations();
                    foreach ($msgs as $msg) {
                        if ($msg instanceof \steam_document && !in_array($msg->get_id(), $this->filter)) {
                            if ($msg->get_attribute("OBJ_CREATION_TIME") > $this->timestamp) {
                                $updates[] = array(
                                                $msg->get_attribute("OBJ_CREATION_TIME"), 
                                                $msg->get_id(), 
                                                $this->getElementHtml(
                                                    $msg->get_id(), 
                                                    $count,
                                                    $this->private,
                                                    $msg->get_attribute("OBJ_CREATION_TIME"),
                                                    "Neuer Beitrag:",
                                                    $msg->get_attribute("OBJ_DESC"),
                                                    PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                                )
                                            );
                            } else if ($msg->get_attribute("DOC_LAST_MODIFIED") > $this->timestamp) {
                                $updates[] = array(
                                                $msg->get_attribute("DOC_LAST_MODIFIED"), 
                                                $msg->get_id(), 
                                                $this->getElementHtml(
                                                    $msg->get_id(), 
                                                    $count,
                                                    $this->private,
                                                    $msg->get_attribute("DOC_LAST_MODIFIED"),
                                                    "Geänderter Beitrag:",
                                                    $msg->get_attribute("OBJ_DESC"),
                                                    PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                                )
                                            );
                            }
                        }
                        $count++;
                    }
                }
            }
            $count++;
        }
        return $updates;
    }
}
?>
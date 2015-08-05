<?php
namespace PortletSubscription\Subscriptions;

class ForumSubscription extends AbstractSubscription {

    public function getUpdates() {
        $updates = array();
        $threads = $this->object->get_annotations();
        $count = 0;
        foreach ($threads as $thread) {
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
                                        $this->depth == 0 ? "Neues Thema:" : "Neues Thema (in Forum <a href=\"" . PATH_URL . "forum/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        getCleanName($thread, 60, false),
                                        PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                    )
                                );
                } else if ($thread->get_attribute("DOC_LAST_MODIFIED") > $this->timestamp && !(isset($this->filter[$thread->get_id()]) && in_array($thread->get_attribute("DOC_LAST_MODIFIED"), $this->filter[$thread->get_id()]))) {
                    $updates[] = array(
                                    $thread->get_attribute("DOC_LAST_MODIFIED"), 
                                    $thread->get_id(),
                                    $this->getElementHtml(
                                        $thread->get_id(), 
                                        $thread->get_id() . "_" . $count,
                                        $this->private,
                                        $thread->get_attribute("DOC_LAST_MODIFIED"),
                                        $this->depth == 0 ? "Geändertes Thema:" : "Geändertes Thema (in Forum <a href=\"" . PATH_URL . "forum/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        getCleanName($thread, 60, false),
                                        PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                    )
                                );
                }
                if ($thread->get_attribute("OBJ_ANNOTATIONS_CHANGED") > $this->timestamp) {
                    $msgs = $thread->get_annotations();
                    foreach ($msgs as $msg) {
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
                                                    $this->depth == 0 ? "Neuer Beitrag:" : "Neuer Beitrag (in Forum <a href=\"" . PATH_URL . "forum/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                                    getCleanName($msg, 60, false),
                                                    PATH_URL . "forum/showTopic/" . $this->object->get_id() . "/" . $thread->get_id()
                                                )
                                            );
                            } else if ($msg->get_attribute("DOC_LAST_MODIFIED") > $this->timestamp && !(isset($this->filter[$msg->get_id()]) && in_array($msg->get_attribute("DOC_LAST_MODIFIED"), $this->filter[$msg->get_id()]))) {
                                $updates[] = array(
                                                $msg->get_attribute("DOC_LAST_MODIFIED"), 
                                                $msg->get_id(), 
                                                $this->getElementHtml(
                                                    $msg->get_id(), 
                                                    $msg->get_id() . "_" . $count,
                                                    $this->private,
                                                    $msg->get_attribute("DOC_LAST_MODIFIED"),
                                                    $this->depth == 0 ? "Geänderter Beitrag:" : "Geänderter Beitrag (in Forum <a href=\"" . PATH_URL . "forum/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                                    getCleanName($msg, 60, false),
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
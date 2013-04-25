<?php
namespace PortletSubscription\Subscriptions;

class WikiSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $updates = array();
        $articles = $this->object->get_inventory();
        $count = 0;
        foreach ($articles as $article) {
            if ($article instanceof \steam_document && $article->get_attribute("DOC_MIME_TYPE") === "text/wiki") {
                if ($article->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$article->get_id()]) && in_array($article->get_attribute("OBJ_CREATION_TIME"), $this->filter[$article->get_id()]))) {
                    $updates[] = array(
                                    $article->get_attribute("OBJ_CREATION_TIME"), 
                                    $article->get_id(),
                                    $this->getElementHtml(
                                        $article->get_id(), 
                                        $article->get_id() . "_" . $count,
                                        $this->private,
                                        $article->get_attribute("OBJ_CREATION_TIME"),
                                        $this->depth == 0 ? "Neuer Artikel:" : "Neuer Artikel (in Wiki <a href=\"" . PATH_URL . "wiki/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        substr($article->get_name(), 0, strpos($article->get_name(), ".wiki")),
                                        PATH_URL . "wiki/entry/" . $article->get_id() . "/"
                                    )
                                );
                } else if ($article->get_attribute("DOC_LAST_MODIFIED") > $this->timestamp && !(isset($this->filter[$article->get_id()]) && in_array($article->get_attribute("DOC_LAST_MODIFIED"), $this->filter[$article->get_id()]))) {
                    $updates[] = array(
                                    $article->get_attribute("DOC_LAST_MODIFIED"), 
                                    $article->get_id(), 
                                    $this->getElementHtml(
                                        $article->get_id(), 
                                        $article->get_id() . "_" . $count,
                                        $this->private,
                                        $article->get_attribute("DOC_LAST_MODIFIED"),
                                        $this->depth == 0 ? "Geänderter Artikel:" : "Geänderter Artikel (in Wiki <a href=\"" . PATH_URL . "wiki/Index/" . $this->object->get_id() . "/" . "\">" . getCleanName($this->object) . "</a>):",
                                        substr($article->get_name(), 0, strpos($article->get_name(), ".wiki")),
                                        PATH_URL . "wiki/entry/" . $article->get_id() . "/"
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
<?php
namespace PortletSubscription\Subscriptions;

class WikiSubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $updates = array();
        $articles = $this->object->get_inventory();
        foreach ($articles as $article) {
            if ($article instanceof \steam_document && $article->get_attribute("DOC_MIME_TYPE") === "text/wiki" && !in_array($article->get_id(), $this->filter)) {
                if ($article->get_attribute("OBJ_CREATION_TIME") > $this->timestamp) {
                    $updates[] = array(
                                    $article->get_attribute("OBJ_CREATION_TIME"), 
                                    $article->get_id(),
                                    $this->getElementHtml(
                                        $article->get_id(), 
                                        $this->private,
                                        $article->get_attribute("OBJ_CREATION_TIME"),
                                        "Neuer Artikel:",
                                        substr($article->get_name(), 0, strpos($article->get_name(), ".wiki")),
                                        PATH_URL . "wiki/entry/" . $article->get_id() . "/"
                                    )
                                );
                } else if ($article->get_attribute("DOC_LAST_MODIFIED") > $this->timestamp) {
                    $updates[] = array(
                                    $article->get_attribute("DOC_LAST_MODIFIED"), 
                                    $article->get_id(), 
                                    $this->getElementHtml(
                                        $article->get_id(), 
                                        $this->private,
                                        $article->get_attribute("DOC_LAST_MODIFIED"),
                                        "Geänderter Artikel:",
                                        substr($article->get_name(), 0, strpos($article->get_name(), ".wiki")),
                                        PATH_URL . "wiki/entry/" . $article->get_id() . "/"
                                    )
                                );
                }
            }
        }
        return $updates;
    }
}
?>

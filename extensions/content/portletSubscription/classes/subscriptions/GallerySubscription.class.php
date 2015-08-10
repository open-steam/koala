<?php
namespace PortletSubscription\Subscriptions;

class GallerySubscription extends AbstractSubscription {
    
    public function getUpdates() {
        $updates = array();
        $pictures = $this->object->get_inventory();
        $count = 0;
        foreach ($pictures as $picture) {
            if ($picture instanceof \steam_document) {
                if ($picture->get_attribute("OBJ_CREATION_TIME") > $this->timestamp && !(isset($this->filter[$picture->get_id()]) && in_array($picture->get_attribute("OBJ_CREATION_TIME"), $this->filter[$picture->get_id()]))) {
                    $updates[] = array(
                                    $picture->get_attribute("OBJ_CREATION_TIME"), 
                                    $picture->get_id(),
                                    $this->getElementHtml(
                                        $picture->get_id(), 
                                        $picture->get_id() . "_" . $count,
                                        $this->private,
                                        $picture->get_attribute("OBJ_CREATION_TIME"),
                                        "Neues Bild: ". \PortletSubscription::getNameForSubscription($picture) ." (in Fotoalbum <a href=\"" . PATH_URL . "photoAlbum/Index/" . $this->object->get_id() . "/" . "\">" . \PortletSubscription::getNameForSubscription($this->object) . "</a>)",
                                        "",
                                        PATH_URL . "gallery/Index/" . $this->object->get_id() . "/"
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

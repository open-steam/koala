<?php

namespace PortletBookmarks\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IIdCommand {

    private $params;
    private $id;
    private $content;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->id = $requestObject->getId();
        $user = \lms_steam::get_current_user();
        $bookmarkRoom = $user->get_attribute("USER_BOOKMARKROOM");

        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $numberOfBookmarks = $obj->get_attribute("PORTLET_BOOKMARK_COUNT");

        $bookmarks = $bookmarkRoom->get_inventory();

        $n = $numberOfBookmarks;
        if (count($bookmarks) <= $numberOfBookmarks) {
            $n = count($bookmarks);
        }
        for ($i = 0; $i < $n; $i++) {
            //Erzeuge eine grafische Ausgabe!!!!
        }
        
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml("Hallo");
        $idResponseObject->addWidget($rawHtml);
        return $idResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
       
        
        
        return $frameResponseObject;
    }

}

?>
<?php

namespace Map\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        if(!($object instanceof \steam_document)){
            throw new \Exception("object isn't an instance of steam_document");
        }
        $objName = $object->get_name();
        if ((strpos($objName, ".kml") === false) && (strpos($objName, ".kmz") === false)){
            throw new \Exception("object isn't a map");
        }
        $actionBar = new \Widgets\ActionBar();
        $downloadUrl = getDownloadUrlForObjectId($this->id);
        $actionBar->setActions(array(
            array("name" => "URL in neuem Fenster öffnen", "onclick" => "javascript:window.open('http://maps.google.de/maps?f=q&hl=de&q=" . $downloadUrl . "');return false;")
        ));

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml("<iframe height=\"800px\" width=\"100%\" src=\"http://maps.google.de/maps?f=q&hl=de&q=" . $downloadUrl . "&output=embed\" scrolling=\"yes\"></iframe>");
        $frameResponseObject->setTitle($objName);
        $frameResponseObject->addWidget($actionBar);
        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
        return $frameResponseObject;
    }

}

?>
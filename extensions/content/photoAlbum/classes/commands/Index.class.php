<?php

namespace PhotoAlbum\Commands;

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
        if($this->id === ""){
            echo "Fehlermeldung";die;
        }
        $gallery = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        echo "Läuft!";die;
        return $frameResponseObject;
    }

}

?>
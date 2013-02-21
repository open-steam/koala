<?php

namespace Postbox\Commands;

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
        //Fallunterscheidung, ob Abgeber, oder Bewerterrolle
        
        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        
        $checkAccessWrite = $obj->check_access_write();
        $checkAccesRead = $obj->check_access_read();
        
        if($checkAccessWrite){
            
            
        } else if($checkAccesRead){
            //Benutzer darf Dokumente einreichen
            echo "Ihre Rolle ist Abgeber!";die;
            
        }else{
            echo "Keine Zugriffsrechte!";die;
            //Leider kein Zugriff
        }
        return $frameResponseObject;
    }

}

?>
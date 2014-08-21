<?php

namespace Ellenberg\Commands;
 //this class is used if the user wants to create a new ellenberg object
//it manages the setup with the remote Ellenberg-server
class Create extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {
    
    private $params;
    private $name;
    private $id;
    private $userName;
    private $userId;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }
    
    private function communicateWithEllenbergServer($values){
        if($values == null) {
            throw new Exception("no values given...");
        }
        
        //we have to encode our values
        $valuesJsonEncoded = json_encode($values);
        
        //Initialize a cURL session
        $curlSession = curl_init();
       
        //set the path where we want to send our message
        curl_setopt($curlSession, CURLOPT_URL, 'http://amole.cs.upb.de/webapp/api/createScenario ');
        //via POST (not GET)
        curl_setopt($curlSession, CURLOPT_POST, 1);
        
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, $valuesJsonEncoded);
        
        // Timeout in 10 seconds
        curl_setopt($curlSession, CURLOPT_TIMEOUT, 10);
        
        //we want curl_exec to return the result, not to output it
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        
        $answer = curl_exec($curlSession);
        
        curl_close ($curlSession);
        
        return json_decode($answer, true);
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
            isset($this->params["name"]) ? $this->name = $this->params["name"] : "";
        }
        
        $this->userName = $GLOBALS["STEAM"]->get_current_steam_user()->get_name();
        $this->userId = $GLOBALS["STEAM"]->get_current_steam_user()->get_id();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        //get the room, the user wants to create the new object in
        //if no id is set, use the Documentroot of the user
        if ($this->id === "") {
            $envRoom = $GLOBALS["STEAM"]->get_current_steam_user()->get_workroom();
            $this->id = $envRoom->get_id();
        } else {
            $envRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        }
        //create a new room without any properties
        $ellenbergObject = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(),$this->name, $envRoom);

        
        //set up the array with the values
        $values = array(
            "user_name" => $this->userName,
            "webdav_url" => "http://www.bid-owl.de/webdav/id/".$ellenbergObject->get_id()
        );
        
        
        
        $decodedAnswer = $this->communicateWithEllenbergServer($values);
        //the decodedAnswer should be of the form
        //{
        //  "ellenberg_id" => {"8-stellige id" | "error"},
        //}
        
        if($decodedAnswer['ellenberg_id'] == "error") {
            //TODO: eventually delete the created object... $ellenbergObject
            throw new \Exception ("There went something wrong with the creation of the Elelnberg-Object on the remote server.");
            
        }
        if($decodedAnswer['ellenberg_id'] == "" || $decodedAnswer['ellenberg_id'] == "0")
        {
            throw new \Exception ("The value is incorrect");
        }
        

        //get the generated unique id from the ellenberg-server-response and set it
        $ellenbergObject->set_attribute("ELLENBERG_ID", $decodedAnswer['ellenberg_id']);
        //set the OBJ_TYPE to 'ellenberg' to recognize objects of this type
        $ellenbergObject->set_attribute("OBJ_TYPE", "ellenberg");



        //use to reload the folder with the new object
        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setJs(<<<END
                closeDialog();
		sendRequest("LoadContent", {"id":"{$this->id}"}, "explorerWrapper", "updater", null, null, "explorer");
END
            );
        $ajaxResponseObject->addWidget($jswrapper);
        
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        
    }

}

?>
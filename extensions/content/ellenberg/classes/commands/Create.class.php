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
    //the url where the ellenbergtool cann access the data
    private $webdavURL = "http://webdav.bid-owl.de/id/";
    //the API URL from the ellenbergtool
    private $ellenbergURL = "http://amole.cs.upb.de/webapp/api/createScenario";
    //the room where we want to create the object in
    private $envRoom;

    public function validateData(\IRequestObject $requestObject) {
        //nothing to validate here
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
        curl_setopt($curlSession, CURLOPT_URL, $this->ellenbergURL);
        //via POST (not GET)
        curl_setopt($curlSession, CURLOPT_POST, 1);
        //add the values as post parameters
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
        
        $this->userName = \lms_steam::get_current_user()->get_name();
        $this->userId = \lms_steam::get_current_user()->get_id();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        //get the room, the user wants to create the new object in
        //if no id is set, use the Documentroot of the user
        if ($this->id === "") {
            $this->envRoom = \lms_steam::get_current_user()->get_workroom();
            $this->id = $this->envRoom->get_id();
        } else {
            $this->envRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        }
        //create a new room without any properties
        $ellenbergObject = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(),$this->name, $this->envRoom);

        //TODO
        $webdavExportUrl = $this->webdavURL.$ellenbergObject->get_id(); //correct path
        //$webdavExportUrl = $this->webdavURL."1043435"; //test
        
        //set up the array with the values
        $values = array(
            "user_name" => $this->userName,
            "webdav_url" => $webdavExportUrl
        );
        
        
        
        $decodedAnswer = $this->communicateWithEllenbergServer($values);
        //the decodedAnswer should be an array of the form
        //{
        //  "ellenberg_id" => {"8-stellige id" | "error"},
        //}
        
        if($decodedAnswer['ellenberg_id'] == "error") {
            //we delete the recently created object
            $ellenbergObject->delete();
            throw new \Exception ("There went something wrong with the creation of the Elelnberg-Object on the remote server.");
            
        }
        
        //if the returned id is empty, 0 or shorter or longer then 8 throw an exception
        if($decodedAnswer['ellenberg_id'] == "" || $decodedAnswer['ellenberg_id'] == "0" || strlen($decodedAnswer['ellenberg_id']) != 8 ) {
            //we delete the recently created object
            $ellenbergObject->delete();
            throw new \Exception ("The returned id is incorrect");
        }
        

        //get the generated unique id from the ellenberg-server-response and set it
        $ellenbergObject->set_attribute("ELLENBERG_ID", $decodedAnswer['ellenberg_id']);
        //set the OBJ_TYPE to 'ellenberg' to recognize objects of this type
        $ellenbergObject->set_attribute("OBJ_TYPE", "ellenberg");



        //use this to reload the folder with the new object
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
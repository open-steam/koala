<?php
namespace Sanction\Commands;

class Save extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $order;
    private $object;
    private $sanctionId;
    private $sanctions;
    private $className = null;

    public static function getSanctionConstant($sanctionString){

        switch ($sanctionString){
            case "read":
                return SANCTION_READ;

            case "write":
                return SANCTION_WRITE;

            case "execute":
                return SANCTION_EXECUTE;

            case "move":
                return SANCTION_MOVE;
        
            case "insert":
                return SANCTION_INSERT;

            case "sanction":
                return SANCTION_SANCTION;

            case "annotate":
                return SANCTION_ANNOTATE;
             
        }
    }

    


    public function validateData(\IRequestObject $requestObject) {

        return true;

    }

    public function processData(\IRequestObject $requestObject) {

        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        $this->order = $this->params["order"];
        $this->value = $this->params["value"];
        if(isset($this->params["className"])){ 
            $this->className = $this->params["className"];
        }

        //decode the JSON String to an JSON Object
        $this->sanctionsDecoded = json_decode($this->value);
        
        $this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        switch ($this->order){
            
            //sets the rights with the ticks in the matrix
            case "detailed":
                
                //traverse over the input JSON Object to sum up the users rights on an object to on variable
                $addedSanctionsPerUser = $this->object->get_sanction();
                
                //set every value in this array to 0
                //$setNull = ;
                $addedSanctionsPerUser = array_map(function($value) { return 0; }, $addedSanctionsPerUser);
                
                //then sum up the rights the user has
                foreach($this->sanctionsDecoded as $sanction=>$value){
                    //split the string at each underscore, 
                    //the syntax is sanction_objectID_(read,write,execute,mode,insert,annotate,sanction)
                    //i.e. sanction_72_read
                    $right = explode ("_", $sanction);
                    $holderOfRightsId = $right[1];
                    
                    $sanctionString = $right[2];

                    if($value){
                        //if the sanction is set to true, 
                        //add the specific sanction constant-value to the int value that is to be set in the next step
                        $addedSanctionsPerUser[$holderOfRightsId] |= $this::getSanctionConstant($sanctionString);
                    }
                }
                
                //traverse the $addedSanctionsPerUser Array 
                //and set the sanction-value for each user listed
                foreach ($addedSanctionsPerUser as $holderOfRightsId => $sanction){

                    $holderOfRightsObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $holderOfRightsId, CLASS_OBJECT);
                    $ret1 = $this->object->sanction($sanction, $holderOfRightsObject);
                    
                    var_dump($ret1 . " ". $sanction);
                    var_dump($this->object->get_sanction());
                    //sanction_meta auf SANCTION_ALL setzen, wenn SANCTION_SANCTION gesetzt ist
                    if($this->object->check_access(SANCTION_SANCTION, $holderOfRightsObject)){
                        
                    $ret = $this->object->sanction_meta(SANCTION_ALL, $holderOfRightsObject);
                    //die("hier ". $ret. "    ".SANCTION_ALL);
                    }
                    

                    $holderOfRightsObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $holderOfRightsId);
                    $this->object->sanction($sanction, $holderOfRightsObject);
                    $this->object->sanction_meta($sanction, $holderOfRightsObject); //test

                }
                die("ende");
            break;
            
            //sets or unsets the acquiering of sanctions
            //acquiring rights disables the setting of rights explicitly
            case "acquire":
                $currentUser = \lms_steam::get_current_user();
                if ($this->value == "true") {
                    
                    $this->object->set_acquire_from_environment();
                    
                    //unset every other sanction on this object
                    $sanction = $this->object->get_sanction();
                    foreach ($sanction as $id => $sanct) {
                        if ($id !== $currentUser->get_id()) {
                            $this->object->sanction(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id, CLASS_OBJECT));
                           // $this->object->sanction_meta(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id, CLASS_OBJECT));
                        }
                    }
                } else {

                    //give the current user all rights and set the acquiring to false
                    $this->object->sanction(SANCTION_ALL, $currentUser);
                   // $this->object->sanction_meta(SANCTION_ALL, $currentUser);
                    $this->object->set_acquire(0);
                }
               
            break;
            
            case "crude":
                $sanction = $this->object->get_sanction();
                $everyone = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "everyone");
                $everyoneId = $everyone->get_id();
                $steamGroup = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "sTeam");
                $steamGroupId = $steamGroup->get_id();
                $currentUser = \lms_steam::get_current_user();
                $currentUserId = $currentUser->get_id();

                if ($this->value == "privat") {
                    
                    foreach ($sanction as $userOrGroupId => $sanct) {
                        if ($currentUserId != $userOrGroupId) {
                            
                            $this->object->sanction(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $userOrGroupId, CLASS_OBJECT));
                         //   $this->object->sanction_meta(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $userOrGroupId, CLASS_OBJECT));
                        }
                    }
                } elseif ($this->value == "user_public") {
                    
                    $this->object->sanction(SANCTION_READ, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $steamGroupId, CLASS_OBJECT));
                    $this->object->sanction(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $everyoneId, CLASS_OBJECT));
                   // $this->object->sanction_meta(SANCTION_READ, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $steamGroupId, CLASS_OBJECT));
                    
                } elseif ($this->value == "server_public") {
                    $this->object->sanction(SANCTION_READ, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $everyoneId, CLASS_OBJECT));
                   // $this->object->sanction_meta(SANCTION_READ, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $everyoneId, CLASS_OBJECT));
                }
                
                
                
                
                
            break;
            
            
            case "addUserOrGroup":
                
                if($this->className != null){
                
                    
                    if($this->className == "user"){
                       
                        $user = \steam_factory::username_to_object($GLOBALS["STEAM"]->get_id(), $this->value);
                        if($user instanceof \Steam_user){
                            $id = $user->get_Id();
                        } else {
                            throw new Exception("Konnte keinen Nutzer zu dem Namen ".$this->value." finden");
                        }
                    } else if($this->className == "group"){
                       
                        $group = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), $this->value);
                        
                        if($group instanceof \Steam_group){
                            $id = $group->get_Id();
                        } else {
                            throw new Exception("Konnte keine Gruppe zu dem Namen ".$this->value." finden");
                        }
                    }
                    $this->object->sanction(SANCTION_READ, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id, CLASS_OBJECT));
                    $this->object->sanction_meta(SANCTION_READ, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id, CLASS_OBJECT)); //test

                }
                
                
            break;
                
        } 
         $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>
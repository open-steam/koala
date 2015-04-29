<?php

//favoriten, die einmal das recht bekommen haben, dann aber kein fav. mehr sind betrachten. geht momentan nicht, weil ich noch keine rechte zuweisen kann.
//sind favoriten nur user oder user and groups? (beide)
//ober und untergruppen (in der untergruppe, aber nicht in der obergruppe)?
//bei der gruppen anzeige Beschreibung (Interner Gruppenname) wie im Rechtedialog
//evtl. bei loadAdditionalRights noch den äußeren container berücksichtigen
namespace Postbox\Commands;

class Sanctions extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $postboxObject;
    private $innerContainer;
    private $postboxObjectId;
    private $postboxObjectName;
    private $postboxObjectType;
    private $steam;
    private $creator;
    private $creatorId;
    private $creatorFullName;
    private $currentUser;
    private $dialog;
    private $content;
    private $users;
    private $groups;
    private $groupMapping;
    private $userMapping;
    

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->postboxObjectId = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->postboxObjectId = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        
        $this->initialiseVariables();
        
        $ajaxResponseObject->setStatus("ok");
        
        //check if the object is a postbox and if the user has appropriate rights
        $checkResult = $this->checkObjectTypeAndSanction($ajaxResponseObject);
        if($checkResult !== null) { return $checkResult;}
           

        $this->dialog = new \Widgets\Dialog();
        $this->dialog->setTitle("Rechte von »" . $this->postboxObjectName . "«");

        $this->loadGroupsAndUsers();
        $this->loadAdditionalRights();
        
        $groupMappingA = array();
        //$groupMappingName = array();
        foreach ($this->groups as $g) {
            $id = $g->get_id();
            $name = $g->get_groupname();
            $groupMappingA[$id] = $name;
            //$groupMappingName[$name] = $id;
        }
        //if an user is in a subgroup but not the superior group, the superior group is not displayed
        /*foreach ($groupMappingA as $name) {
            $array = explode(".", $name);
            $length = count($array);
            if ($length > 1) {

                $string = "";
                for ($i = 0; $i < $length; $i++) {
                    if ($i == 0) {
                        $string .= $array[$i];
                        if (!isset($groupMappingName[$string])) {
                            $group = \steam_factory::get_group($this->steam->get_id(), $string);
                            $groupId = $group->get_id();
                            $groupMappingName[$string] = $groupId;
                            $groupMappingA[$groupId] = $string;
                            $this->groups[$groupId] = $group;
                        }
                    } else {
                        $string .= "." . $array[$i];
                        if (!isset($groupMappingName[$string])) {
                            $group = \steam_factory::get_group($this->steam->get_id(), $string);
                            $groupId = $group->get_id();
                            $groupMappingName[$string] = $groupId;
                            $groupMappingA[$groupId] = $string;
                            $this->groups[$groupId] = $group;
                        }
                    }
                }
            }
        }*/
       //SORT_NATURAL | SORT_FLAG_CASE prevents the ASCII orgering (ABC...abc)
        asort($groupMappingA, SORT_NATURAL | SORT_FLAG_CASE);
        

        //order the groups alphabetically
        foreach ($groupMappingA as $id => $name) {
            $this->groupMapping[$id] = $this->groups[$id];
        }


        //MAPPING USER
        $this->userMapping = array();
        foreach ($this->users as $id => $user) {
            if ($user instanceof \steam_user) {
                $this->userMapping[$id] = $user->get_full_name();
            }
        }
        asort($this->userMapping, SORT_NATURAL | SORT_FLAG_CASE);

        //Creator
        $this->content->setVariable("CREATOR_FULL_NAME", $this->creatorFullName);

        //die Speicherung sollte an den mechanismus der Dialogbox angepasst werden, sodass dieser genutzt werden kann (Abbrechenbutton)
        //$this->content->setVariable("SEND_REQUEST_SANCTION", 'sendRequest("UpdateSanctions", { "id": ' . $this->postboxObjectId . ', "sanctionId": id, "type": "sanction", "value": value }, "", "data", function(response){jQuery(\'#dynamic_wrapper\').remove(); jQuery(\'#overlay\').remove(); sendRequest(\'Sanctions\', {\'id\':\'' . $this->objectId . '\'}, \'\', \'popup\', null, null, \'explorer\');}, null, "explorer");');
        //$this->content->setVariable("SEND_REQUEST_CRUDE", 'sendRequest("UpdateSanctions", { "id": ' . $this->postboxObjectId . ', "type": "crude", "value": value }, "", "data", function(response){jQuery(\'#dynamic_wrapper\').remove(); jQuery(\'#overlay\').remove(); sendRequest(\'Sanctions\', {\'id\':\'' . $this->objectId . '\'}, \'\', \'popup\', null, null, \'explorer\');}, null, "explorer");');

        
        //build the template for the second teacher
        if (count($this->userMapping) == 0 && count($this->groupMapping) == 0) {
            $this->content->setVariable("NO_USERS", "Sie haben keine Favoriten.");
        } else {
            $this->content->setVariable("NO_USERS", "niemand");
            $this->addUsersToList("VIEW_POSTBOX");
            $this->addGroupsToList("VIEW_POSTBOX");
        }
        
        //build the dropdown list for the pupils
        if (count($this->groupMapping) != 0) {
            $this->addGroupsToList("INSERT_POSTBOX");
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($this->content->get());
        $this->dialog->addWidget($rawHtml);

        $ajaxResponseObject->addWidget($this->dialog);
        
        return $ajaxResponseObject;
    }
    
    /**
     * outsource the initialisation of all variables
     */
    private function initialiseVariables(){
        
        $this->steam = $GLOBALS["STEAM"];
        $this->postboxObject = \steam_factory::get_object($this->steam->get_id(), $this->postboxObjectId);
        
        $this->postboxObjectName = getCleanName($this->postboxObject);
        $this->postboxObjectType = $this->postboxObject->get_attribute("OBJ_TYPE");
        
        $this->currentUser = \lms_steam::get_current_user();
     
        $this->creator = $this->postboxObject->get_creator();
        $this->creatorId = $this->creator->get_id();
        
        if ($this->creator instanceof \steam_user) {
            $this->creatorFullName = $this->creator->get_full_name();
        } else {
            $this->creatorFullName = getCleanName($this->creator);
        }
        
        $this->innerContainer = $this->postboxObject->get_attribute("bid:postbox:container");
        
        $this->groups = array();
        $this->favorites = array();
        $this->users = array();
        $this->groupMapping = array();
        
        $this->content = \Postbox::getInstance()->loadTemplate("sanction.template.html");
    }
    
    /**
     * Checks if the object is a postbox and if the current user has the rights to view this postbox
     * @param type $ajaxResponseObject
     * @return type returns null if everything is fine
     */
    private function checkObjectTypeAndSanction($ajaxResponseObject){
        
         if ($this->postboxObjectType !== "postbox") {
            $labelDenied = new \Widgets\RawHtml();
            $labelDenied->setHtml("Dieser Dialog ist nur für Abgabefächer geeignet!");
            $dialogDenied = new \Widgets\Dialog();
            $dialogDenied->setTitle("Rechte von »" . $this->postboxObjectName . "«");
            $dialogDenied->addWidget($labelDenied);
            $ajaxResponseObject->addWidget($dialogDenied);
            return $ajaxResponseObject;
        }

        if (!$this->postboxObject->check_access(SANCTION_SANCTION)) {
            $labelDenied = new \Widgets\RawHtml();
            $labelDenied->setHtml("Sie haben keine Berechtigung die Rechte einzusehen oder zu verändern!");
            $dialogDenied = new \Widgets\Dialog();
            $dialogDenied->setTitle("Rechte von »" . $this->postboxObjectName . "«");
            $dialogDenied->addWidget($labelDenied);
            $ajaxResponseObject->addWidget($dialogDenied);
            return $ajaxResponseObject;
        }
        return null;
    }
    /**
     * Save each favorite of this user in the users or groups array
     * @throws \Exception if the favorites contain an object of another type then steam_user or steam_group
     */
    private function loadGroupsAndUsers(){
        
        //get favorites as an array of user/group objects
        $rawFavorites = $this->currentUser->get_buddies();
        
        //build an associative array
        foreach ($rawFavorites as $rawFavorite) {
            
            //and sort the favorites in the right array
            if ($rawFavorite instanceof \steam_user) {
                $this->users[$rawFavorite->get_id()] = $rawFavorite;
            } else if ($rawFavorite instanceof \steam_group) {
                $this->groups[$rawFavorite->get_id()] = $rawFavorite;
            } else {
                throw new \Exception("Favoriten beeinhalten das Objekt einer ungültigen Klasse!");
            }
        }
        
        
        
        $steamgroup=  \steam_factory::groupname_to_object($this->steam->get_id(), "sTeam");
        $rawGroups = $this->currentUser->get_groups();
        
        //build an associative array
        foreach ($rawGroups as $group) {
            if($group == $steamgroup) {continue;}
            $this->groups[$group->get_id()] = $group;
        }
    }

    /**
     * Checks if there are other users/groups then the favorites that have rights for this postbox and add the additional user/group to the array
     * @throws \Exception if there is an illegal object
     */
    private function loadAdditionalRights(){
        $innerContainerSanction = $this->innerContainer->get_sanction();
        
        foreach ($innerContainerSanction as $id => $sanction) {
            //check if there are objects that have rights for the inner container too
            if (!array_key_exists($id, $this->groups) &&
                !array_key_exists($id, $this->users) &&
                $id != $this->creatorId &&
                $id != 0 &&
                $id != \steam_factory::groupname_to_object($this->steam->get_id(), "sTeam")->get_id()) { //$id != $everyoneId
                //get the additional object and put it in the right array
                $additionalObject = \steam_factory::get_object($this->steam->get_id(), $id);
                if ($additionalObject instanceof \steam_group) {
                    $this->groups[$id] = $additionalObject;
                } else if ($additionalObject instanceof \steam_user) {
                    $this->users[$id] = $additionalObject;
                } else {
                    throw new \Exception("Ungültiger Objekttyp hat Rechte an dem aktuellen Objekt.");
                }
            }
        } 
    }
    
    /**
     * Inserts the $this->groups array into the template
     * @param type $templateBlock the templateblock in which the groups are inserted
     */
    private function addGroupsToList($templateBlock){

        foreach ($this->groupMapping as $id => $name) {
            $group = $this->groups[$id];

            if ($group instanceof \steam_group) {
                $this->content->setCurrentBlock($templateBlock);

                //check if the user has SANCTION_SANCTION rights, then mark this user as active
                //check if the user has rights SANCtION_ALL for the outer and the inner container
                $sanctionCheckInnerContainer = $this->innerContainer->check_access(SANCTION_SANCTION, $group);
                $sanctionCheckOuterContainer = $this->postboxObject->check_access(SANCTION_SANCTION, $group);

                //mark the current user with the view rights
                if($sanctionCheckInnerContainer && $sanctionCheckOuterContainer)
                {
                    $this->content->setVariable("SELECTED", "selected");
                }
                $this->content->setVariable("OBJECT_NAME", $group->get_attribute("OBJ_DESC")." (".$group->get_groupname().")");
                $this->content->setVariable("OBJECT_ID", $id);
                $this->content->parse($templateBlock);
            }
        }
    }
    
    /**
     * Inserts the $this->users array into the template
     * @param type $templateBlock the templateblock in which the groups are inserted
     */
    private function addUsersToList($templateBlock){
        
        foreach ($this->userMapping as $id => $name) {
            $user = $this->users[$id];
            
            if ($user instanceof \steam_user) {
                $this->content->setCurrentBlock($templateBlock);
                
                //check if the user has SANCTION_SANCTION rights, then mark this user as active
                //check if the user has rights SANCtION_ALL for the outer and the inner container
                $sanctionCheckInnerContainer = $this->innerContainer->check_access(SANCTION_SANCTION, $user);
                $sanctionCheckOuterContainer = $this->postboxObject->check_access(SANCTION_SANCTION, $user);

                //mark the current user with the view rights
                if($sanctionCheckInnerContainer && $sanctionCheckOuterContainer)
                {
                    $this->content->setVariable("SELECTED", "selected");
                }
                $this->content->setVariable("OBJECT_NAME", $name);
                
                $this->content->setVariable("OBJECT_ID", $id);
                $this->content->parse($templateBlock);
            }
        }
    }

}

?>
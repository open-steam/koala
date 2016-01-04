<?php
namespace Postbox\Commands;

class Sanctions extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $postboxObject;
    private $innerContainer;
    private $requiredSanctionsForInnerContainer;
    private $postboxObjectId;
    private $postboxObjectName;
    private $postboxObjectType;
    private $steam;
    private $steamGroup;
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
        //print_r($this->params);die();
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        if(isset($this->params['type']) && ($this->params['type'] == 'admin_postbox' || $this->params['type'] == 'insert_postbox')){
            return $this->updateSanctions($ajaxResponseObject);
        } else {
            return $this->displayRightsDialog($ajaxResponseObject);
        }
    }

    /**
     * This method displays the dialog to set the person or group to administrate the postbox or insert documents.
     * @param type $ajaxResponseObject propagate the responseObject
     * @return type returns the $ajaxResponseObject up to the caller
     */
    private function displayRightsDialog($ajaxResponseObject) {

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
        //asort($groupMappingA, SORT_NATURAL | SORT_FLAG_CASE);
        //this flags are not supportet in the current PHP version on the server
        asort($groupMappingA);


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
        //asort($this->userMapping, SORT_NATURAL | SORT_FLAG_CASE);
        asort($this->userMapping);

        //build the dropdown list for the second admin
        if (count($this->userMapping) > 0 || count($this->groupMapping) > 0) {
            $this->content->setVariable("NO_USERS", "nur ich");
            $this->addUsersToList("ADMIN_POSTBOX", SANCTION_ALL);
            $this->addGroupsToList("ADMIN_POSTBOX", true);

        } else {
            $this->content->setVariable("NO_USERS", "Sie haben keine Favoriten.");
        }

        //build the dropdown list for the insert groups
        if (count($this->groupMapping) > 0) {
            $this->addGroupsToList("INSERT_POSTBOX", false);
        }

        $this->content->setVariable("SEND_REQUEST_ADMIN_POSTBOX",  "sendRequest('Sanctions', { 'id': " . $this->postboxObjectId . ",'type': 'admin_postbox',  'value': admin_postbox} , '', 'data', function(response){dataSaveFunctionCallback(response);}, null, 'postbox');");
        $this->content->setVariable("SEND_REQUEST_INSERT_POSTBOX", "sendRequest('Sanctions', { 'id': " . $this->postboxObjectId . ",'type': 'insert_postbox', 'value': insert_postbox}, '', 'data', function(response){dataSaveFunctionCallback(response);}, null, 'postbox');");

        if(ltrim($this->currentUser, '#') == $this->creatorId){
          //current user is creator
          $this->content->setVariable("DESCRIPTION", "<p>Sie sind der Besitzer dieses Briefkastens und besitzen volle Zugriffsrechte. An dieser Stelle können Sie festlegen, welche Gruppen Abgaben einreichen dürfen. Zudem können Sie einer weiteren Person oder einer Gruppe das Recht einräumen, diesen Briefkasten ebenfalls verwalten zu können (diese erhält die vollen Zugriffsrechte abgesehen von dem Recht, die Verwaltung zu ändern).</p>");
          $this->content->setVariable("MANAGE", "");
        }
        else{
          $this->content->setVariable("DESCRIPTION", "<p>Der Besitzer dieses Briefkastens ist " . $this->creatorFullName . ". Er hat Ihnen das Recht eingeräumt, den Briefkasten ebenfalls verwalten zu können. An dieser Stelle können Sie daher festlegen, welche Gruppen Abgaben einreichen dürfen.</p>");
          $this->content->setVariable("MANAGE", 'style="display:none;"');
        }

        $sanctionURL = "http://$_SERVER[HTTP_HOST]" . "/Sanction/Index/" . $this->params['id'] . "/";

        $admins = \steam_factory::groupname_to_object($GLOBALS[ "STEAM" ]->get_id(), "SchulAdmins");
        $isAdmin = $admins->is_member($this->currentUser);
        if($isAdmin){
          $this->dialog->setCustomButtons(array(array("class" => "button pill", "js" => "window.open('$sanctionURL', '_self')", "label" => "Erweiterte Ansicht öffnen")));
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($this->content->get());
        $this->dialog->addWidget($rawHtml);

        $ajaxResponseObject->addWidget($this->dialog);

        return $ajaxResponseObject;
    }

    /**
     * This method updates the rights to administrate or insert the postbox object.
     * @param type $ajaxResponseObject propagate the responseObject
     * @return type returns the $ajaxResponseObject up to the caller
     */
    private function updateSanctions($ajaxResponseObject) {

        $this->initialiseMinimumVariables();
        $newUserOrGroupId = $this->params['value'];
        $innerContainerSanction = $this->innerContainer->get_sanction();
        $postboxObjectSanction = $this->postboxObject->get_sanction();
        $adminRights = SANCTION_ALL;

        if($this->params['type'] == 'admin_postbox'){
            //at first give the new user all rights, then remove the old (every other) user with all rights
            //if the $newUserOrGroupId is zero (no other user as an admin) then just remove every other user with all rights
            if($newUserOrGroupId != 0) {
                $this->postboxObject->sanction($adminRights, \steam_factory::get_object($this->steam->get_id(), $newUserOrGroupId));
                $this->innerContainer->sanction($adminRights, \steam_factory::get_object($this->steam->get_id(), $newUserOrGroupId));

            }

            foreach ($innerContainerSanction as $id => $sanction) {
                //if the current user isn't the new one and if the user doesn't have all rights, then unset him/her
                if ($id != $newUserOrGroupId && $sanction == $adminRights) {
                    $this->innerContainer->sanction(ACCESS_DENIED, \steam_factory::get_object($this->steam->get_id(), $id));
                }
            }

            foreach ($postboxObjectSanction as $id => $sanction) {
                //if the current user isn't the new one and if the user doesn't have all rights, then unset him/her
                if ($id != $newUserOrGroupId && $sanction == $adminRights) {
                    $this->postboxObject->sanction(ACCESS_DENIED, \steam_factory::get_object($this->steam->get_id(), $id));
                }
            }

        } else if($this->params['type'] == 'insert_postbox'){

            //give the new group the insert right (and if neccessary the SANCTION_READ right), then remove the old (all other) users with only the insert right
            //if the $newUserOrGroupId is zero (no other group as an submitter) then just remove every other group with only insert rights
            if($newUserOrGroupId != 0) {
                $this->innerContainer->sanction($this->requiredSanctionsForInnerContainer, \steam_factory::get_object($this->steam->get_id(), $newUserOrGroupId));
            }

            foreach ($innerContainerSanction as $id => $sanction) {
                //if the current user isn't the new one and if the user doesn't have all rights, then unset him/her
                if ($id != $newUserOrGroupId && $sanction != $adminRights) {
                  $this->innerContainer->sanction(ACCESS_DENIED, \steam_factory::get_object($this->steam->get_id(), $id));
                }
            }
        }

        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

    /**
     * this variables are needed in both usecases (display and update)
     */
    private function initialiseMinimumVariables() {
        $this->steam = $GLOBALS["STEAM"];
        $this->postboxObject = \steam_factory::get_object($this->steam->get_id(), $this->postboxObjectId);
        $this->innerContainer = $this->postboxObject->get_attribute("bid:postbox:container");

        //configure sanctions for inner container
        $this->requiredSanctionsForInnerContainer =  SANCTION_INSERT | SANCTION_READ;
        if (defined("API_DOUBLE_FILENAME_NOT_ALLOWED") && (!(API_DOUBLE_FILENAME_NOT_ALLOWED))){
            //if API_DOUBLE_FILENAME_NOT_ALLOWED is false, we only need INSERT rights (and don't need to check whether there already exists a file with the same name)
            $this->requiredSanctionsForInnerContainer = SANCTION_INSERT;
        }
    }


    /**
     * outsource the initialisation of all variables needed for the displaying
     */
    private function initialiseVariables(){

        $this->initialiseMinimumVariables();

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

        $this->groups = array();
        $this->favorites = array();
        $this->users = array();
        $this->groupMapping = array();

        $this->content = \Postbox::getInstance()->loadTemplate("sanction.template.html");
        $this->steamgroup = \steam_factory::groupname_to_object($this->steam->get_id(), "sTeam");
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
        $rawGroups = $this->currentUser->get_groups();

        //build an associative array
        foreach ($rawGroups as $group) {
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
                $id != 0 ) {
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
     * @param type $postboxAdmin true if it is the list for the admins, false if it is the list for the pupils
     */
    private function addGroupsToList($templateBlock, $postboxAdmin){
        foreach ($this->groupMapping as $id => $name) {
            $group = $this->groups[$id];

            if ($group instanceof \steam_group) {
                $this->content->setCurrentBlock($templateBlock);

                //check if the user has the given rights, then mark this user as active
                if($postboxAdmin){
                    $sanctionCheckInnerContainer = $this->innerContainer->check_access(SANCTION_ALL, $group);
                    $sanctionCheckOuterContainer = $this->postboxObject->check_access(SANCTION_ALL, $group);
                } else {
                    $sanctionCheckInnerContainer = $this->innerContainer->check_access($this->requiredSanctionsForInnerContainer, $group);
                    $sanctionCheckOuterContainer = true;
                }
                //mark the current user with the view rights
                if($sanctionCheckInnerContainer && $sanctionCheckOuterContainer){
                    $this->content->setVariable("SELECTED", "selected");
                }
                $gDESC = "";
                $groupDESC = $group->get_attribute("OBJ_DESC");
                if($groupDESC != ""){
                  $gDESC = " (" . $groupDESC . ")";
                }
                $this->content->setVariable("OBJECT_NAME", (($id == $this->steamgroup->get_id())? "Alle angemeldeten Benutzer": $group->get_groupname() . $gDESC));
                $this->content->setVariable("OBJECT_ID", $id);
                $this->content->parse($templateBlock);
            }
        }
    }

    /**
     * Inserts the $this->users array into the template
     * @param type $templateBlock the templateblock in which the groups are inserted
     * @param type $sanctionToCheck the sanction constant to check the access for
     */
    private function addUsersToList($templateBlock, $sanctionToCheck){

        foreach ($this->userMapping as $id => $name) {
            $user = $this->users[$id];

            $isAdmin = \lms_steam::is_steam_admin($user);

            if ($user instanceof \steam_user && !$isAdmin) {
                $this->content->setCurrentBlock($templateBlock);

                //check if the user has the given rights, then mark this user as active
                $sanctionCheckInnerContainer = $this->innerContainer->check_access($sanctionToCheck, $user);
                $sanctionCheckOuterContainer = $this->postboxObject->check_access($sanctionToCheck, $user);

                //mark the current user with the view rights
                if($sanctionCheckInnerContainer && $sanctionCheckOuterContainer){
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

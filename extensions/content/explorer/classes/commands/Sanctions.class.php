<?php

namespace Explorer\Commands;

class Sanctions extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $object;
    private $steam;
    private $steamUser;
    private $creator;
    private $environment;

    private $content;
    private $ajaxResponseObject;

    private $userPicUrl;
    private $groupPicUrl;
    private $favPicUrl;

    private $favorites = array();
    private $favoritesAcq = array();

    private $user = array();
    private $userMapping = array();

    private $userAcq = array();
    private $userMappingAcq = array();

    private $rootGroups = array();

    private $groups = array();
    private $groupsRights = array();
    private $groupMapping = array();
    private $groupMappingA = array();
    private $groupMappingName = array();

    private $groupsAcq = array();
    private $groupsRightsAcq = array();
    private $groupMappingAcq = array();
    private $groupMappingAAcq = array();
    private $groupMappingNameAcq = array();

    private $everyone;
    private $everyoneId;

    private $steamgroup;
    private $steamgroupId;
    private $dropdownValueSteamGroup;
    private $dropdownValueAcqSteamGroup;


    /**
     * @var type array with the current sanctions
     */
    private $sanction;

    /**
     * @var type array with the environment's sancrions
     */
    private $environmentSanction;

    /**
     * @var type different Objects need different write sanctions.
     */
    private $sanctionWriteForCurrentObject;


    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        } else if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }

    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

        $this->ajaxResponseObject = $ajaxResponseObject;
        $this->steam = $GLOBALS["STEAM"];
        $this->object = \steam_factory::get_object($this->steam->get_id(), $this->id);
        $this->ajaxResponseObject->setStatus("ok");

        //check if the user can modify the sanctions
        if (!$this->object->check_access(SANCTION_SANCTION)) {
            $labelDenied = new \Widgets\RawHtml();
            $labelDenied->setHtml("Sie haben keine Berechtigung die Rechte einzusehen oder zu verändern!");
            $dialogDenied = new \Widgets\Dialog();
            $dialogDenied->setTitle("Rechte von »" . getCleanName($this->object) . "«");
            $dialogDenied->setCancelButtonLabel("");
            $dialogDenied->setSaveAndCloseButtonLabel("Schließen");
            $dialogDenied->addWidget($labelDenied);

            $this->ajaxResponseObject->addWidget($dialogDenied);
            return $this->ajaxResponseObject;
        }

        self::setupVariables();

        foreach ($this->steamUser->get_buddies() as $favorite) {
            $this->favorites[$favorite->get_id()] = $favorite;
            $this->favoritesAcq[$favorite->get_id()] = $favorite;
        }

        foreach ($this->steamUser->get_groups() as $group) {
            $this->groups[$group->get_id()] = $group;
            $this->groupsAcq[$group->get_id()] = $group;
        }

        foreach ($this->sanction as $id => $sanct) {
            if (!array_key_exists($id, $this->groups) &&
                    !array_key_exists($id, $this->favorites) &&
                    $id != $this->creator->get_id() && $id != 0 &&
                    $id != $this->everyoneId) {
                $additionalObject = \steam_factory::get_object($this->steam->get_id(), $id);
                if ($additionalObject instanceof \steam_group) {
                    $this->groups[$id] = $additionalObject;
                } else if ($additionalObject instanceof \steam_user) {
                    $this->user[$id] = $additionalObject;
                } else {
                    throw new \Exception("Ungültiger Objekttyp hat Rechte an dem aktuellen Objekt.");
                }
            }
        }

        if ($this->environment instanceof \steam_room) {
            $this->environmentSanction = $this->environment->get_sanction();
        }

        if (isset($this->environmentSanction) && is_array($this->environmentSanction)) {
            foreach ($this->environmentSanction as $id => $envSanct) {
                if (!array_key_exists($id, $this->groups) &&
                        !array_key_exists($id, $this->favorites) &&
                        $id != $this->creator->get_id() && $id != 0 &&
                        $id != $this->everyoneId) {
                    $additionalObject = \steam_factory::get_object($this->steam->get_id(), $id);
                    if ($additionalObject instanceof \steam_group) {
                        $this->groupsAcq[$id] = $additionalObject;
                    } else if ($additionalObject instanceof \steam_user) {
                        $this->userAcq[$id] = $additionalObject;
                    } else {
                        throw new \Exception("Ungültiger Objekttyp hat Rechte an dem aktuellen Objekt.");
                    }
                }
            }
        }

        foreach ($this->favorites as $id => $favorite) {
            if ($favorite instanceof \steam_user) {
                $this->user[$id] = $favorite;
                $this->userAcq[$id] = $favorite;
            } else if ($favorite instanceof \steam_group) {
                $this->groups[$id] = $favorite;
                $this->groupsAcq[$id] = $favorite;
            } else {
                throw new \Exception("Favoriten beeinhalten das Objekt einer ungültigen Klasse!");
            }
        }

        self::defineObjectSpecificWriteSanctions();

        foreach ($this->groups as $g) {
            $id = $g->get_id();
            $name = $g->get_groupname();
            $this->groupMappingA[$id] = $name;
            $this->groupMappingName[$name] = $id;
        }

        foreach ($this->groupsAcq as $g) {
            $id = $g->get_id();
            $name = $g->get_groupname();
            $this->groupMappingAAcq[$id] = $name;
            $this->groupMappingNameAcq[$name] = $id;
        }

        foreach ($this->groupMappingA as $name) {
            $array = explode(".", $name);
            $length = count($array);
            if ($length > 1) {

                $string = "";
                for ($i = 0; $i < $length; $i++) {
                    if ($i == 0) {
                        $string .= $array[$i];
                        if (!isset($this->groupMappingName[$string])) {
                            $group = \steam_factory::get_group($this->steam->get_id(), $string);
                            $groupId = $group->get_id();
                            $this->groupMappingName[$string] = $groupId;
                            $this->groupMappingA[$groupId] = $string;
                            $this->groups[$groupId] = $group;
                        }
                    } else {
                        $string .= "." . $array[$i];
                        if (!isset($this->groupMappingName[$string])) {
                            $group = \steam_factory::get_group($this->steam->get_id(), $string);
                            $groupId = $group->get_id();
                            $this->groupMappingName[$string] = $groupId;
                            $this->groupMappingA[$groupId] = $string;
                            $this->groups[$groupId] = $group;
                        }
                    }
                }
            }
        }

        foreach ($this->groupMappingAAcq as $name) {
            $array = explode(".", $name);
            $length = count($array);
            if ($length > 1) {

                $string = "";
                for ($i = 0; $i < $length; $i++) {
                    if ($i == 0) {
                        $string .= $array[$i];
                        if (!isset($this->groupMappingNameAcq[$string])) {
                            $group = \steam_factory::get_group($this->steam->get_id(), $string);
                            $groupId = $group->get_id();
                            $this->groupMappingNameAcq[$string] = $groupId;
                            $this->groupMappingAAcq[$groupId] = $string;
                            $this->groupsAcq[$groupId] = $group;
                        }
                    } else {
                        $string .= "." . $array[$i];
                        if (!isset($this->groupMappingNameAcq[$string])) {
                            $group = \steam_factory::get_group($this->steam->get_id(), $string);
                            $groupId = $group->get_id();
                            $this->groupMappingNameAcq[$string] = $groupId;
                            $this->groupMappingAAcq[$groupId] = $string;
                            $this->groupsAcq[$groupId] = $group;
                        }
                    }
                }
            }
        }

        asort($this->groupMappingA);
        asort($this->groupMappingAAcq);

        foreach ($this->groupMappingA as $id => $name) {
            $this->groupMapping[$id] = $this->groups[$id];
        }

        foreach ($this->groupMappingAAcq as $id => $name) {
            $this->groupMappingAcq[$id] = $this->groupsAcq[$id];
        }

        foreach ($this->user as $id => $u) {
            if ($u instanceof \steam_user) {
                $this->userMapping[$id] = $u->get_full_name();
            }
        }

        asort($this->userMapping);

        foreach ($this->userAcq as $id => $u) {
            if ($u instanceof \steam_user) {
                $this->userMappingAcq [$id] = $u->get_full_name();
            }
        }

        asort($this->userMappingAcq);

        self::setUpTemplate();
        self::buildGlobalGroups();
        self::buildGroupFavorites();
        self::buildGroupFavoritesAcq();
        self::buildUserFavorites();
        self::buildUserFavoritesAcq();
        self::buildDialog();

        $this->ajaxResponseObject->addWidget($this->dialog);

        if($this->object->get_attribute("OBJ_TYPE") === "postbox"){
            $deactiveAcq = new \Widgets\JSWrapper();
            $deactiveAcq->setPostJsCode('$("#radio_acquire").attr("disabled",true);');
            $this->ajaxResponseObject->addWidget($deactiveAcq);
        }

        return $this->ajaxResponseObject;
    }


    function getOptionsValues() {
        return array(0 => "Kein Zugriff", 1 => "Nur Lesen", 2 => "Lesen und Schreiben", 3 => "Lesen, Schreiben und Berechtigen");
    }


    function setupVariables(){
        $this->steamUser = \lms_steam::get_current_user();

        $this->everyone = \steam_factory::groupname_to_object($this->steam->get_id(), "everyone");
        $this->everyoneId = $this->everyone->get_id();

        $this->steamgroup = \steam_factory::groupname_to_object($this->steam->get_id(), "sTeam");
        $this->steamgroupId = $this->steamgroup->get_id();

        $this->sanction = $this->object->get_sanction();

        $this->dialog = new \Widgets\Dialog();
        $this->dialog->setAutoSaveDialog(false);
        $this->dialog->setWidth(600);
        $this->dialog->setTitle("Rechte von »" . getCleanName($this->object) . "«");

        $this->userPicUrl = PATH_URL . "explorer/asset/icons/user.png";
        $this->groupPicUrl = PATH_URL . "explorer/asset/icons/group.png";
        $this->favPicUrl = PATH_URL . "explorer/asset/icons/red.png";

        $this->creator = $this->object->get_creator();

        $this->environment = $this->object->get_environment();

    }


    function setUpTemplate(){

        $this->content = \Explorer::getInstance()->loadTemplate("sanction.template.html");
        $css = \Explorer::getInstance()->readCSS("sanctions.css");
        $this->content->setVariable("CSS", $css);

        //start with the composition of the dialog
        if ($this->environment instanceof \steam_room) {
            $this->content->setVariable("INHERIT_HEADLINE", "<h3>Option 1: Rechte erben</h3>");
            $this->content->setVariable("PREDEFINED_HEADLINE", "<h3>Option 2: Vordefinierte Rechte verwenden</h3>");
            $this->content->setVariable("INDIVIDUAL_HEADLINE", "<h3>Option 3: Individuelle Rechte festlegen</h3>");
            $this->content->setVariable("INHERIT_FROM", "Rechte vom Elternelement<b>" . getCleanName($this->environment) . "</b> übernehmen:");
        } else{
            $this->content->setVariable("NO_ENVIRONMENT", "style='display:none;'");
            $this->content->setVariable("INHERIT_FROM", "");
            $this->content->setVariable("PREDEFINED_HEADLINE", "<h3>Option 1: Vordefinierte Rechte verwenden</h3>");
            $this->content->setVariable("INDIVIDUAL_HEADLINE", "<h3>Option 2: Individuelle Rechte festlegen</h3>");
        }

        if ($this->object->get_acquire() instanceof \steam_room) {
            $this->content->setVariable("ACQUIRE_START", "acq");
            $this->content->setVariable("DISPLAY_NORMAL_CLASS", "invisible");
            $this->content->setVariable("DISPLAY_ACQ_CLASS", "visible");

            $this->content->setVariable("CHECKBOX_CHECKED", "checked=\"checked\"");

        } else {
            $this->content->setVariable("ACQUIRE_START", "");
            $this->content->setVariable("DISPLAY_NORMAL_CLASS", "visible");
            $this->content->setVariable("DISPLAY_ACQ_CLASS", "invisible");
            $this->content->setVariable("CHECKBOX_CHECKED", "");
        }

        $this->content->setVariable("PRIVATE_PIC", PATH_URL . "explorer/asset/icons/private.png");
        $this->content->setVariable("USER_DEF_PIC", PATH_URL . "explorer/asset/icons/user_defined.png");
        $this->content->setVariable("USER_GLOBAL_PIC", PATH_URL . "explorer/asset/icons/server_public.png");
        $this->content->setVariable("SERVER_GLOBAL_PIC", PATH_URL . "explorer/asset/icons/world_public.png");

        if($this->creator instanceof \steam_user){
            $this->content->setVariable("CREATOR_FULL_NAME", $this->creator->get_full_name());
        } else {
            $this->content->setVariable("CREATOR_FULL_NAME", getCleanName($this->creator));
        }

        $this->content->setVariable("EVERYONE_ID", $this->everyoneId);
        $this->content->setVariable("STEAMID", $this->steamgroupId);
        $this->content->setVariable("SEND_REQUEST_ACQ", 'sendRequest("UpdateSanctions", { "id": ' . $this->id . ', "type": "acquire", "value": acquire_checkbox }, "", "data", function(response){dataSaveFunctionCallback(response);}, null, "explorer");');
    }


    function defineObjectSpecificWriteSanctions(){
        //if the document is a questionary
        if (strcmp($this->object->get_attribute("bid:doctype"), "questionary") == 0) {
            $this->sanctionWriteForCurrentObject = SANCTION_INSERT;
        }
        // In message boards only annotating is allowed. The owner
        // is the only one who can also write and change message
        // board entries.
        else if ($this->object instanceof \steam_messageboard) {
            $this->sanctionWriteForCurrentObject = SANCTION_ANNOTATE;
        }
        // normal documents
        else {
            $this->sanctionWriteForCurrentObject = SANCTION_WRITE | SANCTION_EXECUTE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE;
        }
    }

    function buildGlobalGroups(){
        $dropDownValue = 0;
        if ($this->object->check_access(SANCTION_SANCTION, $this->everyone)) {$dropDownValue = 3;}
        else if ($this->object->check_access($this->sanctionWriteForCurrentObject, $this->everyone)) {$dropDownValue = 2;}
        else if ($this->object->check_access_read($this->everyone)) {$dropDownValue = 1;}

        $allUserIds = array();
        foreach($this->user as $user){
            $allUserIds[] = "#fav_".$user->get_id()."_dd";
        }
        $allUserIds = implode(",",$allUserIds);

        $allGroupIds = array();

        //select distinct all groups that are on the highest level to be statet as "subgroups" of sTeam for the recursion
        foreach ($this->groups as $group){
            if($group !== $this->steamgroup && $group != $this->everyone){
                $this->rootGroups[reset(explode(".", $group->get_groupname()))] = $group;
            }
        }

        foreach($this->rootGroups as $group){
            $allGroupIds[] = "#group_".$group->get_id();
        }

        $allGroupIds = implode(",",$allGroupIds);

        $ddlEveryone = new \Widgets\DropDownListSanction();
        $ddlEveryone->setId("everyone_dd");
        $ddlEveryone->setName("ddlist");
        $ddlEveryone->setSize("1");
        $ddlEveryone->setType("group");
        $ddlEveryone->setReadOnly(false);
        $ddlEveryone->setSaveFunction("sendRequest('UpdateSanctions', { 'id': $this->id, 'sanctionId': $this->everyoneId, 'type': 'sanction', 'value': everyone_dd }, '', 'data', function(response){dataSaveFunctionCallback(response);}, null, 'explorer');");
        $ddlEveryone->setCustomClass("non-acq");
        $ddlEveryone->setMembers($allUserIds);
        $ddlEveryone->setSubGroups("#steam_dd");
        $ddlEveryone->setSteamId($this->everyoneId);
        $ddlEveryone->addDataEntries(self::getOptionsValues());
        $ddlEveryone->setStartValue($dropDownValue);

        $this->content->setCurrentBlock("GROUP_EVERYONE");
        $this->content->setVariable("DROPDOWNLIST", $ddlEveryone->getHtml());
        $this->content->parse("GROUP_EVERYONE");

        $dropdownValueAcq = 0;
        if ($this->environment instanceof \steam_room) {
            if ($this->environment->check_access(SANCTION_SANCTION, $this->everyone)) { $dropdownValueAcq = 3; }
            else if ($this->environment->check_access($this->sanctionWriteForCurrentObject, $this->everyone)) { $dropdownValueAcq = 2; }
            else if ($this->environment->check_access_read($this->everyone)) { $dropdownValueAcq = 1; }
        }

        $ddlEveryoneAcq = new \Widgets\DropDownListSanction();
        $ddlEveryoneAcq->setId("everyone_acq");
        $ddlEveryoneAcq->setName("ddlist");
        $ddlEveryoneAcq->setSize("1");
        $ddlEveryoneAcq->setType("group");
        $ddlEveryoneAcq->setReadOnly(true);
        $ddlEveryoneAcq->addDataEntries(self::getOptionsValues());
        $ddlEveryoneAcq->setStartValue($dropdownValueAcq);

        $this->content->setCurrentBlock("GROUP_EVERYONE_ACQ");
        $this->content->setVariable("DROPDOWNLIST", $ddlEveryoneAcq->getHtml());
        $this->content->parse("GROUP_EVERYONE_ACQ");

        $this->dropdownValueSteamGroup = 0;
        if ($this->object->check_access(SANCTION_SANCTION, $this->steamgroup)) {$this->dropdownValueSteamGroup = 3;}
        else if ($this->object->check_access($this->sanctionWriteForCurrentObject, $this->steamgroup)) {$this->dropdownValueSteamGroup = 2;}
        else if ($this->object->check_access_read($this->steamgroup)) {$this->dropdownValueSteamGroup = 1;}
        $ddlSteam = new \Widgets\DropDownListSanction();
        $steamId = "steam_dd";
        $ddlSteam->setId($steamId);
        $ddlSteam->setName("ddlist");
        $ddlSteam->setSize("1");
        $ddlSteam->setType("group");
        $ddlSteam->setReadOnly(false);
        $ddlSteam->setSaveFunction("sendRequest('UpdateSanctions', { 'id': $this->id, 'sanctionId': $this->steamgroupId, 'type': 'sanction', 'value': $steamId }, '', 'data', function(response){dataSaveFunctionCallback(response);}, null, 'explorer');");
        $ddlSteam->setCustomClass("non-acq");
        $ddlSteam->setMembers($allUserIds);
        $ddlSteam->setSubGroups($allGroupIds);
        $ddlSteam->setSteamId($this->steamgroupId);
        $ddlSteam->addDataEntries(self::getOptionsValues());
        $ddlSteam->setStartValue($this->dropdownValueSteamGroup);

        $this->content->setCurrentBlock("GROUP_STEAM");
        $this->content->setVariable("DROPDOWNLIST", $ddlSteam->getHtml());
        $this->content->parse("GROUP_STEAM");

        $this->dropdownValueAcqSteamGroup = 0;
        if ($this->environment instanceof \steam_room) {
            if ($this->environment->check_access(SANCTION_SANCTION, $this->steamgroup)) {$this->dropdownValueAcqSteamGroup = 3;}
            else if ($this->environment->check_access($this->sanctionWriteForCurrentObject, $this->steamgroup)) {$this->dropdownValueAcqSteamGroup = 2;}
            else if ($this->environment->check_access_read($this->steamgroup)) {$this->dropdownValueAcqSteamGroup = 1;}
        }

        $ddlSteamAcq = new \Widgets\DropDownListSanction();
        $ddlSteamAcq->setId("steam_dd_acq");
        $ddlSteamAcq->setName("ddlist");
        $ddlSteamAcq->setSize("1");
        $ddlSteamAcq->setType("group");
        $ddlSteamAcq->setReadOnly(true);
        $ddlSteamAcq->addDataEntries(self::getOptionsValues());
        $ddlSteamAcq->setStartValue($this->dropdownValueAcqSteamGroup);

        $this->content->setCurrentBlock("GROUP_STEAM_ACQ");
        $this->content->setVariable("DROPDOWNLIST", $ddlSteamAcq->getHtml());
        $this->content->parse("GROUP_STEAM_ACQ");

    }


    function buildGroupFavorites(){
        if (count($this->groupMapping) == 0) {
            $this->content->setVariable("NO_GROUP_MEMBER", "Sie sind kein Mitglied einer Gruppe");
        } else {
            if(count($this->groupMapping) > 4){
              $this->content->setVariable("CSS_GROUPS", "height:104px;");
            } else {
              $this->content->setVariable("CSS_GROUPS", "");
            }
            foreach ($this->groupMapping as $id => $group) {

                if($group->get_attribute("GROUP_INVISIBLE") != 0){
                    continue;
                }
                $groupname = $group->get_groupname();
                if ($groupname != "Everyone" && $groupname != "sTeam") {

                    $name = $group->get_attribute("OBJ_DESC");
                    $realname = $group->get_name();
                    if ($name == "" || $name == "0") {
                      $name = $group->get_name();
                    }

                    $dropDownValue = 0;
                    if ($this->object->check_access(SANCTION_SANCTION, $group)) {$dropDownValue = 3;}
                    elseif ($this->object->check_access($this->sanctionWriteForCurrentObject, $group)) {$dropDownValue = 2;}
                    elseif ($this->object->check_access_read($group)) {$dropDownValue = 1;}

                    $this->groupsRights[$id] = $dropDownValue;

                    $ddl = new \Widgets\DropDownListSanction();
                    $ddl->setId("group_" . $id);
                    $ddl->setName("ddlist");
                    $ddl->setType("group");
                    $ddl->setSize("1");
                    $ddl->setReadOnly(false);
                    $ddl->setSaveFunction("sendRequest('UpdateSanctions', { 'id': $this->id, 'sanctionId': $id, 'type': 'sanction', 'value':group_$id }, '', 'data', function(response){dataSaveFunctionCallback(response);}, null, 'explorer');");
                    $ddl->setCustomClass("non-acq");
                    $ddl->setSteamId($id);
                    $members = array();

                    foreach($this->user as $user){
                        if($group->is_member($user)){
                            $members[] = '#fav_'.$user->get_id()."_dd";
                        }
                    }

                    $ddl->setMembers(implode(',',$members));

                    $subGroups = array();
                    foreach($group->get_subgroups() as $subGroup){
                        if(array_key_exists($subGroup->get_id(), $this->groupMapping)){
                            $subGroups[] = "#group_".$subGroup->get_id();
                        }
                    }

                    $ddl->setSubGroups(implode(',', $subGroups));

                    $indent = count(explode(".", $groupname));
                    if ($indent == 1) {
                        $optionValues = self::getOptionsValues();

                    } else {
                        $parent = $group->get_parent_group();

                        if ($this->object->check_access(SANCTION_SANCTION, $parent)) {$dropDownValueParent = 3;}
                        else if ($this->object->check_access($this->sanctionWriteForCurrentObject, $parent)) {$dropDownValueParent = 2;}
                        else if ($this->object->check_access_read($parent)) {$dropDownValueParent = 1;}
                        else {$dropDownValueParent = 0;}

                        $optionValues = self::getOptionsValues();
                    }
                    $ddl->addDataEntries($optionValues);
                    $ddl->setStartValue($dropDownValue);

                    $this->content->setCurrentBlock("GROUPS");
                    $this->content->setCurrentBlock("GROUP_DDSETTINGS");
                    $this->content->setVariable("GROUPID", $id);
                    $this->content->setVariable("GROUP_ID", $id);
                    $this->content->setVariable("GROUPNAME", $name . " (". $realname . ")" );
                    $this->content->setVariable("INDENTINDEX", $indent);
                    $this->content->setVariable("DROPDOWNLIST", $ddl->getHtml());
                    if (isset($this->favorites[$id])) {
                        $this->content->setVariable("IMG_PATH", $this->favPicUrl);
                    } else {
                        $this->content->setVariable("IMG_PATH", $this->groupPicUrl);
                    }
                    $this->content->parse("GROUP_DDSETTINGS");
                    $this->content->parse("GROUPS");
                }
            }
        }
    }


    function buildGroupFavoritesAcq(){
        if (count($this->groupMappingAcq) == 0) {
            $this->content->setVariable("NO_GROUP_MEMBER_ACQ", "Sie sind kein Mitglied einer Gruppe");
        } else {
            foreach ($this->groupMappingAcq as $id => $group) {

                if($group->get_attribute("GROUP_INVISIBLE") !== 0){
                   continue;
                }
                $name = $group->get_attribute("OBJ_DESC");
                $realname = $group->get_name();
                if ($name == "" || $name == "0") {
                    $name = $realname;
                }
                $groupname = $group->get_groupname();

                $dropDownValueAcq = 0;
                if ($this->environment instanceof \steam_room) {
                    if ($this->environment->check_access(SANCTION_SANCTION, $group)) {$dropDownValueAcq = 3;}
                    else if ($this->environment->check_access($this->sanctionWriteForCurrentObject, $group)) {$dropDownValueAcq = 2;}
                    else if ($this->environment->check_access_read($group)) {$dropDownValueAcq = 1;}
                }

                $this->groupsRightsAcq[$id] = $dropDownValueAcq;

                $ddlAcq = new \Widgets\DropDownListSanction();
                $ddlAcq->setId("group_" . $id."_acq");
                $ddlAcq->setName("ddlist_acq");
                $ddlAcq->setType("group");
                $ddlAcq->setSize("1");
                $ddlAcq->setReadOnly(true);
                $ddlAcq->setStartValue($dropDownValueAcq);
                $ddlAcq->addDataEntries(self::getOptionsValues());

                $indent = count(explode(".", $groupname));

                if ($groupname != "Everyone" && $groupname != "sTeam") {
                    $this->content->setCurrentBlock("GROUPS_ACQ");
                    $this->content->setCurrentBlock("GROUP_DDSETTINGS_ACQ");
                    $this->content->setVariable("GROUPID_ACQ", $id);
                    $this->content->setVariable("GROUP_ID_ACQ", $id);
                    $this->content->setVariable("GROUPNAME_ACQ", $name . " (". $realname . ")" );
                    $this->content->setVariable("OPTIONVALUE_ACQ", max($dropDownValueAcq, $this->dropdownValueAcqSteamGroup));
                    $this->content->setVariable("INDENTINDEX_ACQ", $indent);
                    $this->content->setVariable("DROPDOWNLIST_ACQ", $ddlAcq->getHtml());
                    if (isset($this->favorites[$id])) {
                        $this->content->setVariable("IMG_PATH_ACQ", $this->favPicUrl);
                    } else {
                        $this->content->setVariable("IMG_PATH_ACQ", $this->groupPicUrl);
                    }
                    $this->content->parse("GROUP_DDSETTINGS_ACQ");
                    $this->content->parse("GROUPS_ACQ");
                }
            }
        }
    }


    function buildUserFavorites(){
        if (count($this->userMapping) == 0) {
            $this->content->setVariable("NO_FAV_MEMBER", "Es können keinem Benutzer Rechte zugewiesen werden. ");
        } else {
            $this->content->setVariable("DUMMY_FAV", "");
            $this->content->setVariable("DUMMY_FAV_ACQ", "");

            if (count($this->userMapping) > 4) {
                $this->content->setVariable("CSS_USER", "height:104px;");
            } else {
                $this->content->setVariable("CSS_USER", "");
            }

            foreach ($this->userMapping as $id => $name) {

                $user = \steam_factory::get_object($this->steam->get_id(), $id);
                if ($user instanceof \steam_user) {

                    $dropDownValue = 0;
                    if ($this->object->check_access(SANCTION_SANCTION, $user)) {$dropDownValue = 3;}
                    elseif ($this->object->check_access($this->sanctionWriteForCurrentObject, $user)) {$dropDownValue = 2;}
                    elseif ($this->object->check_access_read($user)) {$dropDownValue = 1;}

                    //check if the user is in one group with more rights
                    $maxSanct = 0;
                    $maxSanctFromGroupMembership = 0;
                    foreach ($user->get_groups() as $group) {
                        if (isset($this->groupMapping[$group->get_id()])) {
                            $currentValue = $this->groupsRights[$group->get_id()];
                            if ($currentValue > $maxSanct) {
                                $maxSanctFromGroupMembership = $currentValue;
                            }
                        }
                    }

                    //display the hights rights
                    $selectedValue = max($maxSanctFromGroupMembership, $dropDownValue);

                    $ddl = new \Widgets\DropDownListSanction();
                    $ddlId = "fav_" . $id . "_dd";
                    $ddl->setId($ddlId);
                    $ddl->setName("ddlist");
                    $ddl->setType("user");
                    $ddl->setSize("1");
                    $ddl->setReadOnly(false);
                    $ddl->setStartValue($selectedValue);

                    $ddl->addDataEntries(self::getOptionsValues());
                    $ddl->setSaveFunction("sendRequest('UpdateSanctions', { 'id': $this->id, 'sanctionId': $id, 'type': 'sanction', 'value': $ddlId }, '', 'data', function(response){dataSaveFunctionCallback(response);}, null, 'explorer');");
                    $ddl->setCustomClass("non-acq");
                    $ddl->setSteamId($id);

                    //get all groups, this user is a member of
                    $memberOf = array();
                    foreach($this->groupMapping as $group){
                        if($group->is_member($user)){
                            $memberOf[] = "#group_".$group->get_id();
                        }
                    }
                    $ddl->setMembers(implode(',',$memberOf).",#everyone_dd,#steam_dd");

                    if(self::isAdmin($user)){
                        $ddl->setType("admin");
                        $ddl->setMembers("");
                        $ddl->setStartValue(3);
                        $ddl->setSaveFunction("");
                        $ddl->setReadOnly(true);
                    }

                    $this->content->setCurrentBlock("FAVORITES");
                    $this->content->setCurrentBlock("FAV_DDSETINGS");
                    $this->content->setVariable("FAVNAME", $name);
                    $this->content->setVariable("DROPDOWNLIST_FAVORITES", $ddl->getHtml());
                    if (isset($this->favorites[$id])) {
                        $this->content->setVariable("IMG_PATH", $this->favPicUrl);
                    } else {
                        $this->content->setVariable("IMG_PATH", $this->userPicUrl);
                    }
                    $this->content->parse("FAV_DDSETTINGS");
                    $this->content->parse("FAVORITES");
                }
            }
        }
    }


    function buildUserFavoritesAcq(){
        if (count($this->userMappingAcq) == 0) {
            $this->content->setVariable("NO_FAV_MEMBER_ACQ", "Es können keinem Benutzer Rechte zugewiesen werden.");
        } else {
            foreach ($this->userMappingAcq as $id => $name) {
                $user = \steam_factory::get_object($this->steam->get_id(), $id);

                if ($user instanceof \steam_user) {

                    $dropDownValueAcq = 0;
                    if ($this->environment instanceof \steam_room) {
                       if ($this->environment->check_access(SANCTION_SANCTION, $user)) {$dropDownValueAcq = 3;}
                       elseif ($this->environment->check_access($this->sanctionWriteForCurrentObject, $user)) {$dropDownValueAcq = 2;}
                       elseif ($this->environment->check_access_read($user)) {$dropDownValueAcq = 1;}
                    }

                    $maxSanct = 0;
                    $maxSanctFromGroupMembership = 0;
                    foreach ($user->get_groups() as $group) {
                        if (isset($this->groupMapping[$group->get_id()])) {
                            $currentValue = $this->groupsRightsAcq[$group->get_id()];
                            if ($currentValue > $maxSanct) {
                                $maxSanctFromGroupMembership = $currentValue;
                            }
                        }
                    }

                    //display the hights rights
                    if ($dropDownValueAcq > $maxSanctFromGroupMembership) {
                        $selectedValue = $dropDownValueAcq;
                    } else {
                        $selectedValue = $maxSanctFromGroupMembership;
                    }

                    $ddl = new \Widgets\DropDownListSanction();
                    $ddl->setId("fav_" . $id . "_dd_acq");
                    $ddl->setName("ddlist");
                    $ddl->setSize("1");
                    $ddl->setType("user");
                    $ddl->setReadOnly(true);
                    $ddl->setStartValue($selectedValue);
                    $ddl->addDataEntries(self::getOptionsValues());

                    $this->content->setCurrentBlock("FAVORITES_ACQ");
                    $this->content->setCurrentBlock("FAV_DDSETINGS_ACQ");
                    $this->content->setVariable("FAVNAME_ACQ", $name);
                    $this->content->setVariable("DROPDOWNLIST_USER_ACQ", $ddl->getHtml());
                    if (isset($this->favorites[$id])) {
                        $this->content->setVariable("IMG_PATH_ACQ", $this->favPicUrl);
                    } else {
                        $this->content->setVariable("IMG_PATH_ACQ", $this->userPicUrl);
                    }
                    $this->content->parse("FAV_DDSETTING_ACQS");
                    $this->content->parse("FAVORITES_ACQ");
                }
            }
        }
    }


    function buildDialog(){
        $sanctionURL = "http://$_SERVER[HTTP_HOST]" . "/Sanction/Index/" . $this->id . "/";

        $isSchoolAdmin = false;

        $schoolAdminGroup = \steam_factory::groupname_to_object($GLOBALS[ "STEAM" ]->get_id(), "SchulAdmins");

        if($schoolAdminGroup instanceof \steam_group){
            $isSchoolAdmin = $schoolAdminGroup->is_member($this->steamUser);
        }

        if(self::isAdmin($this->steamUser) || $isSchoolAdmin){
          $this->dialog->setCustomButtons(array(array("js" => "window.open('$sanctionURL', '_self')", "label" => "Erweiterte Ansicht öffnen")));
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($this->content->get());
        $this->dialog->addWidget($rawHtml);

    }


    function isAdmin(\steam_user $candidate){
        if(\lms_steam::is_steam_admin($candidate)) {
            return true;
        }
        else {
            return false;
        }
    }
}

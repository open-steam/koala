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


        $this->steam = $GLOBALS["STEAM"];
        $this->object = \steam_factory::get_object($this->steam->get_id(), $this->id);
        //$objId = $this->id;
        $ajaxResponseObject->setStatus("ok");

        //check if the user can modify the sanctions
        if (!$this->object->check_access(SANCTION_SANCTION)) {
            $labelDenied = new \Widgets\RawHtml();
            $labelDenied->setHtml("Sie haben keine Berechtigung die Rechte einzusehen und zu verändern!");
            $dialogDenied = new \Widgets\Dialog();
            $dialogDenied->setTitle("Rechte von »" . getCleanName($this->object) . "«");
            $dialogDenied->addWidget($labelDenied);

            $ajaxResponseObject->addWidget($dialogDenied);
            return $ajaxResponseObject;
        }

        $this->steamUser = \lms_steam::get_current_user();

        $dialog = new \Widgets\Dialog();
        $dialog->setAutoSaveDialog(false);
        $dialog->setWidth(600);
        $dialog->setTitle("Rechte von »" . getCleanName($this->object) . "«");


        //GET CREATOR TODO: USEFULL FOR ROOT FOLDER
        //SET ICON URL
        $userPicUrl = PATH_URL . "explorer/asset/icons/user.png";
        $groupPicUrl = PATH_URL . "explorer/asset/icons/group.png";
        $favPicUrl = PATH_URL . "explorer/asset/icons/red.png";

        //GET OWNER OF THE CURRENT OBJECT

        $this->creator = $this->object->get_creator();



        //GET FAVORITES

        $favorites = array();
        $favoritesAcq = array();

        foreach ($this->steamUser->get_buddies() as $favorite) {
            $favorites[$favorite->get_id()] = $favorite;
            $favoritesAcq[$favorite->get_id()] = $favorite;
        }

        //GET GROUPS
        $groups = array();
        $groupsAcq = array();
        foreach ($this->steamUser->get_groups() as $group) {
            $groups[$group->get_id()] = $group;
            $groupsAcq[$group->get_id()] = $group;
        }
       //GET GROUPS EVERYONE
        $everyone = \steam_factory::groupname_to_object($this->steam->get_id(), "everyone");
        $everyoneId = $everyone->get_id();
        //GET GROUP STEAM
        $steamgroup = \steam_factory::groupname_to_object($this->steam->get_id(), "sTeam");
        $steamgroupId = $steamgroup->get_id();

        //GET SANCTION
        $sanction = $this->object->get_sanction();

        $this->environment = $this->object->get_environment();


        if ($this->environment instanceof \steam_room) {
            $environmentSanction = $this->environment->get_sanction();
        }
        $additionalUser = array();
        $additionalGroups = array();
        foreach ($sanction as $id => $sanct) {
            if (!array_key_exists($id, $groups) &&
                    !array_key_exists($id, $favorites) &&
                    $id != $this->creator->get_id() && $id != 0 &&
                    $id != $everyoneId) {
                $additionalObject = \steam_factory::get_object($this->steam->get_id(), $id);
                if ($additionalObject instanceof \steam_group) {
                    $additionalGroups[$id] = $additionalObject;
                } else if ($additionalObject instanceof \steam_user) {
                    $additionalUser[$id] = $additionalObject;
                } else {
                    throw new \Exception("Ungültiger Objekttyp hat Rechte an dem aktuellen Objekt.");
                }
            }
        }

        $additionalUserAcq = array();
        $additionalGroupsAcq = array();
        if (isset($environmentSanction) && is_array($environmentSanction)) {
            foreach ($environmentSanction as $id => $envSanct) {
                if (!array_key_exists($id, $groups) &&
                        !array_key_exists($id, $favorites) &&
                        $id != $this->creator->get_id() && $id != 0 &&
                        $id != $everyoneId) {
                    $additionalObject = \steam_factory::get_object($this->steam->get_id(), $id);
                    if ($additionalObject instanceof \steam_group) {
                        $additionalGroupsAcq[$id] = $additionalObject;
                    } else if ($additionalObject instanceof \steam_user) {
                        $additionalUserAcq[$id] = $additionalObject;
                    } else {
                        throw new \Exception("Ungültiger Objekttyp hat Rechte an dem aktuellen Objekt.");
                    }
                }
            }
        }

        foreach ($additionalGroups as $id => $group) {
            $groups[$id] = $group;
        }
        foreach ($additionalGroupsAcq as $id => $group) {
            $groupsAcq[$id] = $group;
        }
        $user = array();
        foreach ($additionalUser as $id => $usr) {
            $user[$id] = $usr;
        }
        $userAcq = array();
        foreach ($additionalUserAcq as $id => $usr) {
            $userAcq[$id] = $usr;
        }
        foreach ($favorites as $id => $favi) {
            if ($favi instanceof \steam_user) {
                $user[$id] = $favi;
                $userAcq[$id] = $favi;
            } else if ($favi instanceof \steam_group) {
                $groups[$id] = $favi;
                $groupsAcq[$id] = $favi;
            } else {
                throw new \Exception("Favoriten beeinhalten das Objekt einer ungültigen Klasse!");
            }
        }

        //if the document is a questionary
        if (strcmp($this->object->get_attribute("bid:doctype"), "questionary") == 0) {
            $SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_INSERT;
        }
        // In message boards only annotating is allowed. The owner
        // is the only one who can also write and change message
        // board entries.
        else if ($this->object instanceof \steam_messageboard) {
            $SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_ANNOTATE;
        }
        // normal documents
        else {
            $SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_WRITE | SANCTION_EXECUTE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE;
        }
        $groupMappingA = array();
        $groupMappingName = array();
        foreach ($groups as $g) {
            $id = $g->get_id();
            $name = $g->get_groupname();
            $groupMappingA[$id] = $name;
            $groupMappingName[$name] = $id;
        }
        $groupMappingAAcq = array();
        $groupMappingNameAcq = array();
        foreach ($groupsAcq as $g) {
            $id = $g->get_id();
            $name = $g->get_groupname();
            $groupMappingAAcq[$id] = $name;
            $groupMappingNameAcq[$name] = $id;
        }
        foreach ($groupMappingA as $name) {
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
                            $groups[$groupId] = $group;
                        }
                    } else {
                        $string .= "." . $array[$i];
                        if (!isset($groupMappingName[$string])) {
                            $group = \steam_factory::get_group($this->steam->get_id(), $string);
                            $groupId = $group->get_id();
                            $groupMappingName[$string] = $groupId;
                            $groupMappingA[$groupId] = $string;
                            $groups[$groupId] = $group;
                        }
                    }
                }
            }
        }
        foreach ($groupMappingAAcq as $name) {
            $array = explode(".", $name);
            $length = count($array);
            if ($length > 1) {

                $string = "";
                for ($i = 0; $i < $length; $i++) {
                    if ($i == 0) {
                        $string .= $array[$i];
                        if (!isset($groupMappingNameAcq[$string])) {
                            $group = \steam_factory::get_group($this->steam->get_id(), $string);
                            $groupId = $group->get_id();
                            $groupMappingNameAcq[$string] = $groupId;
                            $groupMappingAAcq[$groupId] = $string;
                            $groupsAcq[$groupId] = $group;
                        }
                    } else {
                        $string .= "." . $array[$i];
                        if (!isset($groupMappingNameAcq[$string])) {
                            $group = \steam_factory::get_group($this->steam->get_id(), $string);
                            $groupId = $group->get_id();
                            $groupMappingNameAcq[$string] = $groupId;
                            $groupMappingAAcq[$groupId] = $string;
                            $groupsAcq[$groupId] = $group;
                        }
                    }
                }
            }
        }
        asort($groupMappingA);
        asort($groupMappingAAcq);
        $groupMapping = array();
        foreach ($groupMappingA as $id => $name) {
            $groupMapping[$id] = $groups[$id];
        }

        $groupMappingAcq = array();
        foreach ($groupMappingAAcq as $id => $name) {
            $groupMappingAcq[$id] = $groupsAcq[$id];
        }

        //MAPPING USER
        $userMapping = array();
        foreach ($user as $id => $u) {
            if ($u instanceof \steam_user) {
                $userMapping[$id] = $u->get_full_name();
            }
        }
        asort($userMapping);

        $userMappingAcq = array();

        foreach ($userAcq as $id => $u) {
            if ($u instanceof \steam_user) {
                $userMappingAcq [$id] = $u->get_full_name();
            }
        }
        asort($userMappingAcq);


        $content = \Explorer::getInstance()->loadTemplate("sanction.template.html");
        $css = \Explorer::getInstance()->readCSS("sanctions.css");
        $content->setVariable("CSS", $css);

        //ACQUIRE
        if ($this->environment instanceof \steam_room) {
            $content->setVariable("INHERIT_FROM", "Übernehme Rechte von:<b>" . getCleanName($this->environment) . "</b>");
        } else{
            //$content->setVariable("NO_ENVIRONMENT", "disabled");
            $content->setVariable("NO_ENVIRONMENT", "style='display:none;'");
            $content->setVariable("INHERIT_FROM", "");
        }

        if ($this->object->get_acquire() instanceof \steam_room) {
            $content->setVariable("ACQUIRE_START", "activateAcq();");
        }

        //PICTURES
        $content->setVariable("PRIVATE_PIC", PATH_URL . "explorer/asset/icons/private.png");
        $content->setVariable("USER_DEF_PIC", PATH_URL . "explorer/asset/icons/user_defined.png");
        $content->setVariable("USER_GLOBAL_PIC", PATH_URL . "explorer/asset/icons/server_public.png");
        $content->setVariable("SERVER_GLOBAL_PIC", PATH_URL . "explorer/asset/icons/world_public.png");

        if($this->creator instanceof \steam_user){
            $content->setVariable("CREATOR_FULL_NAME", $this->creator->get_full_name());
        } else {
            $content->setVariable("CREATOR_FULL_NAME", getCleanName($this->creator));
        }


        $content->setVariable("EVERYONEID", $everyoneId);
        $readCheck = $this->object->check_access_read($everyone);
        $writeCheck = $this->object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $everyone);
        $sanctionCheck = $this->object->check_access(SANCTION_SANCTION, $everyone);
        $dropdownValue = 0;
        if ($sanctionCheck)
            $dropdownValue = 3;
        else if ($writeCheck)
            $dropdownValue = 2;
        else if ($readCheck)
            $dropdownValue = 1;
        $content->setVariable("EVERYONE_VALUE", $dropdownValue);

        if ($this->environment instanceof \steam_room) {
            $readCheckAcq = $this->environment->check_access_read($everyone);
            $writeCheckAcq = $this->environment->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $everyone);
            $sanctionCheckAcq = $this->environment->check_access(SANCTION_SANCTION, $everyone);
        } else {
            $readCheckAcq = 0;
            $writeCheckAcq = 0;
            $sanctionCheckAcq = 0;
        }

        $dropdownValueAcq = 0;
        if ($sanctionCheckAcq)
            $dropdownValueAcq = 3;
        else if ($writeCheckAcq)
            $dropdownValueAcq = 2;
        else if ($readCheckAcq)
            $dropdownValueAcq = 1;
        $content->setVariable("EVERYONE_VALUE_ACQ", $dropdownValueAcq);


        $content->setVariable("STEAMID", $steamgroupId);
        $readCheckSteamGroup = $this->object->check_access_read($steamgroup);
        $writeCheckSteamGroup = $this->object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $steamgroup);
        $sanctionCheckSteamGroup = $this->object->check_access(SANCTION_SANCTION, $steamgroup);
        $dropdownValueSteamGroup = 0;
        if ($sanctionCheckSteamGroup)
            $dropdownValueSteamGroup = 3;
        else if ($writeCheckSteamGroup)
            $dropdownValueSteamGroup = 2;
        else if ($readCheckSteamGroup)
            $dropdownValueSteamGroup = 1;
        $content->setVariable("STEAM_VALUE", $dropdownValueSteamGroup);

        if ($this->environment instanceof \steam_room) {
            $readCheckAcqSteamGroup = $this->environment->check_access_read($steamgroup);
            $writeCheckAcqSteamGroup = $this->environment->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $steamgroup);
            $sanctionCheckAcqSteamGroup = $this->environment->check_access(SANCTION_SANCTION, $steamgroup);
        } else {
            $readCheckAcqSteamGroup = 0;
            $writeCheckAcqSteamGroup = 0;
            $sanctionCheckAcqSteamGroup = 0;
        }

        $dropdownValueAcqSteamGroup = 0;
        if ($sanctionCheckAcqSteamGroup)
            $dropdownValueAcqSteamGroup = 3;
        else if ($writeCheckAcqSteamGroup)
            $dropdownValueAcqSteamGroup = 2;
        else if ($readCheckAcqSteamGroup)
            $dropdownValueAcqSteamGroup = 1;
        $content->setVariable("STEAM_VALUE_ACQ", $dropdownValueAcqSteamGroup);

        $content->setVariable("EVERYONE_ID", $everyoneId);
        $content->setVariable("STEAM_ID", $steamgroupId);
        $content->setVariable("SEND_REQUEST_SANCTION", 'sendRequest("UpdateSanctions", { "id": ' . $this->id . ', "sanctionId": id, "type": "sanction", "value": value }, "", "data", function(response){jQuery(\'#dynamic_wrapper\').remove(); jQuery(\'#overlay\').remove(); sendRequest(\'Sanctions\', {\'id\':\'' . $this->id . '\'}, \'\', \'popup\', null, null, \'explorer\');}, null, "explorer");');
        $content->setVariable("SEND_REQUEST_CRUDE", 'sendRequest("UpdateSanctions", { "id": ' . $this->id . ', "type": "crude", "value": value }, "", "data", function(response){jQuery(\'#dynamic_wrapper\').remove(); jQuery(\'#overlay\').remove(); sendRequest(\'Sanctions\', {\'id\':\'' . $this->id . '\'}, \'\', \'popup\', null, null, \'explorer\');}, null, "explorer");');
        $content->setVariable("SEND_REQUEST_ACQ_ACT", 'sendRequest("UpdateSanctions", { "id": ' . $this->id . ', "type": "acquire", "value": "acq" }, "", "data", null, null, "explorer");');
        $content->setVariable("SEND_REQUEST_ACQ_DEACT", 'sendRequest("UpdateSanctions", { "id": ' . $this->id . ', "type": "acquire", "value": "non_acq" }, "", "data", null, null, "explorer");');
        //TEMPLATE GROUPS

        if (count($groupMapping) == 0) {
            $content->setVariable("NO_GROUP_MEMBER", "Sie sind kein Mitglied einer Gruppe");
        } else {
            $groupsRights = array();

            foreach ($groupMapping as $id => $group) {
                $name = $group->get_attribute("OBJ_DESC");
                $realname = $group->get_name();
                if ($name == "" || $name == "0") {
                    $name = $group->get_name();
                }
                $groupVisibility = $group->get_attribute("GROUP_INVISIBLE");
                if(!($groupVisibility == 0)){
                    unset($groupMapping[$id]);
                   continue;
                }
                $groupname = $group->get_groupname();
                $readCheck = $this->object->check_access_read($group);
                $writeCheck = $this->object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $group);
                $sanctionCheck = $this->object->check_access(SANCTION_SANCTION, $group);


                if ($sanctionCheck) {
                    $dropDownValue = 3;
                } elseif ($writeCheck) {
                    $dropDownValue = 2;
                } elseif ($readCheck) {
                    $dropDownValue = 1;
                } else {
                    $dropDownValue = 0;
                }

                $groupsRights[$id] = $dropDownValue;


                $explodeName = array();
                $explodeName = explode(".", $groupname);

                $explodeLength = count($explodeName);


                $ddl = new \Widgets\DropDownList();
                $ddl->setId("group_" . $id . "_dd");
                $ddl->setName("ddlist");
                $ddl->setOnChange("specificChecked(id, value);");
                $ddl->setSize("1");
                $ddl->setDisabled(false);

                $intend = $explodeLength;
                if ($intend == 1) {
                    $optionValues = self::getOptionsValues(0);
                } else {
                    $parent = $group->get_parent_group();
                    $readCheckParent = $this->object->check_access_read($parent);
                    $writeCheckParent = $this->object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $parent);
                    $sanctionCheckParent = $this->object->check_access(SANCTION_SANCTION, $parent);
                    if ($sanctionCheckParent) {
                        $dropDownValueParent = 3;
                    } else if ($writeCheckParent) {
                        $dropDownValueParent = 2;
                    } else if ($readCheckParent) {
                        $dropDownValueParent = 1;
                    } else {
                        $dropDownValueParent = 0;
                    }
                    $optionValues = self::getOptionsValues($dropDownValueParent);
                }
                $ddl->setOptionValues($optionValues);

                if ($groupname != "Everyone" && $groupname != "sTeam") {
                    $content->setCurrentBlock("GROUPS");
                    $content->setCurrentBlock("GROUP_DDSETTINGS");
                    $content->setVariable("GROUPID", $id);
                    $content->setVariable("GROUP_ID", $id);
                    $content->setVariable("GROUPNAME", $name . " (". $realname . ")" );
                    $content->setVariable("OPTIONVALUE", max($dropDownValue, $dropdownValueSteamGroup));
                    $content->setVariable("INDENTINDEX", $intend);
                    $content->setVariable("DROPDOWNLIST", $ddl->getHtml());
                    if (isset($favorites[$id])) {
                        $content->setVariable("IMG_PATH", $favPicUrl);
                    } else {
                        $content->setVariable("IMG_PATH", $groupPicUrl);
                    }
                    $content->parse("GROUP_DDSETTINGS");
                    $content->parse("GROUPS");
                }
            }
        }
        if (count($groupMappingAcq) == 0) {
            $content->setVariable("NO_GROUP_MEMBER_ACQ", "Sie sind kein Mitglied einer Gruppe");
        } else {
            foreach ($groupMappingAcq as $id => $group) {
                 $groupVisibility = $group->get_attribute("GROUP_INVISIBLE");
                if(!($groupVisibility === 0)){
                   continue;
                }
                $name = $group->get_attribute("OBJ_DESC");
                $realname = $group->get_name();
                if ($name == "" || $name == "0") {
                    $name = $group->get_name();
                }
                $groupname = $group->get_groupname();
                if ($this->environment instanceof \steam_room) {
                    $readCheckAcq = $this->environment->check_access_read($group);
                    $writeCheckAcq = $this->environment->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $group);
                    $sanctionCheckAcq = $this->environment->check_access(SANCTION_SANCTION, $group);
                } else {
                    $readCheckAcq = 0;
                    $writeCheckAcq = 0;
                    $sanctionCheckAcq = 0;
                }
                $dropDownValueAcq = 0;
                if ($sanctionCheckAcq) {
                    $dropDownValueAcq = 3;
                } else if ($writeCheckAcq) {
                    $dropDownValueAcq = 2;
                } else if ($readCheckAcq) {
                    $dropDownValueAcq = 1;
                }
                $explodeName = array();
                $explodeName = explode(".", $groupname);

                $explodeLength = count($explodeName);

                $ddlAcq = new \Widgets\DropDownList();
                $ddlAcq->setId("group_" . $id . "_dd_acq");
                $ddlAcq->setName("ddlist_acq");
                $ddlAcq->setSize("1");
                $ddlAcq->setDisabled(true);

                $optionValuesAcq = self::getOptionsValues($dropDownValueAcq);

                $ddlAcq->setOptionValues($optionValuesAcq);

                $intend = count(explode(".", $groupname));

                if ($name != "Everyone" && $name != "sTeam") {
                    $content->setCurrentBlock("GROUPS_ACQ");
                    $content->setCurrentBlock("GROUP_DDSETTINGS_ACQ");
                    $content->setVariable("GROUPID_ACQ", $id);
                    $content->setVariable("GROUP_ID_ACQ", $id);
                    $content->setVariable("GROUPNAME_ACQ", $name . " (". $realname . ")" );
                    $content->setVariable("OPTIONVALUE_ACQ", max($dropDownValueAcq, $dropdownValueAcqSteamGroup));
                    $content->setVariable("INDENTINDEX_ACQ", $intend);
                    $content->setVariable("DROPDOWNLIST_ACQ", $ddlAcq->getHtml());
                    if (isset($favorites[$id])) {
                        $content->setVariable("IMG_PATH_ACQ", $favPicUrl);
                    } else {
                        $content->setVariable("IMG_PATH_ACQ", $groupPicUrl);
                    }
                    $content->parse("GROUP_DDSETTINGS_ACQ");
                    $content->parse("GROUPS_ACQ");
                }
            }
        }

        //TEMPLATE FAVORITES
        if (count($userMapping) == 0) {
            $content->setVariable("NO_FAV_MEMBER", "Es können keinem Benutzer Rechte zugewiesen werden. ");
        } else {
            $content->setVariable("DUMMY_FAV", "");
            $content->setVariable("DUMMY_FAV_ACQ", "");

            foreach ($userMapping as $id => $name) {
                $favo = \steam_factory::get_object($this->steam->get_id(), $id);
                if ($favo instanceof \steam_user) {
                    $readCheck = $this->object->check_access_read($favo);
                    $writeCheck = $this->object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $favo);
                    $sanctionCheck = $this->object->check_access(SANCTION_SANCTION, $favo);


                    $dropDownValue = 0;
                    if ($sanctionCheck) {
                        $dropDownValue = 3;
                    } elseif ($writeCheck) {
                        $dropDownValue = 2;
                    } elseif ($readCheck) {
                        $dropDownValue = 1;
                    }


                    $userGroups = $favo->get_groups();
                    $maxSanct = 0;
                    foreach ($userGroups as $group) {
                        if (isset($groupMapping[$group->get_id()])) {
                            $currentValue = $groupsRights[$group->get_id()];
                            if ($currentValue > $maxSanct) {
                                $maxSanct = $currentValue;
                            }
                        }
                    }

                    if ($dropDownValue > $maxSanct) {
                        $selectedValue = $dropDownValue;
                    } else {
                        $selectedValue = $maxSanct;
                    }

                    $ddl = new \Widgets\DropDownList();
                    $ddl->setId("fav_" . $id . "_dd");
                    $ddl->setName("ddlist");
                    $ddl->setOnChange("specificChecked(id, value);");
                    $ddl->setSize("1");
                    $ddl->setDisabled(false);
                    $ddl->setStartValue($selectedValue);
                    $optionValues = self::getOptionsValues($maxSanct);
                    $ddl->setOptionValues($optionValues);


                    $content->setCurrentBlock("FAVORITES");
                    $content->setCurrentBlock("FAV_DDSETINGS");
                    $content->setVariable("FAVNAME", $name);
                    $content->setVariable("DROPDOWNLIST_USER", $ddl->getHtml());
                    if (isset($favorites[$id])) {
                        $content->setVariable("IMG_PATH", $favPicUrl);
                    } else {
                        $content->setVariable("IMG_PATH", $userPicUrl);
                    }
                    $content->parse("FAV_DDSETTINGS");
                    $content->parse("FAVORITES");
                }
            }
        }
        if (count($userMappingAcq) == 0) {
            $content->setVariable("NO_FAV_MEMBER_ACQ", "Es können keinem Benutzer Rechte zugewiesen werden.");
        }foreach ($userMappingAcq as $id => $name) {
            $favo = \steam_factory::get_object($this->steam->get_id(), $id);
            if ($this->environment instanceof \steam_room) {
                $readCheckAcq = $this->environment->check_access_read($favo);
                $writeCheckAcq = $this->environment->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $favo);
                $sanctionCheckAcq = $this->environment->check_access(SANCTION_SANCTION, $favo);
            } else {
                $readCheckAcq = 0;
                $writeCheckAcq = 0;
                $sanctionCheckAcq = 0;
            }

            $dropDownValueAcq = 0;
            if ($sanctionCheckAcq) {
                $dropDownValueAcq = 3;
            } elseif ($writeCheckAcq) {
                $dropDownValueAcq = 2;
            } elseif ($readCheckAcq) {
                $dropDownValueAcq = 1;
            }

            $userGroups = $favo->get_groups();
            $maxSanct = 0;
            foreach ($userGroups as $group) {
                if (isset($groupMapping[$group->get_id()])) {
                    $currentValue = $groupsRights[$group->get_id()];
                    if ($currentValue > $maxSanct) {
                        $maxSanct = $currentValue;
                    }
                }
            }


            if ($dropDownValueAcq > $maxSanct) {
                $selectedValue = $dropDownValueAcq;
            } else {
                $selectedValue = $maxSanct;
            }

            $ddl = new \Widgets\DropDownList();
            $ddl->setId("fav_" . $id . "_dd_acq");
            $ddl->setName("ddlist");
            $ddl->setOnChange("specificChecked(id, value);");
            $ddl->setSize("1");
            $ddl->setDisabled(true);
            $ddl->setStartValue($selectedValue);
            $optionValues = self::getOptionsValues(0);
            $ddl->setOptionValues($optionValues);


            $content->setCurrentBlock("FAVORITES_ACQ");
            $content->setCurrentBlock("FAV_DDSETINGS_ACQ");
            $content->setVariable("FAVNAME_ACQ", $name);
            $content->setVariable("DROPDOWNLIST_USER_ACQ", $ddl->getHtml());
            if (isset($favorites[$id])) {
                $content->setVariable("IMG_PATH_ACQ", $favPicUrl);
            } else {
                $content->setVariable("IMG_PATH_ACQ", $userPicUrl);
            }
            $content->parse("FAV_DDSETTING_ACQS");
            $content->parse("FAVORITES_ACQ");
        }

        $sanctionURL = "http://$_SERVER[HTTP_HOST]" . "/Sanction/Index/" . $this->id . "/";

        $isAdmin = \lms_steam::is_steam_admin($this->steamUser);
        if($isAdmin){
          $dialog->setCustomButtons(array(array("class" => "button pill", "js" => "window.open('$sanctionURL', '_self')", "label" => "Erweiterte Ansicht öffnen")));
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($content->get());
        $dialog->addWidget($rawHtml);

        $ajaxResponseObject->addWidget($dialog);


        if($this->object->get_attribute("OBJ_TYPE") === "postbox"){
            $deactiveAcq = new \Widgets\JSWrapper();
            $deactiveAcq->setPostJsCode('$("#radio_acquire").attr("disabled",true);');
            $ajaxResponseObject->addWidget($deactiveAcq);
        }

        return $ajaxResponseObject;
    }

    private static

    function getOptionsValues($dropDownValue) {
        $optionValues = array();
        for ($i = $dropDownValue; $i <= 3; $i++) {
            if ($i == 1) {
                $optionValues[1] = "Nur Lesen";
            } else if ($i == 2) {
                $optionValues[2] = "Lesen und Schreiben";
            } else if ($i == 3) {
                $optionValues[3] = "Lesen, Schreiben und Berechtigen";
            } else if ($i == 0) {
                $optionValues[0] = "Kein Zugriff";
            }
        }
        return $optionValues;
    }

}

?>

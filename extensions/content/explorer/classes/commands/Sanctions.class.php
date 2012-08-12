<?php

namespace Explorer\Commands;

class Sanctions extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

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
        //Hole Objekt
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $objId = $this->id;
        $ajaxResponseObject->setStatus("ok");
        $accessRight = $object->check_access(SANCTION_SANCTION);
        //Prüfe, ob Berechtigung vorhanden
        if (!$accessRight) {
            $labelDenied = new \Widgets\RawHtml();
            $labelDenied->setHtml("Sie haben keine Berechtigung die Rechte einzusehen und zu verändern!");
            $dialogDenied = new \Widgets\Dialog();
            $dialogDenied->setTitle("Rechte von »" . getCleanName($object) . "«");
            $dialogDenied->addWidget($labelDenied);

            $ajaxResponseObject->addWidget($dialogDenied);
            return $ajaxResponseObject;
        }

        $steam = $GLOBALS["STEAM"];
        $steamUser = \lms_steam::get_current_user();


        $dialog = new \Widgets\Dialog();
        $dialog->setWidth(600);
        $dialog->setTitle("Rechte von »" . getCleanName($object) . "«");

        $dialog->setPositionX($this->params["mouseX"]);
        $dialog->setPositionY($this->params["mouseY"]);

        //GET CREATOR TODO: USEFULL FOR ROOT FOLDER
        //SET ICON URL
        $privatePicUrl = PATH_URL . "explorer/asset/icons/private.png";
        $userdefPicUrl = PATH_URL . "explorer/asset/icons/user_defined.png";
        $userglobalPicUrl = PATH_URL . "explorer/asset/icons/server_public.png";
        $worldglobalPicUrl = PATH_URL . "explorer/asset/icons/world_public.png";
        $userPicUrl = PATH_URL . "explorer/asset/icons/user.png";
        $groupPicUrl = PATH_URL . "explorer/asset/icons/group.png";
        $favPicUrl = PATH_URL . "explorer/asset/icons/red.png";

        //GET OWNER OF THE CURRENT OBJECT
        $owner = $object->get_creator();
        $creatorId = $owner->get_id();
        $ownerFullName = $owner->get_full_name();

        //GET ACQUIRE SETTINGS
        $acquire = $object->get_acquire();
        $acqChecked = $acquire instanceof \steam_room ? true : false;

        //GET FAVORITES
        $favs = $steamUser->get_buddies();
        $favorites = array();
        $favoritesAcq = array();

        foreach ($favs as $fav) {
            $favorites[$fav->get_id()] = $fav;
            $favoritesAcq[$fav->get_id()] = $fav;
        }

        //GET GROUPS
        $groupsA = $steamUser->get_groups();
        $groups = array();
        $groupsAcq = array();
        foreach ($groupsA as $g) {
            $groupsAcq[$g->get_id()] = $g;
            $groups[$g->get_id()] = $g;
        }
        //GET GROUPS EVERYONE
        $everyone = \steam_factory::groupname_to_object($steam->get_id(), "everyone");
        $everyoneId = $everyone->get_id();
        //GET GROUP STEAM
        $steamgroup = \steam_factory::groupname_to_object($steam->get_id(), "sTeam");
        $steamgroupId = $steamgroup->get_id();
        //GET SOME ATTRIBUTES
        $attrib = $object->get_attributes(array(OBJ_NAME, OBJ_DESC, "bid:doctype"));
        //GET SANCTION
        $sanction = $object->get_sanction();

        $env = $object->get_environment();
        $envName = $env instanceof \steam_room ? $env->get_name() : "";

        if ($env instanceof \steam_room) {
            $environmentSanction = $env->get_sanction();
        }
        $additionalUser = array();
        $additionalGroups = array();
        foreach ($sanction as $id => $sanct) {
            if (!array_key_exists($id, $groups) &&
                    !array_key_exists($id, $favorites) &&
                    $id != $creatorId && $id != 0 &&
                    $id != $everyoneId) {
                $additionalObject = \steam_factory::get_object($steam->get_id(), $id);
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
                        $id != $creatorId && $id != 0 &&
                        $id != $everyoneId) {
                    $additionalObject = \steam_factory::get_object($steam->get_id(), $id);
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

        $bid_doctype = isset($attrib["bid:doctype"]) ? $attrib["bid:doctype"] : "";
        $docTypeQuestionary = strcmp($attrib["bid:doctype"], "questionary") == 0;
        $docTypeMessageBoard = $object instanceof \steam_messageboard;

        if ($docTypeQuestionary) {
            $SANCTION_WRITE_FOR_CURRENT_OBJECT = SANCTION_INSERT;
        }
        // In message boards only annotating is allowed. The owner
        // is the only one who can also write and change message
        // board entries.
        else if ($docTypeMessageBoard) {
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
                            $group = \steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $string);
                            $groupId = $group->get_id();
                            $groupMappingName[$string] = $groupId;
                            $groupMappingA[$groupId] = $string;
                            $groups[$groupId] = $group;
                        }
                    } else {
                        $string .= "." . $array[$i];
                        if (!isset($groupMappingName[$string])) {
                            $group = \steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $string);
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
                            $group = \steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $string);
                            $groupId = $group->get_id();
                            $groupMappingNameAcq[$string] = $groupId;
                            $groupMappingAAcq[$groupId] = $string;
                            $groupsAcq[$groupId] = $group;
                        }
                    } else {
                        $string .= "." . $array[$i];
                        if (!isset($groupMappingNameAcq[$string])) {
                            $group = \steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $string);
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
        //ACQUIRE
        if ($envName == "") {
            $content->setVariable("NO_ENVIRONMENT", "disabled");
        }
        if ($acqChecked) {
            $content->setVariable("ACQUIRE_START", "activateAcq();");
        }

        $content->setVariable("INHERIT_FROM", getCleanName($env));
        //PICTURES
        $content->setVariable("PRIVATE_PIC", $privatePicUrl);
        $content->setVariable("USER_DEF_PIC", $userdefPicUrl);
        $content->setVariable("USER_GLOBAL_PIC", $userglobalPicUrl);
        $content->setVariable("SERVER_GLOBAL_PIC", $worldglobalPicUrl);
        //OWNER
        $content->setVariable("OWNER_FULL_NAME", $ownerFullName);

        $content->setVariable("EVERYONEID", $everyoneId);
        $readCheck = $object->check_access_read($everyone);
        $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $everyone);
        $sanctionCheck = $object->check_access(SANCTION_SANCTION, $everyone);
        $dropdownValue = 0;
        if ($sanctionCheck)
            $dropdownValue = 3;
        else if ($writeCheck)
            $dropdownValue = 2;
        else if ($readCheck)
            $dropdownValue = 1;
        $content->setVariable("EVERYONE_VALUE", $dropdownValue);

        if ($env instanceof \steam_room) {
            $readCheckAcq = $env->check_access_read($everyone);
            $writeCheckAcq = $env->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $everyone);
            $sanctionCheckAcq = $env->check_access(SANCTION_SANCTION, $everyone);
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
        $readCheckSteamGroup = $object->check_access_read($steamgroup);
        $writeCheckSteamGroup = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $steamgroup);
        $sanctionCheckSteamGroup = $object->check_access(SANCTION_SANCTION, $steamgroup);
        $dropdownValueSteamGroup = 0;
        if ($sanctionCheckSteamGroup)
            $dropdownValueSteamGroup = 3;
        else if ($writeCheckSteamGroup)
            $dropdownValueSteamGroup = 2;
        else if ($readCheckSteamGroup)
            $dropdownValueSteamGroup = 1;
        $content->setVariable("STEAM_VALUE", $dropdownValueSteamGroup);

        if ($env instanceof \steam_room) {
            $readCheckAcqSteamGroup = $env->check_access_read($steamgroup);
            $writeCheckAcqSteamGroup = $env->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $steamgroup);
            $sanctionCheckAcqSteamGroup = $env->check_access(SANCTION_SANCTION, $steamgroup);
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
        $content->setVariable("SEND_REQUEST_SANCTION", 'sendRequest("UpdateSanctions", { "id": ' . $objId . ', "sanctionId": id, "type": "sanction", "value": value }, "", "data", function(response){jQuery(\'#dynamic_wrapper\').remove(); jQuery(\'#overlay\').remove(); sendRequest(\'Sanctions\', {\'id\':\'' . $objId . '\'}, \'\', \'popup\', null, null, \'explorer\');}, null, "explorer");');
        $content->setVariable("SEND_REQUEST_CRUDE", 'sendRequest("UpdateSanctions", { "id": ' . $objId . ', "type": "crude", "value": value }, "", "data", function(response){jQuery(\'#dynamic_wrapper\').remove(); jQuery(\'#overlay\').remove(); sendRequest(\'Sanctions\', {\'id\':\'' . $objId . '\'}, \'\', \'popup\', null, null, \'explorer\');}, null, "explorer");');
        $content->setVariable("SEND_REQUEST_ACQ_ACT", 'sendRequest("UpdateSanctions", { "id": ' . $objId . ', "type": "acquire", "value": "acq" }, "", "data", null, null, "explorer");');
        $content->setVariable("SEND_REQUEST_ACQ_DEACT", 'sendRequest("UpdateSanctions", { "id": ' . $objId . ', "type": "acquire", "value": "non_acq" }, "", "data", null, null, "explorer");');
        //TEMPLATE GROUPS

        if (count($groupMapping) == 0) {
            $content->setVariable("NO_GROUP_MEMBER", "Sie sind kein Mitglied einer Gruppe");
        } else {
            $groupsRights = array();
            if (count($groupMapping) > 5) {
                $content->setVariable("CSS_GROUPS", "height: 125px;");
            } else {
                $content->setVariable("CSS_GROUPS", "");
            }
            foreach ($groupMapping as $id => $group) {
                $name = $group->get_attribute("OBJ_DESC");
                if ($name == "" || $name == "0") {
                    $name = $group->get_name();
                }
                $groupname = $group->get_groupname();
                $readCheck = $object->check_access_read($group);
                $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $group);
                $sanctionCheck = $object->check_access(SANCTION_SANCTION, $group);


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
                    $readCheckParent = $object->check_access_read($parent);
                    $writeCheckParent = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $parent);
                    $sanctionCheckParent = $object->check_access(SANCTION_SANCTION, $parent);
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
                    $content->setVariable("GROUPNAME", $name);
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
                $name = $group->get_attribute("OBJ_DESC");
                if ($name == "" || $name == "0") {
                    $name = $group->get_name();
                }
                $groupname = $group->get_groupname();
                if ($env instanceof \steam_room) {
                    $readCheckAcq = $env->check_access_read($group);
                    $writeCheckAcq = $env->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $group);
                    $sanctionCheckAcq = $env->check_access(SANCTION_SANCTION, $group);
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
                $explodeName = array();
                $explodeName = explode(".", $groupname);

                $explodeLength = count($explodeName);

                $ddlAcq = new \Widgets\DropDownList();
                $ddlAcq->setId("group_" . $id . "_dd_acq");
                $ddlAcq->setName("ddlist_acq");
                $ddlAcq->setSize("1");
                $ddlAcq->setDisabled(true);

                $optionValuesAcq = self::getOptionsValues(1);

                $ddlAcq->setOptionValues($optionValuesAcq);

                $intend = count(explode(".", $groupname));

                if ($name != "Everyone" && $name != "sTeam") {
                    $content->setCurrentBlock("GROUPS_ACQ");
                    $content->setCurrentBlock("GROUP_DDSETTINGS_ACQ");
                    $content->setVariable("GROUPID_ACQ", $id);
                    $content->setVariable("GROUP_ID_ACQ", $id);
                    $content->setVariable("GROUPNAME_ACQ", $name);
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
            if (count($userMapping) > 5) {
                $content->setVariable("CSS_USER", "height: 100px;");
            } else {
                $content->setVariable("CSS_USER", "");
            }
            foreach ($userMapping as $id => $name) {
                $favo = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
                if ($favo instanceof \steam_user) {
                    $readCheck = $object->check_access_read($favo);
                    $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $favo);
                    $sanctionCheck = $object->check_access(SANCTION_SANCTION, $favo);


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
            $favo = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
            if ($env instanceof \steam_room) {
                $readCheckAcq = $env->check_access_read($favo);
                $writeCheckAcq = $env->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $favo);
                $sanctionCheckAcq = $env->check_access(SANCTION_SANCTION, $favo);
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
            $optionValues = self::getOptionsValues($maxSanct);
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


        /*  $content->setCurrentBlock("FAVORITES_ACQ");
          $content->setCurrentBlock("FAV_DDSETINGS_ACQ");
          $content->setVariable("FAVID_ACQ", $id);
          $content->setVariable("FAV_ID_ACQ", $id);
          $content->setVariable("FAVNAME_ACQ", $name);
          $content->setVariable("FAV_OPTION_VALUE_ACQ", $dropDownValueAcq);
          if (isset($favorites[$id])) {
          $content->setVariable("IMG_PATH_ACQ", $favPicUrl);
          } else {
          $content->setVariable("IMG_PATH_ACQ", $userPicUrl);
          }
          $content->parse("FAV_DDSETTING_ACQS");
          $content->parse("FAVORITES_ACQ");
          } */


        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($content->get());
        $dialog->addWidget($rawHtml);

        $ajaxResponseObject->addWidget($dialog);

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
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


        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $objId = $this->id;
        $ajaxResponseObject->setStatus("ok");
        $accessRight = $object->check_access(SANCTION_SANCTION);

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
        $env = $object->get_environment();
        $envName = $env instanceof \steam_room ? $env->get_name() : "";

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
        foreach ($favs as $fav) {
            $favorites[$fav->get_id()] = $fav;
        }

        //GET GROUPS
        $groups = $steamUser->get_groups();
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
        if ($env instanceof \steam_room) {
            $environmentSanction = $env->get_sanction();
        }

        $additionalUser = array();
        foreach ($sanction as $id => $sanct) {
            if (!array_key_exists($id, $groups) &&
                    !array_key_exists($id, $favorites) &&
                    $id != $creatorId && $id != 0 &&
                    $id != $everyoneId) {
                $additionalUser[$id] = \steam_factory::get_object($steam->get_id(), $id);
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
        //MAPPING GROUPS
        $groupsMappingName = array();
        $groupsMapping = array();
        foreach ($groups as $group) {
            $id = $group->get_id();
            $name = $group->get_groupname();
            $groupsMappingName[$name] = $id;
            $groupsMapping[$id] = $name;
        }
        //MAPPING FAVORITES
        $favoritesMapping = array();
        foreach ($favorites as $favorite) {
            if ($favorite instanceof \steam_user) {
                $favoritesMapping[$favorite->get_id()] = $favorite->get_full_name();
            } else {
                $favoritesMapping[$favorite->get_id()] = $favorite->get_groupname();
            }
        }
        //MAPPING ADDITIONAL USERS
        $additionalMapping = array();
        foreach ($sanction as $id => $sanct) {
            if (!array_key_exists($id, $groupsMapping) &&
                    !array_key_exists($id, $favoritesMapping) &&
                    $id != $creatorId && $id != 0 &&
                    $id != $everyoneId) {
                $additionalMapping[$id] = \steam_factory::get_object($steam->get_id(), $id)->get_full_name();
            }
        }
        //MAPPING ADDITIONAL USERS ACQUIRED
        $additionalMappingEnvironment = array();
        if (isset($environmentSanction) && count($environmentSanction) > 0) {
            foreach ($environmentSanction as $id => $sanct) {
                if (!array_key_exists($id, $groupsMapping) &&
                        !array_key_exists($id, $favoritesMapping) &&
                        $id != $creatorId && $id != 0 &&
                        $id != $groupEveryoneId) {
                    $additionalMappingEnvironment[$id] = \steam_factory::get_object($steam, $id)->get_name();
                }
            }
        }



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
        $readCheck = $object->check_access_read($steamgroup);
        $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $steamgroup);
        $sanctionCheck = $object->check_access(SANCTION_SANCTION, $steamgroup);
        $dropdownValue = 0;
        if ($sanctionCheck)
            $dropdownValue = 3;
        else if ($writeCheck)
            $dropdownValue = 2;
        else if ($readCheck)
            $dropdownValue = 1;
        $content->setVariable("STEAM_VALUE", $dropdownValue);

        if ($env instanceof \steam_room) {
            $readCheckAcq = $env->check_access_read($steamgroup);
            $writeCheckAcq = $env->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $steamgroup);
            $sanctionCheckAcq = $env->check_access(SANCTION_SANCTION, $steamgroup);
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
        $content->setVariable("STEAM_VALUE_ACQ", $dropdownValueAcq);

        $content->setVariable("EVERYONE_ID", $everyoneId);
        $content->setVariable("STEAM_ID", $steamgroupId);
        $content->setVariable("SEND_REQUEST_SANCTION", 'sendRequest("UpdateSanctions", { "id": ' . $objId . ', "sanctionId": id, "type": "sanction", "value": value }, "", "data", function(response){jQuery(\'#dynamic_wrapper\').remove(); jQuery(\'#overlay\').remove(); sendRequest(\'Sanctions\', {\'id\':\'' . $objId . '\'}, \'\', \'popup\', null, null, \'explorer\');}, null, "explorer");');
        $content->setVariable("SEND_REQUEST_CRUDE", 'sendRequest("UpdateSanctions", { "id": ' . $objId . ', "type": "crude", "value": value }, "", "data", function(response){jQuery(\'#dynamic_wrapper\').remove(); jQuery(\'#overlay\').remove(); sendRequest(\'Sanctions\', {\'id\':\'' . $objId . '\'}, \'\', \'popup\', null, null, \'explorer\');}, null, "explorer");');
        $content->setVariable("SEND_REQUEST_ACQ_ACT", 'sendRequest("UpdateSanctions", { "id": ' . $objId . ', "type": "acquire", "value": "acq" }, "", "data", null, null, "explorer");');
        $content->setVariable("SEND_REQUEST_ACQ_DEACT", 'sendRequest("UpdateSanctions", { "id": ' . $objId . ', "type": "acquire", "value": "non_acq" }, "", "data", null, null, "explorer");');
        //TEMPLATE GROUPS
        if (count($groupsMapping) == 0) {
            $content->setVariable("NO_GROUP_MEMBER", "Sie sind kein Mitglied einer Gruppe");
            $content->setVariable("NO_GROUP_MEMBER_ACQ", "Sie sind kein Mitglied einer Gruppe");
        } else {
            sort($groupsMapping);
            $lastExplotedArray = array();
            $lastExplotedArray[0] = 0;
            $lastIntendIndex = 1;
            $lastDropDownValue = 0;
            foreach ($groupsMapping as $name) {
                $id = $groupsMappingName[$name];
                $group = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
                $readCheck = $object->check_access_read($group);
                $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $group);
                $sanctionCheck = $object->check_access(SANCTION_SANCTION, $group);

                if ($env instanceof \steam_room) {
                    $readCheckAcq = $env->check_access_read($group);
                    $writeCheckAcq = $env->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $group);
                    $sanctionCheckAcq = $env->check_access(SANCTION_SANCTION, $group);
                } else {
                    $readCheckAcq = 0;
                    $writeCheckAcq = 0;
                    $sanctionCheckAcq = 0;
                }

                if ($sanctionCheck) {
                    $dropDownValue = 3;
                    
                } elseif ($writeCheck) {
                    $dropDownValue = 2;
                    
                } elseif ($readCheck) {
                    $dropDownValue = 1;
                    
                } else {
                    $dropDownValue = 0;
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
                $explodeName = explode(".", $name);

                $explodeLength = count($explodeName);
                $lastExploteLength = count($lastExplotedArray);
                 
                $ddl = new \Widgets\DropDownList();
                $ddl->setId("group_" . $id . "_dd");
                $ddl->setName("ddlist");
                $ddl->setOnChange("specificChecked(id, value);");
                $ddl->setSize("1");
                $ddl->setDisabled(false);
                
                //hack
                if ($explodeLength > $lastExploteLength) {
                    if (($explodeName[0] == $lastExplotedArray[0]) && ($explodeName[$lastExploteLength - 1] == $lastExplotedArray[$lastExploteLength - 1])) {
                        $indentIndex = $lastIntendIndex + 1;
                        if($dropDownValue == $lastDropDownValue){
                            $ddl->setDisabled(true);                          
                            $optionValues = self::getOptionsValues($dropDownValue);
                
                            
                        }
                    }
                } else if ($explodeLength == $lastExploteLength) {
                    if ($explodeName[0] == $lastExplotedArray[0]) {
                        $indentIndex = $lastIntendIndex;
                         $ddl->setDisabled(true);
                         $optionValues = self::getOptionsValues($dropDownValue);
                        
                    }
                } else {
                    $indentIndex = 1;
                    $optionValues = self::getOptionsValues(1);
                }
               $ddl->setOptionValues($optionValues);
                
                $lastExplotedArray = $explodeName;
                $lastIntendIndex = $indentIndex;

                $groupname = $group->get_groupname();

                if ($groupname != "Everyone" && $groupname != "sTeam") {
                    $content->setCurrentBlock("GROUPS");
                    $content->setCurrentBlock("GROUP_DDSETTINGS");
                    $content->setVariable("GROUPID", $id);
                    $content->setVariable("GROUP_ID", $id);
                    $content->setVariable("GROUPNAME", $groupname);
                    $content->setVariable("OPTIONVALUE", $dropDownValue);
                    $content->setVariable("INDENTINDEX", $indentIndex);                  
                    $content->setVariable("DROPDOWNLIST", $ddl->getHtml());
                    if (isset($favoritesMapping[$id])) {
                        $content->setVariable("IMG_PATH", $favPicUrl);
                    } else {
                        $content->setVariable("IMG_PATH", $groupPicUrl);
                    }
                    $content->parse("GROUP_DDSETTINGS");
                    $content->parse("GROUPS");
                }

                if ($name != "Everyone" && $name != "sTeam") {
                    $content->setCurrentBlock("GROUPS_ACQ");
                    $content->setCurrentBlock("GROUP_DDSETTINGS_ACQ");
                    $content->setVariable("GROUPID_ACQ", $id);
                    $content->setVariable("GROUP_ID_ACQ", $id);
                    $content->setVariable("GROUPNAME_ACQ", $name);
                    $content->setVariable("OPTIONVALUE_ACQ", $dropDownValueAcq);
                    $content->parse("GROUP_DDSETTINGS_ACQ");
                    $content->parse("GROUPS_ACQ");
                }
                $lastDropDownValue = $dropDownValue;
            }
        }

        //TEMPLATE FAVORITES
        if (count($favoritesMapping) == 0) {
            $content->setVariable("NO_FAV_MEMBER", "Sie haben keine Favoriten");
            $content->setVariable("NO_FAV_MEMBER_ACQ", "Sie haben keine Favoriten");
        } else {
            $content->setVariable("DUMMY_FAV", "");
            $content->setVariable("DUMMY_FAV_ACQ", "");
            foreach ($favoritesMapping as $id => $name) {


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
                    $content->setCurrentBlock("FAVORITES");
                    $content->setCurrentBlock("FAV_DDSETINGS");
                    $content->setVariable("FAVID", $id);
                    $content->setVariable("FAV_ID", $id);
                    $content->setVariable("FAVNAME", $name);
                    $content->setVariable("FAV_OPTION_VALUE", $dropDownValue);
                    if (isset($favoritesMapping[$id])) {
                        $content->setVariable("IMG_PATH", $favPicUrl);
                    } else {
                        $content->setVariable("IMG_PATH", $userPicUrl);
                    }
                    $content->parse("FAV_DDSETTINGS");
                    $content->parse("FAVORITES");

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


                    $content->setCurrentBlock("FAVORITES_ACQ");
                    $content->setCurrentBlock("FAV_DDSETINGS_ACQ");
                    $content->setVariable("FAVID_ACQ", $id);
                    $content->setVariable("FAV_ID_ACQ", $id);
                    $content->setVariable("FAVNAME_ACQ", $name);
                    $content->setVariable("FAV_OPTION_VALUE_ACQ", $dropDownValueAcq);
                    $content->parse("FAV_DDSETTING_ACQS");
                    $content->parse("FAVORITES_ACQ");
                }
            }
        }

        //TEMPLATE ADDITIONAL USERS
        if (count($additionalMapping) == 0) {
            $content->setVariable("NO_AU_MEMBER", "Keine weiteren berechtigten Nutzer");
        } else {
            $content->setVariable("DUMMY_FAV", "");
            $content->setVariable("DUMMY_AU_ACQ", "");
            foreach ($additionalMapping as $id => $name) {
                $au = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);

                $readCheck = $object->check_access_read($au);
                $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $au);
                $sanctionCheck = $object->check_access(SANCTION_SANCTION, $au);
                $dropDownValue = 0;

                if ($sanctionCheck) {
                    $dropDownValue = 3;
                } elseif ($writeCheck) {
                    $dropDownValue = 2;
                } elseif ($readCheck) {
                    $dropDownValue = 1;
                }

                $content->setCurrentBlock("AU");
                $content->setCurrentBlock("AU_DDSETINGS");
                $content->setVariable("AUID", $id);
                $content->setVariable("AU_ID", $id);
                $content->setVariable("AUNAME", $name);
                $content->setVariable("AU_OPTION_VALUE", $dropDownValue);
                $content->parse("AU_DDSETTINGS");
                $content->parse("AU");
            }
        }
        if (count($additionalMappingEnvironment) == 0) {
            $content->setVariable("NO_AU_MEMBER_ACQ", "Keine weiteren berechtigten Nutzer");
        } else {
            foreach ($additionalMappingEnvironment as $id => $name) {
                $au = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
                $readCheck = $object->check_access_read($au);
                $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $au);
                $sanctionCheck = $object->check_access(SANCTION_SANCTION, $au);

                $dropDownValueAcq = 0;

                if ($sanctionCheck) {
                    $dropDownValueAcq = 3;
                } elseif ($writeCheck) {
                    $dropDownValueAcq = 2;
                } elseif ($readCheck) {
                    $dropDownValueAcq = 1;
                }

                $content->setCurrentBlock("AU_ACQ");
                $content->setCurrentBlock("AU_DDSETINGS_ACQ");
                $content->setVariable("AUID_ACQ", $id);
                $content->setVariable("AU_ID_ACQ", $id);
                $content->setVariable("AUNAME_ACQ", $name);
                $content->setVariable("AU_OPTION_VALUE_ACQ", $dropDownValueAcq);
                $content->parse("AU_DDSETTINGS_ACQ");
                $content->parse("AU_ACQ");
            }
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($content->get());
        $dialog->addWidget($rawHtml);

        $ajaxResponseObject->addWidget($dialog);
        return $ajaxResponseObject;
    }

    private static function getOptionsValues($dropDownValue) {
        $optionValues = array();
        $optionValues[0]= "";
        for($i=$dropDownValue;$i<=3;$i++){
            if($i == 1){
                $optionValues[1] = "Lesen"; 
            }else if($i == 2){
                $optionValues[2] = "Lesen und Schreiben";
            }else if($i == 3){
                $optionValues[3] = "Lesen, Schreiben und Berechtigen";
            }
        }
        return $optionValues;
    }

}

?>
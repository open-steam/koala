<?php

namespace Explorer\Commands;

class Sanctions extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
        
        if(!$accessRight){
            
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
        $groups[$everyoneId] = $everyone;
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
        $groupsMapping = array();
        $groupsMapping[$everyone->get_id()] = $everyone->get_name();
        foreach ($groups as $group) {
            $groupsMapping[$group->get_id()] = $group->get_name();
        }
        //MAPPING FAVORITES
        $favoritesMapping = array();
        foreach ($favorites as $favorite) {

            if ($favorite instanceof \steam_user) {
                $favoritesMapping[$favorite->get_id()] = $favorite->get_full_name();
            } else {
                $favoritesMapping[$favorite->get_id()] = $favorite->get_name();
            }
        }
        //MAPPING ADDITIONAL USERS
        $additionalMapping = array();
        foreach ($sanction as $id => $sanct) {
            if (!array_key_exists($id, $groupsMapping) &&
                    !array_key_exists($id, $favoritesMapping) &&
                    $id != $creatorId && $id != 0 &&
                    $id != $everyoneId) {
                $additionalMapping[$id] = \steam_factory::get_object($steam->get_id(), $id)->get_name();
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
            foreach ($groupsMapping as $id => $name) {
                $dropDownValue = 0;
                if (isset($sanction[$id])) {
                    if ($sanction[$id] == SANCTION_READ) {
                        $dropDownValue = 1;
                    } elseif ($sanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT)) {
                        $dropDownValue = 2;
                    } elseif ($sanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT | SANCTION_SANCTION)) {
                        $dropDownValue = 3;
                    }
                }
                $dropDownValueAcq = 0;
                if (isset($environmentSanction[$id])) {
                    if ($environmentSanction[$id] == SANCTION_READ) {
                        $dropDownValueAcq = 1;
                    } elseif ($environmentSanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT)) {
                        $dropDownValueAcq = 2;
                    } elseif ($environmentSanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT | SANCTION_SANCTION)) {
                        $dropDownValueAcq = 3;
                    }
                }

                //HACK
                $group = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
                //		SANCTION_WRITE | SANCTION_EXECUTE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE;

                $readCheck = $object->check_access_read($group);
                $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $group);
                $sanctionCheck = $object->check_access(SANCTION_SANCTION, $group);



                $content->setCurrentBlock("GROUPS");
                $content->setCurrentBlock("GROUP_DDSETTINGS");
                $content->setVariable("GROUPID", $id);
                $content->setVariable("GROUP_ID", $id);
                if ($sanctionCheck) {
                    $content->setVariable("GROUP_RIGHTS", "Lesen, Schreiben und Berechtigen");
                } elseif ($writeCheck) {
                    $content->setVariable("GROUP_RIGHTS", "Lesen und Schreiben");
                } elseif ($readCheck) {
                    $content->setVariable("GROUP_RIGHTS", "Nur Lesen");
                } else {
                    $content->setVariable("GROUP_RIGHTS", "");
                }
                if ($name == "Everyone") {
                    $content->setVariable("GROUPNAME", "Jeder");
                } else if ($name == "sTeam") {
                    $content->setVariable("GROUPNAME", "Angemeldete Benutzer");
                } else {
                    $content->setVariable("GROUPNAME", $group->get_groupname());
                }

                $content->setVariable("OPTIONVALUE", $dropDownValue);
                $content->parse("GROUP_DDSETTINGS");
                $content->parse("GROUPS");

                $content->setCurrentBlock("GROUPS_ACQ");
                $content->setCurrentBlock("GROUP_DDSETTINGS_ACQ");
                $content->setVariable("GROUPID_ACQ", $id);
                $content->setVariable("GROUP_ID_ACQ", $id);
                if ($name == "Everyone") {
                    $content->setVariable("GROUPNAME_ACQ", "Jeder");
                } else if ($name == "sTeam") {
                    $content->setVariable("GROUPNAME_ACQ", "Angemeldete Benutzer");
                } else {
                    $content->setVariable("GROUPNAME_ACQ", $name);
                }
                if ($sanctionCheck) {
                    $content->setVariable("GROUP_RIGHTS_ACQ", "Lesen, Schreiben und Berechtigen");
                } elseif ($writeCheck) {
                    $content->setVariable("GROUP_RIGHTS_ACQ", "Lesen und Schreiben");
                } elseif ($readCheck) {
                    $content->setVariable("GROUP_RIGHTS_ACQ", "Nur Lesen");
                } else {
                    $content->setVariable("GROUP_RIGHTS_ACQ", "");
                }
                $content->setVariable("OPTIONVALUE_ACQ", $dropDownValueAcq);
                $content->parse("GROUP_DDSETTINGS_ACQ");
                $content->parse("GROUPS_ACQ");
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
                $dropDownValue = 0;
                if (isset($sanction[$id])) {
                    if ($sanction[$id] == SANCTION_READ) {
                        $dropDownValue = 1;
                    } elseif ($sanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT)) {
                        $dropDownValue = 2;
                    } elseif ($sanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT | SANCTION_SANCTION)) {
                        $dropDownValue = 3;
                    }
                }
                $dropDownValueAcq = 0;
                if (isset($environmentSanction[$id])) {
                    if ($environmentSanction[$id] == SANCTION_READ) {
                        $dropDownValueAcq = 1;
                    } elseif ($environmentSanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT)) {
                        $dropDownValueAcq = 2;
                    } elseif ($environmentSanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT | SANCTION_SANCTION)) {
                        $dropDownValueAcq = 3;
                    }
                }
                $favo = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);

                $readCheck = $object->check_access_read($favo);
                $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $favo);
                $sanctionCheck = $object->check_access(SANCTION_SANCTION, $favo);

                $content->setCurrentBlock("FAVORITES");
                $content->setCurrentBlock("FAV_DDSETINGS");
                $content->setVariable("FAVID", $id);
                $content->setVariable("FAV_ID", $id);
                $content->setVariable("FAVNAME", $name);
                $content->setVariable("FAV_OPTION_VALUE", $dropDownValue);
                if ($sanctionCheck) {
                    $content->setVariable("FAV_RIGHTS", "Lesen, Schreiben und Berechtigen");
                } elseif ($writeCheck) {
                    $content->setVariable("FAV_RIGHTS", "Lesen und Schreiben");
                } elseif ($readCheck) {
                    $content->setVariable("FAV_RIGHTS", "Nur Lesen");
                } else {
                    $content->setVariable("FAV_RIGHTS", "");
                }
                $content->parse("FAV_DDSETTINGS");
                $content->parse("FAVORITES");



                $content->setCurrentBlock("FAVORITES_ACQ");
                $content->setCurrentBlock("FAV_DDSETINGS_ACQ");
                $content->setVariable("FAVID_ACQ", $id);
                $content->setVariable("FAV_ID_ACQ", $id);
                if ($sanctionCheck) {
                    $content->setVariable("FAV_RIGHTS_ACQ", "Lesen, Schreiben und Berechtigen");
                } elseif ($writeCheck) {
                    $content->setVariable("FAV_RIGHTS_ACQ", "Lesen und Schreiben");
                } elseif ($readCheck) {
                    $content->setVariable("FAV_RIGHTS_ACQ", "Nur Lesen");
                } else {
                    $content->setVariable("FAV_RIGHTS_ACQ", "");
                }
                $content->setVariable("FAVNAME_ACQ", $name);
                $content->setVariable("FAV_OPTION_VALUE_ACQ", $dropDownValueAcq);
                $content->parse("FAV_DDSETTING_ACQS");
                $content->parse("FAVORITES_ACQ");
            }
        }

        //TEMPLATE ADDITIONAL USERS
        if (count($additionalMapping) == 0) {
            $content->setVariable("NO_AU_MEMBER", "Keine weiteren berechtigten Nutzer");
        } else {
            $content->setVariable("DUMMY_AU", "");
            $content->setVariable("DUMMY_AU_ACQ", "");
            foreach ($additionalMapping as $id => $name) {
                $au = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
                //		SANCTION_WRITE | SANCTION_EXECUTE | SANCTION_MOVE | SANCTION_INSERT | SANCTION_ANNOTATE;

                $readCheck = $object->check_access_read($au);
                $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $au);
                $sanctionCheck = $object->check_access(SANCTION_SANCTION, $au);
                $dropDownValue = 0;
                if (isset($sanction[$id])) {
                    if ($sanction[$id] == SANCTION_READ) {
                        $dropDownValue = 1;
                    } elseif ($sanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT)) {
                        $dropDownValue = 2;
                    } elseif ($sanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT | SANCTION_SANCTION)) {
                        $dropDownValue = 3;
                    }
                }
                $content->setCurrentBlock("AU");
                $content->setCurrentBlock("AU_DDSETINGS");
                $content->setVariable("AUID", $id);
                $content->setVariable("AU_ID", $id);
                $content->setVariable("AUNAME", $name);
                $content->setVariable("AU_OPTION_VALUE", $dropDownValue);
                if ($sanctionCheck) {
                    $content->setVariable("AU_RIGHTS", "Lesen, Schreiben und Berechtigen");
                } elseif ($writeCheck) {
                    $content->setVariable("AU_RIGHTS", "Lesen und Schreiben");
                } elseif ($readCheck) {
                    $content->setVariable("AU_RIGHTS", "Nur Lesen");
                } else {
                    $content->setVariable("AU_RIGHTS", "");
                }
                $content->parse("AU_DDSETTINGS");
                $content->parse("AU");
            }
        }
        if (count($additionalMappingEnvironment) == 0) {
            $content->setVariable("NO_AU_MEMBER_ACQ", "Keine weiteren berechtigten Nutzer");
        } else {
            foreach ($additionalMappingEnvironment as $id => $name) {
                $readCheck = $object->check_access_read($au);
                $writeCheck = $object->check_access($SANCTION_WRITE_FOR_CURRENT_OBJECT, $au);
                $sanctionCheck = $object->check_access(SANCTION_SANCTION, $au);
                $dropDownValueAcq = 0;
                if (isset($environmentSanction[$id])) {
                    if ($environmentSanction[$id] == SANCTION_READ) {
                        $dropDownValueAcq = 1;
                    } elseif ($environmentSanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT)) {
                        $dropDownValueAcq = 2;
                    } elseif ($environmentSanction[$id] <= (SANCTION_READ | $SANCTION_WRITE_FOR_CURRENT_OBJECT | SANCTION_SANCTION)) {
                        $dropDownValueAcq = 3;
                    }
                }
                $content->setCurrentBlock("AU_ACQ");
                $content->setCurrentBlock("AU_DDSETINGS_ACQ");
                $content->setVariable("AUID_ACQ", $id);
                $content->setVariable("AU_ID_ACQ", $id);
                if ($sanctionCheck) {
                    $content->setVariable("AU_RIGHTS_ACQ", "Lesen, Schreiben und Berechtigen");
                } elseif ($writeCheck) {
                    $content->setVariable("AU_RIGHTS_ACQ", "Lesen und Schreiben");
                } elseif ($readCheck) {
                    $content->setVariable("AU_RIGHTS_ACQ", "Nur Lesen");
                } else {
                    $content->setVariable("AU_RIGHTS_ACQ", "");
                }
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

    public function frameResponse(\FrameResponseObject $frameResponseObject) {

        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $object = $currentUser->get_workroom();

        $dialog = new \Widgets\Dialog();
        $dialog->setTitle("Eigenschaften von " . $object->get_name());

        $dialog->setContent("Nulla dui purus, eleifend vel, consequat non, <br>
	dictum porta, nulla. Duis ante mi, laoreet ut,  <br>
	commodo eleifend, cursus nec, lorem. Aenean eu est.  <br>
	Etiam imperdiet turpis. Praesent nec augue. Curabitur  <br>
	ligula quam, rutrum id, tempor sed, consequat ac, dui. <br>
	Vestibulum accumsan eros nec magna. Vestibulum vitae dui. <br>
	Vestibulum nec ligula et lorem consequat ullamcorper.  <br>
	Class aptent taciti sociosqu ad litora torquent per  <br>
	conubia nostra, per inceptos hymenaeos. Phasellus  <br>
	eget nisl ut elit porta ullamcorper. Maecenas  <br>
	tincidunt velit quis orci. Sed in dui. Nullam ut  <br>
	mauris eu mi mollis luctus. Class aptent taciti  <br>
	sociosqu ad litora torquent per conubia nostra, per  <br>
	inceptos hymenaeos. Sed cursus cursus velit. Sed a  <br>
	massa. Duis dignissim euismod quam. Nullam euismod  <br>
	metus ut orci. Vestibulum erat libero, scelerisque et,  <br>
	porttitor et, varius a, leo.");
        $dialog->setButtons(array(array("name" => "speichern", "href" => "save")));
        return $dialog->getHtml();
    }

    public static function sortGroups($objects) {
        $names = array();
        foreach ($objects as $o) {
            if ($o instanceof \steam_group) {
                $names[$o->get_groupname()] = $o;
            }
        }
        $keys = array_keys($names);

        sort($keys);
        $result = array();
        foreach ($keys as $key) {
            $result[] = $names[$key];
        }


        return $result;
    }

    public static function sortFavorites($objects) {
        $names = array();
        foreach ($objects as $o) {
            if ($o instanceof \steam_group) {
                $names[$o->get_groupname()] = $o;
            } elseif ($o instanceof \steam_user) {
                $names[$o->get_name()] = $o;
            }
        }
        $keys = array_keys($names);
        sort($keys);
        $result = array();
        foreach ($keys as $key) {
            $result[] = $names[$key];
        }
        return $result;
    }

}

?>
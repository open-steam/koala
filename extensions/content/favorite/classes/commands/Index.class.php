<?php

namespace Favorite\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject = $this->execute($frameResponseObject);
        return $frameResponseObject;
    }

    public function execute(\FrameResponseObject $frameResponseObject) {


        //BEGIN Search-Part
        //DEFINITION OF IGNORED USERS AND GROUPS
        $ignoredUserNames = array(0 => "postman", 1 => "root", 2 => "guest");
        $ignoredGroupNames = array(0 => "sTeam", 1 => "admin");

        $steam = $GLOBALS["STEAM"];
        $action = (isset($_POST["action"])) ? $_POST["action"] : "";
        $searchString = (isset($_POST["searchString"])) ? trim($_POST["searchString"]) : "";
        $searchType = (isset($_POST["searchType"])) ? $_POST["searchType"] : "searchUser";
        $steamUser = \lms_steam::get_current_user();
        $searchResult = array();
        $min_search_string_count = 3;


        $buddies_user = array();
        $buddies_group = array();
        $buddies_user_name = array();
        $buddies_group_name = array();

        foreach ($steamUser->get_buddies() as $buddy) {
            $id = $buddy->get_id();
            if ($buddy instanceof \steam_user) {
                $buddies_user[$id] = $buddy;
                $buddies_user_name[$id] = $buddy->get_name();
            } else if ($buddy instanceof \steam_group) {
                $buddies_group[$id] = $buddy;
                $buddies_group_name[$id] = $buddy->get_groupname();
            }
        }

        //kann vermutlich weg 30.03.2016
        /* foreach ($buddies_user_name as $id => $val) {
          $buddies_user_name[$id] = $buddies_user_name[$id];
          }
          foreach ($buddies_group_name as $id => $val) {
          $buddies_group_name[$id] = $buddies_group_name[$id];
          } */

        // sort favourites
        natcasesort($buddies_user_name);
        natcasesort($buddies_group_name);


        //IF THERE IS AN ACTION TO DO
        if ($action != "") {

            if (strlen($searchString) < $min_search_string_count) {
                //$frameResponseObject->setProblemDescription(gettext("Search string too short"));
                $frameResponseObject->setProblemDescription("Länge der Suchanfrage zu klein! Eine Suchanfrage muss aus mindestens 3 Zeichen bestehen.");
            } elseif ((strpos($searchString, "*") !== FALSE) || (strpos($searchString, "?") !== FALSE) || (strpos($searchString, "%") !== FALSE)) {
                $frameResponseObject->setProblemDescription("Eine Suchanfrage darf aus Datenschutzgründen keine Wildcards (* , % und ?) enthalten.");

                //IF SEARCH REQUEST IS CLEAN
            } else {
                /* prepare search string */
                $modSearchString = $searchString;
                if ($modSearchString[0] != "%")
                    $modSearchString = "%" . $modSearchString;
                if ($modSearchString[strlen($modSearchString) - 1] != "%")
                    $modSearchString = $modSearchString . "%";

                $searchModule = $steam->get_module("searching");
                $searchobject = new \OpenSteam\Search\Searching($searchModule);
                $search = new \OpenSteam\Search\SearchDefine();
                switch ($searchType) {
                    case "searchUser":
                        $search->extendAttr("OBJ_NAME", \OpenSteam\Search\SearchDefine::like($modSearchString));
                        $resultItems = $searchobject->search($search, CLASS_USER);
                        foreach ($resultItems as $resultItem) {
                            $resultItemName[$resultItem->get_id()] = $resultItem->get_name(1);
                        }
                        $result = $steam->buffer_flush();
                        break;

                    case "searchGroup":
                        $search->extendAttr("OBJ_NAME", \OpenSteam\Search\SearchDefine::like($modSearchString));
                        $resultItems = $searchobject->search($search, CLASS_GROUP);
                        foreach ($resultItems as $resultItem) {
                            if ($resultItem instanceof \steam_group) {
                                $resultItemName[$resultItem->get_id()] = $resultItem->get_groupname(1);
                            }
                        }
                        $result = $steam->buffer_flush();
                        break;

                    case "searchUserFullname":
                        $cache = get_cache_function($steamUser->get_name(), 60);
                        $resultUser = $cache->call("lms_steam::search_user", $searchString, "name");
                        $resultItems = array();
                        for ($i = 0; $i < count($resultUser); $i++) {
                            $resultItems[$i] = \steam_factory::get_object($steam->get_id(), $resultUser[$i]["OBJ_ID"]);
                        }

                        foreach ($resultItems as $resultItem) {
                            $resultItemName[$resultItem->get_id()] = $resultItem->get_name();
                        }
                        
                        $result = array();
                        $counter = 0;
                        foreach ($resultItems as $resultItem) {
                            $result[$resultItem->get_name()] = $resultItem->get_id();
                            $counter++;
                        }
                        break;

                    default:
                        break;
                }

                //helper array: name-->id
                $helper = array();

                foreach ($resultItems as $resultItem) {
                    $id = $resultItem->get_id();

                    if ($resultItem instanceof \steam_object) {
                        try {
                            $helper[$resultItem->get_name()] = $id;
                        } catch (\Exception $e) {
                            $helper["defektes Objekt ({$id})"] = $id;
                        }
                    }

                    if ($resultItem instanceof \steam_group) {
                        try {
                            $helper[$resultItem->get_groupname()] = $id;
                        } catch (\Exception $e) {
                            $helper["defekte Gruppe ({$id})"] = $id;
                        }
                    }

                    $resultItemName[$id] = $result[$resultItemName[$id]];
                    $searchResult[] = $resultItemName[$id];
                }
            }
        }

        // sort favourites
        natcasesort($searchResult);

        // display actionbar

        $profileUtils = new \ProfileActionBar($steamUser, $steamUser);
        $actions = $profileUtils->getActions();
        if (count($actions) > 1) {
            $actionBar = new \Widgets\ActionBar();
            $actionBar->setActions($actions);
            $frameResponseObject->addWidget($actionBar);
        }


        $content = \Favorite::getInstance()->loadTemplate("favorite_template.html");
        $content->setVariable("TITLE", "Suche");

        $content->setVariable("SEARCH", "Suchbegriff");
        $content->setVariable("BUTTON_LABEL", "Suchen");

        $content->setVariable("GROUPS", "Gruppenname");
        $content->setVariable("USER_LOGIN", "Loginname");
        $content->setVariable("USER_FULLNAME", "Benutzername");

        //preselect search
        $content->setVariable("PRE_SELECT_USER", "");
        $content->setVariable("PRE_SELECT_FULLNAME", '');
        $content->setVariable("PRE_SELECT_GROUP", '');
        if ($searchType == "searchUserFullname")
            $content->setVariable("PRE_SELECT_FULLNAME", 'checked');
        else if ($searchType == "searchGroup")
            $content->setVariable("PRE_SELECT_GROUP", 'checked');
        else
            $content->setVariable("PRE_SELECT_USER", "checked");

        if ($action != "") {
            $loopCount = 0;
            if ($searchType == "searchUser" || $searchType == "searchUserFullname") {
                $category = "user";
            } else {
                $category = "group";
            }

            foreach ($searchResult as $resultEntry) {
                $content->setVariable("SEARCH_RESULTS", "Suchergebnisse");
                $ignoredUser = false;

                if ($searchType != "searchUserFullname") {
                    $urlId = $helper[$resultEntry];
                } else {
                    $urlId = $resultEntry;
                }

                //remove ignored user
                if ($category == "user") {
                    foreach ($ignoredUserNames as $ignore) {
                        if ($ignore == $resultEntry) {
                            $ignoredUser = true;
                        }
                    }
                }
                if ($category == "group") {
                    foreach ($ignoredGroupNames as $ignore) {
                        if ($ignore == $resultEntry) {
                            $ignoredUser = true;
                        }
                    }
                }

                if (!$ignoredUser) {
                    if ($category == "user") {
                        $resultUser = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $urlId);
                        if ($resultUser instanceof \steam_user) {
                            $content->setCurrentBlock("BLOCK_SEARCH_RESULTS_BUDDY");
                            $content->setVariable("BUDDY_NAME", PATH_URL . "profile/index/" . $resultUser->get_name() . "/");
                            $fullname = $resultUser->get_full_name();
                            $content->setVariable("BUDDY_NAME1", $fullname);
                            $picId = $resultUser->get_attribute("OBJ_ICON")->get_id();
                            $content->setVariable("BUDDY_PIC_LINK", PATH_URL . "download/image/" . $picId . "/50/60/");
                            if ($steamUser->get_id() == $resultUser->get_id()) {
                                $content->setVariable("ALREADY_BUDDY", "Das bist du");
                            } elseif (!($steamUser->is_buddy($resultUser))) {
                                $content->setVariable("ADD_FAVORITE_BUDDY", "Als Favorit hinzufügen");

                                $content->setVariable("FAVORITE_BUDDY_LINK", PATH_URL . "favorite/add/" . $urlId . "/" . $category . "/");
                            } else {
                                $content->setVariable("ALREADY_BUDDY", "Ist bereits ein Favorit");
                            }
                            $content->parse("BLOCK_SEARCH_RESULTS_BUDDY");
                            $loopCount++;
                        }
                    } elseif ($category == "group") {
                        $resultGroup = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $urlId);
                        if ($resultGroup instanceof \steam_group) {

                            $content->setCurrentBlock("BLOCK_SEARCH_RESULTS_GROUP");
                            $content->setVariable("GROUP_NAME", $resultEntry);

                            $groupDesc = $resultGroup->get_attribute("OBJ_DESC");
                            $content->setVariable("GROUP_DESC", $groupDesc);
                            if (!(isset($buddies_group[$resultGroup->get_id()]))) {
                                $content->setVariable("ADD_FAVORITE_GROUP", "Als Favorit hinzufügen");
                                $content->setVariable("FAVORITE_GROUP_LINK", PATH_URL . "favorite/add/" . $urlId . "/" . $category . "/");
                            } else {
                                $content->setVariable("ALREADY_GROUP", "Ist bereits ein Favorit");
                            }
                            $content->parse("BLOCK_SEARCH_RESULTS_GROUP");
                            $loopCount++;
                        }
                    }
                }
            }

            if ($loopCount == 0 || (count($searchResult) == 0)) {
                $content->setVariable("NO_RESULT", "Die Suchanfrage nach '".$searchString."' ergab keinen Treffer.");
            }
        }

        //END Search-Part
        //BEGIN List-Part
        // display actionbar
        /*
          $profileUtils = new \ProfileActionBar($steamUser, $steamUser);
          $profileUtils->setContext("favorite");
          $actions = $profileUtils->getActions();
          if (count($actions) > 1) {
          $actionBar = new \Widgets\ActionBar();
          $actionBar->setActions($actions);
          $frameResponseObject->addWidget($actionBar);
          }
         */

        $content->setVariable("FAVORITE_BUDDYS", "Meine Favoriten (Benutzer)");

        $loopCount = 0;
        foreach ($buddies_user_name as $id => $buddy) {
            $content->setCurrentBlock("BLOCK_BUDDY_LIST");

            $user = \steam_factory::get_object($steam->get_id(), $id);
            $picId = $user->get_attribute("OBJ_ICON")->get_id();
            $content->setVariable("BUDDY_PIC_LINK", PATH_URL . "download/image/" . $picId . "/50/60");
            $content->setVariable("BUDDY_NAME1", $user->get_attribute("USER_FIRSTNAME") . " " . $user->get_attribute("USER_FULLNAME"));
            $content->setVariable("BUDDY_NAME", PATH_URL . "profile/index/" . $buddy . "/");
            $content->setVariable("DELETE_BUDDY", "Favorit Löschen");

            $content->setVariable("DELETE_BUDDY_LINK", PATH_URL . "favorite/delete/" . $id . "/");
            $content->parse("BLOCK_BUDDY_LIST");
            $loopCount += 1;
        }

        if ($loopCount == 0) {
            $content->setVariable("NO_BUDDYS", "Es wurde kein Benutzer der Favoritenliste hinzugefügt");
        }
        
        $content->setVariable("FAVORITE_GROUPS", "Meine Favoriten (Gruppen)");

        $loopCount = 0;
        foreach ($buddies_group_name as $id => $buddy) {
            $group = \steam_factory::get_object($steam->get_id(), $id);
            $groupDesc = $group->get_attribute("OBJ_DESC");
            $content->setCurrentBlock("BLOCK_GROUP_LIST");
            $content->setVariable("GROUP_NAME", $buddy);
            $content->setVariable("GROUP_DESC", $groupDesc);
            $content->setVariable("DELETE_GROUP", "Favorit Löschen");

            $content->setVariable("DELETE_GROUP_LINK", PATH_URL . "favorite/delete/" . $id . "/");

            $content->parse("BLOCK_GROUP_LIST");
            $loopCount += 1;
        }
        if ($loopCount == 0) {
            $content->setVariable("NO_GROUPS", "Es wurde keine Gruppe der Favoritenliste hinzugefügt");
        }

        //END List-Part

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($content->get());
        $frameResponseObject->addWidget($rawHtml);
        return $frameResponseObject;
    }

}

?>

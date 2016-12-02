<?php

class Chronic extends AbstractExtension implements IMenuExtension {

    private $chronicLength = 15;

    public function getName() {
        return "Chronic";
    }

    public function getDesciption() {
        return "Extension for chronic handling.";
    }

    public function getVersion() {
        return "v1.0.1";
    }

    public function getAuthors() {
        $result = array();
        $result[] = new Person("Marcel", "Jakoblew", "mjako@uni-paderborn.de");

        return $result;
    }

    public function getMenuEntries() {
        $chronic = $this->loadChronic();
        $length = count($chronic);
        $result = array();

        // upwards menu
        $result[] = array("name" => "Hierarchie", "menu" => array());
        $menuArray = $result[0]["menu"];
        $menuArray = $this->getUpwardsMenu($menuArray);
        if (count($menuArray) == 0) {
            $menuArray[] = array("name" => "Keine Hierarchie vorhanden.");
        }else{
            $parent = $menuArray[0];
            $parent["name"] = "<svg><use xlink:href='" . PATH_URL . "explorer/asset/icons/up.svg#up'/></svg> Aufwärts";
            $menuArray = array_reverse($menuArray, true);
            array_unshift($menuArray, array("name" => "SEPARATOR"));
            array_unshift($menuArray, $parent);
        }
        $result[0]["menu"] = $menuArray;

        // chronic
        $result[] = array("name" => "Verlauf", "menu" => array($this->getBackEntry()));
        if ($length > 1) {
            $menuArray = $result[1]["menu"];
            $menuArray[] = array("name" => "SEPARATOR");
            $count = 0;
            foreach ($chronic as $chronicItem) {
                $count++;
                if ($count < 2)
                    continue; //skip this and last element
                $entryName = $this->getEntryName($chronicItem);
                if (strlen($entryName) != 0) {
                    $menuArray[] = array("name" => $this->getEntryIconTag($chronicItem) . " " . $entryName, "link" => $this->getEntryPath($chronicItem));
                }
            }
            $result[1]["menu"] = $menuArray;
        }
        return $result;
    }

    //for portlet chronic
    public function getChronic() {
        $chronic = $this->loadChronic();
        $result = array();
        $count = 0;
        $minusCount = -1;
        foreach ($chronic as $chronicItem) {
            $count++;
            if ($count < 2)
                continue; //skip this and last element

            $content = explode(":", $chronicItem);
            $entryType = $content[0];

            if ($entryType == "oid") {
                $objectId = $content[1];
            } else {
                $objectId = $minusCount;
                $minusCount--;
            }
            $entryName = $this->getEntryName($chronicItem, -1);
            if (strlen($entryName) != 0) {
                $result[] = array("id" => $objectId, "name" => $entryName, "image" => $this->getEntryIconTag($chronicItem), "link" => $this->getEntryPath($chronicItem));
            }
        }
        return $result;
    }

    public function setCurrentObject($steamObject) {
        if ($steamObject instanceof steam_object && $steamObject->check_access_read()) {
            $this->updateChronic("oid:" . $steamObject->get_id());
        }
    }

    public function setCurrentCommand($namespace, $command) {
        $this->updateChronic("cmd:" . $namespace . ":" . $command);
    }

    public function setCurrentPath($path, $title) {
        $this->updateChronic("pth:" . $path . ":" . $title);
    }

    public function setCurrentOther($other) {
        if ($other == "profile")
            $this->updateChronic("oth:profile");
        if ($other == "desktop")
            $this->updateChronic("oth:desktop");
        if ($other == "bookmarks")
            $this->updateChronic("oth:bookmarks");
        if ($other == "trashbin")
            $this->updateChronic("oth:trashbin");
        if ($other == "clipboard")
            $this->updateChronic("oth:clipboard");
    }

    //get entry for back button
    private function getBackEntry() {
        return array("name" => "<svg style='width:16px; height:16px; position:relative; top:3px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/left.svg#left'/></svg> Zurück", "onclick" => "history.back();return false;"); //remove for php back method

        $chronic = $this->loadChronic();
        $startBackIndex = 1;

        if (isset($chronic[$startBackIndex])) {
            $steamObject = $this->getEntryObject($chronic[$startBackIndex]);
            if ($steamObject === FALSE || ($steamObject->get_attribute("bid:presentation") === "index")) {
                $startBackIndex++;
            }

            $backEntry = $chronic[$startBackIndex];

            return array("name" => "<svg style='width:16px; height:16px; position:relative; top:3px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/left.svg#left'/></svg> Zurück", "link" => $this->getEntryPath($backEntry));
        }

        return "";
    }

    //get entry for up button
    private function getParentEntry() {
        $chronic = $this->loadChronic();

        if (!isset($chronic[0]))
            return "";

        $currentLocation = $chronic[0];
        $content = explode(":", $currentLocation);
        $entryType = $content[0];
        $currentObjectId = $content[1];

        if ($entryType === "oid") {
            //find object
            try {
                $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectId);
            } catch (\steam_exception $e) {
                //object not found
                return "";
            }

            //find parent
            $environmentObject = $steamObject;
            try {
                $environmentObject = $steamObject->get_environment();
                if ("0" == $environmentObject)
                    throw new \steam_exception;
                if (!($environmentObject instanceof \steam_object))
                    throw new \steam_exception;

                //is Presentation, autoforward case
                if ($environmentObject->get_attribute("bid:presentation") === "index") {
                    $environmentObject = $environmentObject->get_environment();
                }
                if ("0" == $environmentObject)
                    throw new \steam_exception;
                if (!($environmentObject instanceof \steam_object))
                    throw new \steam_exception;
            } catch (\steam_exception $e) {
                //no environment
                return "";
            }

            return array("name" => "nach oben ( <img src=\"" . PATH_URL . "explorer/asset/icons/mimetype/" . deriveIcon($environmentObject) . "\"></img> " . getCleanName($environmentObject, 20) . " )", "link" => $this->getEntryPath("oid:" . $environmentObject->get_id()));
        }

        return "";
    }

    //add a new object to chronic
    private function updateChronic($entry) {
        $chronic = $this->loadChronic();

        //remove entry before adding
        while (array_search($entry, $chronic) !== FALSE) {
            if (is_array($chronic)) {
                $key = array_search($entry, $chronic);
                unset($chronic[$key]);
            }
        }

        //add entry
        $chronic = array_reverse($chronic);
        $chronic[] = $entry;
        $chronic = array_reverse($chronic);

        //dedupe
        $cleandChronic = array();
        $lastElement = "";
        foreach ($chronic as $chronicItem) {
            if ($chronicItem !== $lastElement) {
                $lastElement = $chronicItem;
                $cleandChronic[] = $chronicItem;
            } else {

            }
        }
        $chronic = $cleandChronic;

        //throw tail away
        $counter = 1;
        $cleandChronic = array();
        foreach ($chronic as $chronicItem) {
            $cleandChronic[] = $chronicItem;
            if ($counter == $this->chronicLength)
                break;
            $counter++;
        }
        $chronic = $cleandChronic;
        $this->saveChronic($chronic);
    }

    private function getEntryName($chronicEntry, $length = 30) {
        $content = explode(":", $chronicEntry);
        $entryType = $content[0];
        if ($entryType == "oid") {
            $objectId = $content[1];

            try {
                $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
            } catch (\steam_exception $e) {
                return "(Objekt gelöscht)";
            }

            return getCleanName($steamObject, $length);
        } elseif ($entryType == "cmd") {
            //not yet supported
            return "command";
        } elseif ($entryType == "pth") {
            $name = $content[2];

            return $name;
        } elseif ($entryType == "oth") {
            $type = $content[1];
            if ($type === "profile")
                return "Profil";
            if ($type === "desktop")
                return "Schreibtisch";
            if ($type === "bookmarks")
                return "Lesezeichen";
            if ($type === "trashbin")
                return "Papierkorb";
            if ($type === "clipboard")
                return "Zwischenablage";

            return "Ungültiger oth-Eintrag";
        }
        return "Ungültiger Eintrag";
    }

    private function getEntryPath($chronicEntry) {
        $content = explode(":", $chronicEntry);
        $entryType = $content[0];

        if ($entryType == "oid") {
            $objectId = $content[1];
            try {
                $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
                if ($steamObject instanceof \steam_object) {
                    return \ExtensionMaster::getInstance()->getUrlForObjectId($objectId, "view");
                } else {
                    return "/";
                }
            } catch (Exception $e) {
                return "(Objekt gelöscht)";
            }
        } elseif ($entryType == "cmd") {
            //not yet supported
            return "command";
        } elseif ($entryType == "pth") {
            $path = $content[1];
            return $path;
        } elseif ($entryType == "oth") {
            $type = $content[1];
            if ($type === "profile")
                return "/profile/";
            if ($type === "desktop")
                return "/desktop/";
            if ($type === "bookmarks")
                return "/bookmarks/";
            if ($type === "trashbin")
                return "/trashbin/";
            if ($type === "clipboard")
                return "/clipboard/";

            return "";
        }

        return "Debug:$chronicEntry";
    }

    private function getEntryObject($chronicEntry) {
        $content = explode(":", $chronicEntry);
        $entryType = $content[0];

        if ($entryType == "oid") {
            $objectId = $content[1];
            try {
                $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
                if ($steamObject instanceof \steam_object) {
                    return $steamObject;
                } else {
                    return FALSE;
                }
            } catch (\steam_exception $e) {
                return FALSE;
            }
        }
        return FALSE;
    }

    private function getEntryIconTag($chronicEntry) {
        $defaultIcon = "<svg><use xlink:href='" . PATH_URL . "explorer/asset/icons/mimetype/svg/folder.svg#folder'/></svg>";

        $content = explode(":", $chronicEntry);
        $entryType = $content[0];

        if ($entryType == "oid") {
            $objectId = $content[1];
            try {
                $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
                if ($steamObject instanceof \steam_object) {
                    $icon = deriveIcon($steamObject);
                    $iconSVG = str_replace("png", "svg", $icon);
                    $idSVG = str_replace(".svg", "", $iconSVG);
                    $iconSVG = PATH_URL . "explorer/asset/icons/mimetype/svg/" . $iconSVG;
                    return "<svg><use xlink:href='" . $iconSVG . "#" . $idSVG . "'/></svg>";
          } elseif ($cell == $this->rawName) {
              $creator = $contentItem->get_creator();
              $tipsy = new \Widgets\Tipsy();

                } else {
                    return $defaultIcon;
                }
            } catch (\steam_exception $e) {
                return $defaultIcon;
            }
        } elseif ($entryType == "pth") {
            $objectPath = $content[1];
            try {
                if (strpos($objectPath, "/forum/showTopic/") === 0) {
                    return "<svg><use xlink:href='" . PATH_URL . "explorer/asset/icons/mimetype/svg/forumthread.svg#forumthread'/></svg>";
                } elseif (strpos($objectPath, "/wiki/entry/") === 0) {
                    return "<svg><use xlink:href='" . PATH_URL . "explorer/asset/icons/mimetype/svg/wiki.svg#wiki'/></svg>";
                } else {
                    return $defaultIcon;
                }
            } catch (\steam_exception $e) {
                return $defaultIcon;
            }
        }
        return $defaultIcon;
    }

    //loads the chronic and returns it
    private function loadChronic() {
        //fix error connector missing
        $user = lms_steam::get_current_user();
        

        if ($user instanceof \steam_user) {
            $chronic = $user->get_attribute("USER_CHRONIC");
            if (!is_array($chronic))
                $chronic = array();
            return $this->validateChronic($chronic);
        }
        else {
            session_destroy();
            header("Location : /");
        }
    }

    private function saveChronic($chronic) {
        $user = lms_steam::get_current_user();
        $chronic = $this->validateChronic($chronic);
        $user->set_attribute("USER_CHRONIC", $chronic);
    }

    private function validateChronic($chronic) {
        foreach ($chronic as $chronicKey => $chronicEntry) {
            $content = explode(":", $chronicEntry);
            $entryType = $content[0];
            $target = $content[1];

            $valid = false;
            if ($entryType === "oth")
                $valid = true;
            if ($entryType === "pth")
                $valid = true;
            if ($entryType === "oid")
                $valid = true;

            try {
                $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $target);
            } catch (\steam_exception $e) {
                $valid = false;
            }

            if (!$valid)
                unset($chronic[$chronicKey]);
        }
        return $chronic;
    }

    private function getUpwardsMenu($menuArray) {
        $moreItems = true;
        $bookmarks = false;

        $chronic = $this->loadChronic();
        if (!isset($chronic[0])) $moreItems = false;
        $currentLocation = $chronic[0];
        $content = explode(":", $currentLocation);
        $entryType = $content[0];
        $currentObjectId = $content[1];

        // extensions that set a path in the chronic need to be treated separately
        if ($entryType == "pth") {
            //at the moment forums and wikis use the pth: chronic link to log the access of certain documents/posts
            if (preg_match("/\/forum\/showTopic\/([0-9]+)\/([0-9]+)/i", $content[1], $output)) { // forum
                $parentObjectId = $output[1];
                $currentObjectId = $output[2];
                $firstCycle = true; // to prevent a infinity loop in the recursion
            }

            if (preg_match("/\/wiki\/entry\/([0-9]+)\//i", $content[1], $output)) { // wiki
                $currentObjectId = $output[1];
            }

            if (preg_match("/\/bookmarks\/index\/([0-9]+)\//i", $content[1], $output)) { // bookmarks
                $currentObjectId = $output[1];
                $bookmarks = true;
            }
        }

        if ($entryType === "oid" || $entryType === "pth") {
            //find object
            try {
                $steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentObjectId);

                if (!$steamObject instanceof \steam_object) {
                    //object not found
                    $moreItems = false;
                }
            } catch (\steam_exception $e) {
                //object not found
                $moreItems = false;
            }

            //find parent
            while ($moreItems) {

                try {
                    $environmentObject = $steamObject->get_environment();

                    if ("0" == $environmentObject)
                        throw new \steam_exception;
                    if (!($environmentObject instanceof \steam_object))
                        throw new \steam_exception;

                    //is Presentation, autoforward case
                    if ($environmentObject->get_attribute("bid:presentation") === "index") {
                        $environmentObject = $environmentObject->get_environment();
                    }
                    if ("0" == $environmentObject)
                        throw new \steam_exception;
                    if (!($environmentObject instanceof \steam_object))
                        throw new \steam_exception;

                    $icon = deriveIcon($environmentObject);
                    $iconSVG = str_replace("png", "svg", $icon);
                    $idSVG = str_replace(".svg", "", $iconSVG);
                    $iconSVG = PATH_URL . "explorer/asset/icons/mimetype/svg/" . $iconSVG;
                    $objectArray = array("name" => "<svg><use xlink:href='" . $iconSVG . "#" . $idSVG . "'/></svg> " . getCleanName($environmentObject, 20));

                    if ($environmentObject->check_access_read()) {
                      if($bookmarks){
                        $objectArray["link"] = "/bookmarks/index/" . $environmentObject->get_id() . "/";
                      }
                      else{
                        $objectArray["link"] = $this->getEntryPath("oid:" . $environmentObject->get_id());
                      }
                    } else {
                        $moreItems = false;
                        break;
                    }
                    $menuArray[] = $objectArray;
                    $steamObject = $environmentObject;
                } catch (\steam_exception $e) {
                    //no environment
                    $moreItems = false;
                }
            }
        }
        return $menuArray;
    }

}

<?php

namespace PortletTermplan\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IIdCommand {

    private $params;
    private $id;
    private $content;
    private $rawHtmlWidget;

    public function validateData(\IRequestObject $requestObject) {

        //robustness for missing ids and objects
        try {
            $objectId = $requestObject->getId();
            $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        } catch (\Exception $e) {
            \ExtensionMaster::getInstance()->send404Error();
        }

        if (!$object instanceof \steam_object) {
            \ExtensionMaster::getInstance()->send404Error();
        }

        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $objectId = $requestObject->getId();
        $portlet = $portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $portletName = $portlet->get_attribute(OBJ_DESC);


        //reference handling
        $params = $requestObject->getParams();
        if (isset($params["referenced"]) && $params["referenced"] == true) {
            if (!$portlet->check_access_read()) {
                $this->rawHtmlWidget = new \Widgets\RawHtml();
                $this->rawHtmlWidget->setHtml("");
                return null;
            }

            $portletIsReference = true;
            $referenceId = $params["referenceId"];
        } else {
            $portletIsReference = false;
        }

        $this->getExtension()->addCSS();
        $this->getExtension()->addJS();

        $htmlBody = "";

        include_once(PATH_BASE . "core/lib/bid/slashes.php");

        //get content of portlet
        $content = $portlet->get_attribute("bid:portlet:content");
        if (is_array($content) && count($content) > 0) {
            array_walk($content, "_stripslashes");
        } else {
            $content = array();
        }

        //get singleton and portlet path
        $portletInstance = \PortletTermplan::getInstance();
        $portletPath = $portletInstance->getExtensionPath();

        //create template
        $portletFileName = $portletPath . "/ui/html/index.html";
        $tmpl = new \HTML_TEMPLATE_IT();
        $tmpl->loadTemplateFile($portletFileName);
        $tmpl->setVariable("PORTLET_ID", $portlet->get_id());

        if (sizeof($content) > 0) {
            //popupmenu
            if (!$portletIsReference && $portlet->check_access_write(\lms_steam::get_current_user())) {
                $popupmenu = new \Widgets\PopupMenu();
                $popupmenu->setData($portlet);
                $popupmenu->setNamespace("PortletTermplan");
                $popupmenu->setElementId("portal-overlay");
                $tmpl->setVariable("POPUPMENU", $popupmenu->getHtml());
            }

            if ($portletIsReference && $portlet->check_access_write(\lms_steam::get_current_user())) {
                $popupmenu = new \Widgets\PopupMenu();
                $popupmenu->setData($portlet);
                $popupmenu->setNamespace("Portal");
                $popupmenu->setElementId("portal-overlay");
                $popupmenu->setParams(array(array("key" => "sourceObjectId", "value" => $portlet->get_id()),
                    array("key" => "linkObjectId", "value" => $referenceId)
                ));
                $popupmenu->setCommand("PortletGetPopupMenuReference");
                $tmpl->setVariable("POPUPMENU", $popupmenu->getHtml());
            }

            $tmpl->setVariable("POLL_NAME", $portletName);

            //if the title is empty the headline will not be displayed (only in edit mode)
            if (trim($portletName == "")) {
                $tmpl->setVariable("HEADLINE_CLASS", "headline editbutton");
            } else {
                $tmpl->setVariable("HEADLINE_CLASS", "headline");
            }

            $options = $content["options"];
            $options_votecount = $content["options_votecount"];
            $startDate = $content["start_date"];
            $endDate = $content["end_date"];

            if ($options[0] == "" && $options[1] == "" && $options[2] == "" && $options[3] == "" && $options[4] == "" && $options[5] == "") {
                //NO poll options
                $tmpl->setCurrentBlock("BLOCK_NO_MESSAGE");
                $tmpl->setVariable("NO_MESSAGE_INFO", "Kein Terminplaner vorhanden.");
                $tmpl->parse("BLOCK_NO_MESSAGE");
                $tmpl->setVariable("HIDDEN", "hidden");
            } else if (time() < mktime(0, 0, 0, $startDate["month"], $startDate["day"], $startDate["year"])) {
                //poll before start
                $tmpl->setCurrentBlock("BLOCK_NO_MESSAGE");
                $tmpl->setVariable("NO_MESSAGE_INFO", "Terminplaner wurde noch nicht gestartet.");
                $tmpl->parse("BLOCK_NO_MESSAGE");
                $tmpl->setVariable("HIDDEN", "hidden");
            } else {

                if (time() > mktime(0, 0, 0, $startDate["month"], $startDate["day"], $startDate["year"]) && time() < mktime(24, 0, 0, $endDate["month"], $endDate["day"], $endDate["year"])) {
                    $pollActive = true;
                } else {
                    $pollActive = false;
                }

                $max_votecount = 1;
                foreach ($options_votecount as $option_votecount) {
                    if ($option_votecount > $max_votecount)
                        $max_votecount = $option_votecount;
                }

                //reference icon
                if ($portletIsReference) {
                    $referIcon = \Explorer::getInstance()->getAssetUrl() . "icons/menu/svg/refer.svg";
                    $titleTag = "title='" . \Portal::getInstance()->getReferenceTooltip() . "'";
                    $envId = $portlet->get_environment()->get_environment()->get_id();
                    $envUrl = PATH_URL . "portal/index/" . $envId;
                    $tmpl->setVariable("REFERENCE_ICON", "<a $titleTag href='{$envUrl}' target='_blank'><svg><use xlink:href='{$referIcon}#refer'></svg></a>");
                }

                $tmpl->setVariable("POLL_TOPIC", $content["poll_topic"]);

                //advanced result
                //decode content mapping
                $encodedVoteUserMapping = $portlet->get_attribute("termChoices");
                if ($encodedVoteUserMapping == "0") {
                    $mapping = array();
                } else {
                    $mapping = json_decode($encodedVoteUserMapping, true);
                }

                $currentUser = \lms_steam::get_current_user();
                $currentUserName = $currentUser->get_full_name();
                $currentUserLogin = $currentUser->get_name(); //fehler
                //create table
                $userTimeTable = "<br/><table border='0' style='margin:auto;border-color:#C0C0C0;width:98%'>";

                //headline
                $userTimeTable.= "<tr>";
                $userTimeTable.= "<th style='background-color:#E0E0E0;'>Name</th>";
                $optionsCount = 0;

                foreach ($options as $option) {
                    if ($option != "") {
                        //show
                        $optionsCount++;
                        $optionText = $this->termplanCutItemLenght($option);
                        $userTimeTable.="<th style='background-color:#E0E0E0;'>$optionText</th>";
                    }
                }

                $userTimeTable.= "</tr>";

                $userVoteArray = array();
                foreach ($mapping as $username => $encodedTermChoices) {
                    @$voteUserArray = explode("#", $voteXuser); //TODO
                    $votes = explode(":", substr($encodedTermChoices, 12));
                    $user = $username;

                    foreach ($votes as $vote) {
                        if (isset($userVoteArray[$user][$vote]) && $userVoteArray[$user][$vote] == "X") { //hier den umschalter
                            $userVoteArray[$user][$vote] = "N";
                        } else {
                            $userVoteArray[$user][$vote] = "X";
                        }
                    }
                }

                //initialize votecount
                $voteCount = array();
                $voteCount[0] = 0;
                $voteCount[1] = 0;
                $voteCount[2] = 0;
                $voteCount[3] = 0;
                $voteCount[4] = 0;
                $voteCount[5] = 0;

                if (!("guest" == \lms_steam::get_current_user()->get_name())) { //its not allowed for guest to vote
                    //create first line for current user
                    $userTimeTable.= "<tr>";
                    $userTimeTable.= "<td style='font-weight: bold;'>$currentUserName</td>";

                    if (isset($userVoteArray[$currentUserLogin])) {
                        $votingForCurrentUser = $userVoteArray[$currentUserLogin];
                    } else {
                        $votingForCurrentUser = array();
                    }

                    $backGroundGreen = "#99EE99";

                    for ($i = 0; $i < $optionsCount; $i++) {
                        //$userTimeTable.= "<td>";
                        $portletId = $portlet->get_id();
                        if (isset($votingForCurrentUser[$i]) && $votingForCurrentUser[$i] == "X") {
                            if (!$pollActive)
                                $userTimeTable.= "<td class='termplanopenvote dateaccepted'>";
                            if ($pollActive)
                                $userTimeTable.= "<td class='termplanopenvote dateaccepted'>";
                            $voteCommand = 'sendRequest("VoteTerm",	{"portletObjectId": "' . $objectId . '", "termId": "' . $i . '"}, "", "popup", "","", "PortletTermplan");return false;';
                            if ($pollActive)
                                $userTimeTable.="<input onclick='$voteCommand;'  type='checkbox' name='termitem' value='$i' checked='checked'>";

                            if (!$pollActive)
                                $userTimeTable.="";
                            $voteCount[$i] ++;
                            $userTimeTable.= "</td>";
                        }else {
                            $userTimeTable.= "<td class='termplanopenvote datedeclined'>";
                            $voteCommand = 'sendRequest("VoteTerm",	{"portletObjectId": "' . $objectId . '", "termId": "' . $i . '"}, "", "popup", "","", "PortletTermplan");return false;';
                            if ($pollActive)
                                $userTimeTable.="<input onclick='$voteCommand;' type='checkbox' name='termitem' value='$i'>";
                            if (!$pollActive)
                                $userTimeTable.="";
                            $userTimeTable.= "</td>";
                        }
                    }
                    $userTimeTable.= "</tr>";
                }

                //sort the users
                //$userVoteArray
                $sortedNames = array();
                foreach ($userVoteArray as $login => $userElement) {
                    $userObject = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $login);
                    $userRealName = $userObject->get_full_name();
                    $sortedNames[$login] = $userRealName;
                }

                asort($sortedNames, SORT_STRING);

                //resorting the other array
                foreach ($sortedNames as $userName => $realName) {
                    $sortedNames[$userName] = $userVoteArray[$userName];
                }
                $userVoteArraySorted = $sortedNames;

                //table for other users
                foreach ($userVoteArraySorted as $user => $userElement) {
                    if ($user == $currentUserLogin)
                        continue;
                    //create row
                    $userTimeTable.= "<tr>";

                    $userObject = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $user);
                    $realName = $userObject->get_full_name();

                    //create other users
                    if ($currentUserName == $realName) {
                        //DO NOTHING
                    } else {
                        $userTimeTable.= "<td>$realName</td>";
                    }


                    for ($i = 0; $i < $optionsCount; $i++) {
                        //create field
                        if (isset($userElement[$i]) && $userElement[$i] == "X") {
                            if ($currentUserName == $realName) {
                                $userTimeTable.="<td></td>";
                                //DO NOTHING
                            } else {
                                $userTimeTable.="<td class='termplanclosedvote dateaccepted'><input type='checkbox' checked disabled> </td>";
                            }
                            $voteCount[$i] ++;
                        } else {
                            $userTimeTable.="<td class='termplanclosedvote datedeclined'></td>";
                            if ($currentUserName == $realName) {
                                //DO NOTHING
                            }
                        }
                    }
                    $userTimeTable.= "</tr>";
                }

                //count and show results
                $userTimeTable.= "<tr>";
                $userTimeTable.= "<td>Summe</td>";
                $portletId = $portlet->get_id();
                for ($i = 0; $i < $optionsCount; $i++) {
                    $userTimeTable.= "<td class='termplansum' id='" . $portletId . "TermSum$i'>$voteCount[$i]</td>";
                }
                $userTimeTable.= "</tr>";

                $userTimeTable.= "</table>";
                //table created

                $tmpl->setVariable("USER_VOTE_TABLE", $userTimeTable);
            }
            $htmlBody = $tmpl->get();

            //widgets
            $outputWidget = new \Widgets\RawHtml();
            $outputWidget->setHtml($htmlBody);
            $this->rawHtmlWidget = $outputWidget;
        }
    }

    public function idResponse(\IdResponseObject $idResponseObject) {
        $idResponseObject->addWidget($this->rawHtmlWidget);
        return $idResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $frameResponseObject->setTitle("Portal");
        $frameResponseObject->addWidget($this->rawHtmlWidget);
        return $frameResponseObject;
    }

    private function termplanCutItemLenght($optionText) {
        $returnString = "";
        $textExploded = explode(" ", $optionText);

        $firstLoop = true;
        foreach ($textExploded as $word) {
            $wordCutted = "";
            for ($i = 0; $i < strlen($word); $i++) {
                $wordCutted.=$word[$i];
                if ($i > 1 && $i % 9 == 0)
                    $wordCutted.=" ";
            }
            if ($firstLoop) {
                $returnString.="" . $wordCutted;
            } else {
                $returnString.=" " . $wordCutted;
            }
            $firstLoop = false;
        }
        return $returnString;
    }

}

?>

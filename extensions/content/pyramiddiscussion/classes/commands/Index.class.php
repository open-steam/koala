<?php
namespace Pyramiddiscussion\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $pyramidRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["id"]);
        if ($this->params["action"] == "phase") {
            // admin: change current phase
            $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_ACTCOL", $this->params["newphase"]);
            if ($pyramidRoom->get_attribute("PYRAMIDDISCUSSION_USEDEADLINES") == "yes") {
                $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_OVERRIDE_DEADLINES", 1);
            }
        } else if ($this->params["action"] == "deadlines") {
            // admin: reactivate overridden deadlines
            $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_OVERRIDE_DEADLINES", 0);
        }
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        $userID = $user->get_id();
        $pyramidRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        if ($pyramidRoom->get_attribute("PYRAMIDDISCUSSION_INITIALIZED") == "0") {
            $rawWidget = new \Widgets\RawHtml();
            if ($userID === $pyramidRoom->get_creator()->get_id()) {
                $params = "{ pyramid : " . $pyramidRoom->get_id() . " }";
                $html = "<br><center>
                    Bevor Sie die Pyramidendiskussion benutzen können, müssen Sie diese initialisieren.<br>
                    Die Dauer dieses Vorgangs ist abhängig von der Größe der ausgewählten Gruppen.<br>
                    In den meisten Fällen dauert er weniger als eine Minute, bei Gruppen<br>
                    mit vielen Mitgliedern kann die Initialisierung allerdings auch wenige Minuten dauern.
                    <br><br><a href=\"javascript:sendRequest('InitializePyramid', " . $params . ", '" . $pyramidRoom->get_id() . "', 'popup', '', '', 'Pyramiddiscussion');\">Klicken Sie jetzt hier um die Pyramidendiskussion zu initialisieren.</a></center>";
            } else {
                $html = "<center>Bevor die Pyramidendiskussion benutzt werden kann, muss diese zunächst vom Ersteller initialisiert werden.</center>";
            }
            $rawWidget = new \Widgets\RawHtml();
            $rawWidget->setHtml($html);
            $frameResponseObject->addWidget($rawWidget);
            $frameResponseObject->setHeadline("Pyramidendiskussion: " . $pyramidRoom->get_attribute("OBJ_DESC"));
            return $frameResponseObject;
        } else if ($pyramidRoom->get_attribute("PYRAMIDDISCUSSION_INITIALIZED") == "1") {
            $html = "<center>Die Pyramidendiskussion wird gerade initialisiert. Bitte laden Sie die Seite in wenigen Minuten noch einmal neu.<br><br>Wenn die Initialisierung auch nach einigen Minuten nicht abgeschlossen wurde, löschen Sie die Pyramidendiskussion und erstellen Sie sie erneut.</center>";
            $rawWidget = new \Widgets\RawHtml();
            $rawWidget->setHtml($html);
            $frameResponseObject->addWidget($rawWidget);
            $frameResponseObject->setHeadline("Pyramidendiskussion: " . $pyramidRoom->get_attribute("OBJ_DESC"));
            return $frameResponseObject;
        } else {
            $pyramiddiscussionExtension = \Pyramiddiscussion::getInstance();
            $pyramiddiscussionExtension->addCSS();
            $pyramiddiscussionExtension->addJS();
            $content = $pyramiddiscussionExtension->loadTemplate("pyramiddiscussion_index.template.html");

            $group = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_PRIVGROUP");
            $startElements = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAX");
            $maxcol = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAXCOL");
            $deadlines = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_DEADLINES");
            $user_management = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_PARTICIPANT_MANAGEMENT");
            $deadlines_used = false;
            $currentUserGroup = 0;

            $objtype = $pyramidRoom->get_attribute("OBJ_TYPE");
            if (!(strStartsWith($objtype, "container_pyramiddiscussion"))) {
                $rawWidget = new \Widgets\RawHtml();
                $rawWidget->setHtml("Objekt " . $this->id . " ist keine Pyramidendiskussion.");
                $frameResponseObject->addWidget($rawWidget);
                return $frameResponseObject;
            }

            // chronic
            \ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentObject($pyramidRoom);

            // if one or more deadlines passed by since the last visit on this page, change the phase
            $phase = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_ACTCOL");
            if ($phase != 0 && $phase <= $maxcol && $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_USEDEADLINES") == "yes" && $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_OVERRIDE_DEADLINES") == 0) {
                $currentDeadline = $deadlines[$phase];
                while (true) {
                    if ($currentDeadline > time())
                        break;
                    else {
                        $phase++;
                        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_ACTCOL", $phase);
                        if ($phase == $maxcol + 1)
                            break;
                        $currentDeadline = $deadlines[$phase];
                    }
                }
            }

            $adminoptions_change = -1;
            if (isset($this->params[1])) {
                $adminoptions_change = $this->params[1];
            }
            if ($group->is_admin($user)) {
                // if rights on the group are missing insert them
                if ($group->query_sanction($group) == 0 && $group->check_access(SANCTION_SANCTION, $user)) {
                    $group->set_sanction_all($group);
                }
                
                // synchronize groups
                $admingroup = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_ADMINGROUP");
                $basegroup = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_BASEGROUP");
                foreach ($basegroup->get_members() as $baseMember) {
                    if ($baseMember instanceof \steam_user) {
                        if (!$group->is_member($baseMember)) {
                            $group->add_member($baseMember);
                        }
                    }
                }
                if ($admingroup instanceof \steam_group) {
                    foreach ($admingroup->get_members() as $adminMember) {
                        if ($adminMember instanceof \steam_user) {
                            if (!$group->is_member($adminMember)) {
                                $group->add_member($adminMember);
                                $group->set_admin($adminMember);
                            }
                        }
                    }
                }

                // get action bar settings
                $adminconfig = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_ADMINCONFIG");
                if (array_key_exists($userID, $adminconfig)) {
                    $options = $adminconfig[$userID];
                    // change show/hide settings
                    if ($adminoptions_change != -1) {
                        if ($adminoptions_change == 1) {
                            $options["show_adminoptions"] = "true";
                        } else {
                            $options["show_adminoptions"] = "false";
                        }
                        $adminconfig[$userID] = $options;
                        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_ADMINCONFIG", $adminconfig);
                    }
                } else {
                    $adminconfig[$userID] = array();
                    $options = $adminconfig[$userID];
                    $options["show_adminoptions"] = "true";
                    $adminconfig[$userID] = $options;
                    $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_ADMINCONFIG", $adminconfig);
                }
                if ($options["show_adminoptions"] == "true") {
                    $actionbar = new \Widgets\Actionbar();
                    $actions = array(
                        array("name" => "Konfiguration", "link" => $pyramiddiscussionExtension->getExtensionUrl() . "configuration/" . $this->id),
                        array("name" => "Teilnehmerverwaltung", "link" => $pyramiddiscussionExtension->getExtensionUrl() . "users/" . $this->id),
                        PLATFORM_ID !== "bid" ? array("name" => "Rundmail erstellen", "link" => $pyramiddiscussionExtension->getExtensionUrl() . "mail/" . $this->id) : "",
                        array("name" => "Adminmodus ausschalten", "link" => $pyramiddiscussionExtension->getExtensionUrl() . "Index/" . $this->id . "/0"));
                    $actionbar->setActions($actions);
                    $frameResponseObject->addWidget($actionbar);
                } else {
                    $actionbar = new \Widgets\Actionbar();
                    $actions = array(
                        array("name" => "Adminmodus einschalten", "link" => $pyramiddiscussionExtension->getExtensionUrl() . "Index/" . $this->id . "/1"));
                    $actionbar->setActions($actions);
                    $frameResponseObject->addWidget($actionbar);
                }
            }
            // display the general information block
            $content->setCurrentBlock("BEGIN BLOCK_PYRAMID_INFORMATION");
            $content->setVariable("JS_URL", $pyramiddiscussionExtension->getAssetUrl());
            $content->setVariable("GENERAL_INFORMATION", "Allgemeine Informationen");
            $content->setVariable("PHASE_LABEL", "Aktueller Status:");
            // if user is an admin and adminoptions are shown, display a select box to change the current phase
            if (isset($options["show_adminoptions"]) && $options["show_adminoptions"] == "true") {
                for ($count = 0; $count <= $maxcol + 2; $count++) {
                    $content->setCurrentBlock("BLOCK_PHASE_OPTION");
                    $content->setVariable("PHASE_ID", $count);
                    if ($count == 0) {
                        $content->setVariable("PHASE_VALUE", "Gruppeneinteilungsphase");
                    } else if ($count <= $maxcol) {
                        $content->setVariable("PHASE_VALUE", $count . ". Diskussionsphase");
                    } else if ($count == $maxcol + 1) {
                        $content->setVariable("PHASE_VALUE", "Endphase");
                    } else {
                        $content->setVariable("PHASE_VALUE", "Pyramide einfrieren");
                    }
                    if ($count == $phase) {
                        $content->setVariable("PHASE_SELECTED", "selected");
                    }
                    $content->setVariable("CHANGE_PHASE", "Ändern");
                    $params = "{ id : " . $pyramidRoom->get_id() . ", action : 'phase', newphase : document.getElementById('phase').value }";
                    $content->setVariable("CHANGE_PHASE_ACTION", "sendRequest('Index', " . $params . ", '" . $pyramidRoom->get_id() . "', 'reload');");
                    $content->parse("BLOCK_PHASE_OPTION");
                }
            } else {
                $content->setCurrentBlock("BLOCK_PYRAMID_PHASE_NOADMIN");
                if ($phase == 0) {
                    $content->setVariable("PHASE_VALUE", "Gruppeneinteilungsphase");
                } else if ($phase <= $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAXCOL")) {
                    $content->setVariable("PHASE_VALUE", $phase . ". Diskussionsphase");
                } else if ($phase == $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAXCOL") + 1) {
                    $content->setVariable("PHASE_VALUE", "Endphase");
                } else {
                    $content->setVariable("PHASE_VALUE", "Pyramide eingefroren");
                }
                $content->parse("BLOCK_PYRAMID_PHASE_NOADMIN");
            }
            $content->setVariable("DEADLINE_LABEL", "Deadline:");
            // display current deadline if deadlines are used and override is off
            if ($pyramidRoom->get_attribute("PYRAMIDDISCUSSION_USEDEADLINES") == "yes" && $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_OVERRIDE_DEADLINES") == 0 && $phase != 0 && $phase <= $maxcol) {
                $content->setVariable("DEADLINE_VALUE", date("d.m.Y H:i", (int) $deadlines[$phase]));
                $content->setVariable("DISPLAY_OVERRIDE", "none");
                $deadlines_used = true;
                // if user is admin, adminoptions are shown, deadlines are used but override is on, display a dialog to stop the override
            } else if ($pyramidRoom->get_attribute("PYRAMIDDISCUSSION_USEDEADLINES") == "yes" && $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_OVERRIDE_DEADLINES") == 1 && isset($options["show_adminoptions"]) && $options["show_adminoptions"] == "true") {
                $content->setVariable("DEADLINE_VALUE", "Aktuelle Phase wurde trotz der Verwendung von Deadlines manuell gesetzt. Die Deadlines werden im Moment nicht berücksichtigt.");
                $content->setVariable("DEADLINE_OVERRIDE_LABEL", "Deadlines aktivieren");
                $params = "{ id : " . $pyramidRoom->get_id() . ", action : 'deadlines' }";
                $content->setVariable("DEADLINE_OVERRIDE_ACTION", "sendRequest('Index', " . $params . ", '" . $pyramidRoom->get_id() . "', 'reload');");
            } else {
                $content->setVariable("DEADLINE_VALUE", "keine");
                $content->setVariable("DISPLAY_OVERRIDE", "none");
            }
            $content->setVariable("INFO_LABEL", "Infotext:");
            if ($pyramidRoom->get_attribute("PYRAMIDDISCUSSION_DESC") != "0") {
                $content->setVariable("INFO_VALUE", nl2br($pyramidRoom->get_attribute("PYRAMIDDISCUSSION_DESC")));
            }
            $content->parse("BEGIN BLOCK_PYRAMID_INFORMATION");

            // display pyramid
            $content->setCurrentBlock("BLOCK_PYRAMID");
            // create array to store the users for all positions
            $users = array();
            for ($count = 1; $count <= $maxcol; $count++) {
                for ($count2 = 1; $count2 <= $startElements / pow(2, $count - 1); $count2++) {
                    $users[$count . $count2] = array();
                }
            }
            // for every phase
            $maxuser = 1;
            for ($count = 1; $count <= $maxcol; $count++) {
                // if deadlines are used, display deadline of every phase on the top of the phase
                if ($pyramidRoom->get_attribute("PYRAMIDDISCUSSION_USEDEADLINES") == "yes" && $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_OVERRIDE_DEADLINES") == 0 && $phase <= $maxcol) {
                    $content->setCurrentBlock("BLOCK_PYRAMID_DEADLINE");
                    $content->setVariable("DEADLINE_ID", "deadline" . $count);
                    $content->setVariable("DEADLINE_DATE", date("d.m.Y H:i", (int) $deadlines[$count]));
                    $content->parse("BLOCK_PYRAMID_DEADLINE");
                }
                // create user array
                for ($count2 = 1; $count2 <= $startElements / pow(2, $count - 1); $count2++) {
                    $position = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $pyramidRoom->get_path() . "/Position_" . $count . "_" . $count2);
                    $positionGroup = $position->get_attribute("PYRAMIDDISCUSSION_RELGROUP");
                    $positionMembers = $positionGroup->get_members();
                    // add the users from the positions in the first phase to their corresponding arrays
                    if ($count == 1) {
                        if ($positionMembers instanceof \steam_user) {
                            $users[$count . $count2] = $positionMembers->get_id();
                            if ($positionMembers->get_id() == $userID) {
                                $currentUserGroup = $positionGroup->get_id();
                            }
                        } else {
                            foreach ($positionMembers as $positionMember) {
                                array_push($users[$count . $count2], $positionMember->get_id());
                                if ($positionMember->get_id() == $userID) {
                                    $currentUserGroup = $positionGroup->get_id();
                                }
                            }
                        }
                        if (count($users[$count . $count2]) > $maxuser) {
                            $maxuser = count($users[$count . $count2]);
                        }
                        // add users to the arrays of the positions for all other phases
                    } else {
                        $users[$count . $count2] = array_merge($users[$count - 1 . ($count2 * 2 - 1)], $users[$count - 1 . ($count2 * 2)]);
                    }
                }
                // for every position in a phase
                for ($count2 = 1; $count2 <= $startElements / pow(2, $count - 1); $count2++) {
                    $position = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $pyramidRoom->get_path() . "/Position_" . $count . "_" . $count2);
                    $positionGroup = $position->get_attribute("PYRAMIDDISCUSSION_RELGROUP");
                    $positionMembers = $positionGroup->get_members();
                    // get the names from all users of the current position (that still take part in the discussion)
                    $participants = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_PARTICIPANT_MANAGEMENT");
                    foreach ($users[$count . $count2] as $currentUserID) {
                        if (!isset($participants[$currentUserID])) {
                            $participants[$currentUserID] = 0;
                            $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_PARTICIPANT_MANAGEMENT", $participants);
                        }
                    }

                    // get position read states
                    $read_states = $position->get_attribute("PYRAMIDDISCUSSION_POS_READ_STATES");
                    if (!is_array($read_states)) {
                        $read_states = array();
                    }

                    // determine if there are unread comments for the current position
                    $comments_read = 1;
                    $comments = $position->get_annotations();
                    foreach ($comments as $comment) {
                        $comment_read_states = $comment->get_attribute("PYRAMIDDISCUSSION_COMMENT_READ_STATES");
                        if (!is_array($comment_read_states)) {
                            $comment_read_states = array();
                        }
                        if (!array_key_exists($userID, $comment_read_states) || $comment_read_states[$userID] != "1") {
                            $comments_read = 0;
                            break;
                        }
                    }
                    $read = false;
                    $content->setCurrentBlock("BLOCK_PYRAMID_POSITION");
                    $state = "phase" . $count . " ";
                    if ($count <= $phase && count($users[$count . $count2]) > 0) {
                        $state = $state . "active ";
                    } else if ((!($phase == 0 && $count == 1)) || ($phase != 0 && (count($users[$count . $count2])) == 0)) {
                        $state = $state . "inactive ";
                        $content->setVariable("POSITION_ACTION_HIDDEN", "hidden");
                        $read = true;
                    } else {
                        $state = $state . "active ";
                    }
                    if (in_array($userID, $users[$count . $count2])) {
                        $state = $state . "my ";
                        if ($phase != 0) {
                            if ($phase == $count && (!isset($user_management[$currentUserID]) || $user_management[$currentUserID] == 0 || $user_management[$currentUserID] >= $count)) {
                                // current discussion phase
                                $content->setVariable("POSITION_ACTION_LABEL", "Position lesen<br>und bearbeiten");
                                $content->setVariable("COMMENTS_URL", 'href="' . $pyramiddiscussionExtension->getExtensionUrl() . 'ViewPosition/' . $pyramidRoom->get_id() . '/' . $position->get_id() . '#comments"');
                            } else if ($count < $phase) {
                                // previous discussion phase
                                $content->setVariable("POSITION_ACTION_LABEL", "Position lesen");
                                $content->setVariable("COMMENTS_URL", 'href="' . $pyramiddiscussionExtension->getExtensionUrl() . 'ViewPosition/' . $pyramidRoom->get_id() . '/' . $position->get_id() . '#comments"');
                            }
                            $content->setVariable("POSITION_ACTION_URL", $pyramiddiscussionExtension->getExtensionUrl() . "ViewPosition/" . $pyramidRoom->get_id() . "/" . $position->get_id());

                            if (array_key_exists($userID, $read_states) && $read_states[$userID] == "1" || $position->get_content() == "0" || $position->get_content() == "") {
                                $read = true;
                            }
                        } else {
                            // group choosing phase, my position
                            $content->setVariable("POSITION_ACTION_LABEL", "Position<br>verlassen");
                            $params = "{ id : " . $position->get_id() . ", pyramid : " . $pyramidRoom->get_id() . ", action : 'join', formergroup : " . $currentUserGroup . ", newgroup : " . $positionGroup->get_id() . " }";
                            $content->setVariable("POSITION_ACTION_URL", "javascript:sendRequest('EditPosition', " . $params . ", '" . $position->get_id() . "', 'reload');");
                            $read = true;
                        }
                    } else {
                        if ($phase != 0) {
                            if ($count < $phase) {
                                // previous discussion phase, other positions (reading allowed)
                                $content->setVariable("POSITION_ACTION_LABEL", "Position lesen");
                                $content->setVariable("POSITION_ACTION_URL", $pyramiddiscussionExtension->getExtensionUrl() . "ViewPosition/" . $pyramidRoom->get_id() . "/" . $position->get_id());

                                if (array_key_exists($userID, $read_states) && $read_states[$userID] == "1" || $position->get_content() == "0" || $position->get_content() == "") {
                                    $read = true;
                                }
                                $content->setVariable("COMMENTS_URL", 'href="' . $pyramiddiscussionExtension->getExtensionUrl() . 'ViewPosition/' . $pyramidRoom->get_id() . '/' . $position->get_id() . '#comments"');
                            } else if ($phase == $count) {
                                // current discussion phase, other positions (reading not allowed)
                                $content->setVariable("POSITION_ACTION_HIDDEN", "hidden");
                                $read = true;
                            }
                        } else {
                            // group choosing phase, other positions
                            $content->setVariable("POSITION_ACTION_LABEL", "Position<br>beitreten");
                            $params = "{ id : " . $position->get_id() . ", pyramid : " . $pyramidRoom->get_id() . ", action : 'join', formergroup : " . $currentUserGroup . ", newgroup : " . $positionGroup->get_id() . " }";
                            $content->setVariable("POSITION_ACTION_URL", "javascript:sendRequest('EditPosition', " . $params . ", '" . $position->get_id() . "', 'reload');");

                            $read = true;
                        }
                    }
                    if ($read) {
                        $state = $state . "read";
                        $content->setVariable("READSTATUS_ICON", "read_position");
                        $content->setVariable("READSTATUS_TITLE", "Position gelesen");
                    } else {
                        $state = $state . "unread";
                        $content->setVariable("READSTATUS_ICON", "not_read_position");
                        $content->setVariable("READSTATUS_TITLE", "Position ungelesen");
                    }

                    if ($phase > 0 && $phase != ($maxcol + 2) && (isset($options["show_adminoptions"]) && $options["show_adminoptions"] == "true")) {
                        $content->setVariable("POSITION_ACTION_URL", $pyramiddiscussionExtension->getExtensionUrl() . "ViewPosition/" . $pyramidRoom->get_id() . "/" . $position->get_id());
                        $content->setVariable("POSITION_ACTION_HIDDEN", "");
                        $content->setVariable("POSITION_ACTION_LABEL", "Position lesen<br>und bearbeiten");
                        $content->setVariable("COMMENTS_URL", 'href="' . $pyramiddiscussionExtension->getExtensionUrl() . 'ViewPosition/' . $pyramidRoom->get_id() . '/' . $position->get_id() . '#comments"');
                    }

                    $content->setVariable("POSITION_ID", "position" . $count . $count2);
                    $content->setVariable("POSITION_STATE", $state);
                    $content->setVariable("POSITION_LABEL", "Position " . $count . "-" . $count2);
                    $content->setVariable("ASSETURL", $pyramiddiscussionExtension->getAssetUrl());
                    if ($comments_read == 1) {
                        $content->setVariable("ANNOTATION_COUNT", count($comments));
                        $content->setVariable("ANNOTATION_TITLE", "Keine ungelesenen Kommentare vorhanden");
                        $content->setVariable("COMMENTSTATUS_ICON", "no_comments");
                    } else {
                        $content->setVariable("ANNOTATION_COUNT", "<b>" . count($comments) . "</b>");
                        $content->setVariable("ANNOTATION_TITLE", "Ungelesene Kommentare vorhanden");
                        $content->setVariable("COMMENTSTATUS_ICON", "comments");
                    }
                    foreach ($users[$count . $count2] as $currentUserID) {
                        if (!isset($user_management[$currentUserID]) || $user_management[$currentUserID] == 0 || $user_management[$currentUserID] >= $count) {
                            $currentUser = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentUserID);
                            $pic_id = $currentUser->get_attribute("OBJ_ICON")->get_id();
                            $pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/15/20";
                            $content->setCurrentBlock("BLOCK_PYRAMID_POSITION_USER");
                            $content->setVariable("USER_URL", PATH_URL . "user/index/" . $currentUser->get_name());
                            $content->setVariable("PIC_URL", $pic_link);
                            $content->setVariable("USER_NAME", $currentUser->get_full_name());
                            $content->parse("BLOCK_PYRAMID_POSITION_USER");
                        }
                    }
                    $content->parse("BLOCK_PYRAMID_POSITION");
                }
            }

            $heights = array();
            $heights[0] = 5; // gap
            switch ($maxuser) {
                case 1:
                    $heights[1] = 106;
                    break;
                case 2:
                    $heights[1] = 126;
                    break;
                case 3:
                    $heights[1] = 146;
                    break;
                case 4:
                    $heights[1] = 166;
                    break;
                default:
                    $heights[1] = 176;
                    break;
            }
            $heights[2] = 2 * $heights[1] - 0.5 * $heights[1] + 1 * $heights[0];
            $heights[3] = 3 * $heights[1] + 2 * $heights[0];
            $heights[4] = 4 * $heights[1] + 3 * $heights[0];
            $heights[5] = 8 * $heights[1] + 7 * $heights[0];
            $heights[6] = 16 * $heights[1] + 15 * $heights[0];
            $heights[7] = 32 * $heights[1] + 31 * $heights[0];

            // special case
            if ($maxcol == 3) {
                $heights[3] = 2 * $heights[1] + 1 * $heights[0];
            }

            $css = "";
            for ($count = 1; $count <= $maxcol; $count++) {
                $css = $css . ".phase" . $count . " { width: 120px; height: " . $heights[$count] . "px; } \n";
            }
            // background triangle
            $triangle = $startElements * ($heights[0] + $heights[1]) * 0.5 + 16;
            $triangle_end = array(0, 300, 450, 600, 720);
            if ($startElements <= 16) {
                $css = $css . "
                            .pyramid_triangle {
                                    border-color: transparent transparent transparent #FDF1A7;
                                    border-style: solid;
                                    border-width: " . $triangle . "px 0px " . $triangle . "px " . $triangle_end[$maxcol - 1] . "px;
                                    width:0px;
                                    height:0px;
                            }";
            }

            $content->setVariable("CSS_HEIGHTS", $css);
            $content->setVariable("JS_STARTPOSITIONS", $startElements);
            $content->setVariable("JS_GAP", $heights[0]);
            $content->setVariable("JS_HEIGHT", $heights[1]);
            $content->setVariable("JS_DEADLINES", $deadlines_used);
            $content->setVariable("EMPTY_DIV_HEIGHT", $startElements * ($heights[0] + $heights[1]) + 16 . "px");
            $content->parse("BLOCK_PYRAMID");

            $rawWidget = new \Widgets\RawHtml();
            $rawWidget->setHtml($content->get());
            $frameResponseObject->addWidget($rawWidget);
            $frameResponseObject->setHeadline("Pyramidendiskussion: " . $pyramidRoom->get_attribute("OBJ_DESC"));
            return $frameResponseObject;
        }
    }
}
?>
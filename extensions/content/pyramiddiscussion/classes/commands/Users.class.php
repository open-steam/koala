<?php
namespace Pyramiddiscussion\Commands;

class Users extends \AbstractCommand implements \IFrameCommand {

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
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        $pyramidRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $pyramiddiscussionExtension = \Pyramiddiscussion::getInstance();
        $pyramiddiscussionExtension->addCSS();
        $group = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_PRIVGROUP");
        $content = $pyramiddiscussionExtension->loadTemplate("pyramiddiscussion_users.template.html");

        // if user is no admin display error msg
        if (!$group->is_admin($user)) {
            $rawWidget = new \Widgets\RawHtml();
            $rawWidget->setHtml("Error: Kein Administrator");
            $frameResponseObject->addWidget($rawWidget);
            return $frameResponseObject;
        }

        $members = $group->get_members();

        $maxcol = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAXCOL");
        $maxstart = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAX");
        $participants = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_PARTICIPANT_MANAGEMENT");
        foreach ($members as $member) {
            if (!isset($participants[$member->get_id()])) {
                $participants[$member->get_id()] = 0;
            }
        }

        // save changes if form got submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_users"])) {
            $previousstartpositions_post = $_POST["previousstartposition"];
            $startpositions_post = $_POST["startposition"];
            $deactivate_post = $_POST["deactivate"];
            if (isset($_POST["admin"])) {
                $admin_post = $_POST["admin"];
            } else
                $admin_post = array();
            $formeradmin_post = $_POST["formeradmin"];
            for ($count = 0; $count < count($members); $count++) {
                if ($members[$count] instanceof \steam_user && $members[$count]->get_name() != "root") {
                    // change startposition
                    $currentUserID = $members[$count]->get_id();
                    if ($previousstartpositions_post[$currentUserID] == "") {
                        $previousstartpositions_post[$currentUserID] = 0;
                    }
                    if ($previousstartpositions_post[$currentUserID] != $startpositions_post[$currentUserID]) {
                        if ($previousstartpositions_post[$currentUserID] != 0) {
                            $oldPosition = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $pyramidRoom->get_path() . "/Position_1_" . $previousstartpositions_post[$currentUserID]);
                            $oldPositionGroup = $oldPosition->get_attribute("PYRAMIDDISCUSSION_RELGROUP");
                            $oldPositionGroup->remove_member($members[$count]);
                            $userGroupID = 0;
                        }
                        if ($startpositions_post[$currentUserID] != 0) {
                            $newPosition = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $pyramidRoom->get_path() . "/Position_1_" . $startpositions_post[$currentUserID]);
                            $newPositionGroup = $newPosition->get_attribute("PYRAMIDDISCUSSION_RELGROUP");
                            $newPositionGroup->add_member($members[$count]);
                            $userGroupID = $newPositionGroup->get_id();
                        }
                    } else {
                        if ($previousstartpositions_post[$currentUserID] != 0) {
                            $userPosition = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $pyramidRoom->get_path() . "/Position_1_" . $previousstartpositions_post[$currentUserID]);
                            $helpGroup = $userPosition->get_attribute("PYRAMIDDISCUSSION_RELGROUP");
                            $userGroupID = $helpGroup->get_id();
                        } else
                            $userGroupID = 0;
                    }
                    $participants[$currentUserID] = $deactivate_post[$currentUserID];
                    if ($currentUserID !== $user->get_id() && $currentUserID !== $pyramidRoom->get_creator()->get_id()) {
                        if (!isset($admin_post[$currentUserID])) {
                            if ($formeradmin_post[$currentUserID] == "on" && $group->is_admin($user)) {
                                $group->remove_member($members[$count]);
                                $group->add_member($members[$count]);
                            }
                        } else {
                            if ($formeradmin_post[$currentUserID] == "off") {
                                // give adminrights
                                $group->set_admin($members[$count]);
                            }
                        }
                    }
                }
            }
            $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_PARTICIPANT_MANAGEMENT", $participants);
            $frameResponseObject->setConfirmText("Änderungen erfolgreich gespeichert.");
        }
        // save users of the startpositions to arrays
        $startpositions = array();
        for ($count = 1; $count <= $maxstart; $count++) {
            $startpositions[$count] = array();
            $position = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $pyramidRoom->get_path() . "/Position_1_" . $count);
            $positionGroup = $position->get_attribute("PYRAMIDDISCUSSION_RELGROUP");
            $positionMembers = $positionGroup->get_members();
            for ($count2 = 0; $count2 < count($positionMembers); $count2++) {
                array_push($startpositions[$count], $positionMembers[$count2]->get_id());
            }
        }

        // display users
        $content->setCurrentBlock("BLOCK_PYRAMID_USERS");
        $content->setVariable("LOGIN_LABEL", "Login");
        $content->setVariable("NAME_LABEL", "Name");
        $content->setVariable("START_LABEL", "Startposition");
        $content->setVariable("DEACTIVATE_LABEL", "Hat mitgemacht bis");
        $content->setVariable("ADMIN_LABEL", "Admin");
        for ($count = 0; $count < count($members); $count++) {
            if ($members[$count] instanceof \steam_user && $members[$count]->get_name() != "root") {
                $content->setCurrentBlock("BLOCK_PYRAMID_USERS_ELEMENT");
                $content->setVariable("USER_LOGIN", $members[$count]->get_name());
                $content->setVariable("USER_NAME", $members[$count]->get_full_name());
                $content->setVariable("USER_ID", $members[$count]->get_id());
                for ($count2 = 0; $count2 <= $maxstart; $count2++) {
                    $content->setCurrentBlock("BLOCK_POSITION_OPTION");
                    $content->setVariable("POSITION_ID", $count2);
                    if ($count2 == 0) {
                        if ($maxstart >= 10) {
                            $content->setVariable("POSITION_VALUE", "--");
                        } else {
                            $content->setVariable("POSITION_VALUE", "-");
                        }
                    } else {
                        $content->setVariable("POSITION_VALUE", $count2);
                        if (in_array($members[$count]->get_id(), $startpositions[$count2])) {
                            $content->setVariable("POSITION_SELECTED", "selected");
                            $content->setVariable("PREVIOUSSTART_VALUE", $count2);
                        }
                    }
                    $content->parse("BLOCK_POSITION_OPTION");
                }
                for ($count2 = 0; $count2 <= $maxcol - 1; $count2++) {
                    $content->setCurrentBlock("BLOCK_PHASE_OPTION");
                    $content->setVariable("PHASE_ID", $count2);
                    if ($count2 == 0) {
                        $content->setVariable("PHASE_VALUE", "Komplette Diskussion");
                    } else {
                        $content->setVariable("PHASE_VALUE", $count2 . ". Diskussionsphase");
                    }
                    if ($count2 == $participants[$members[$count]->get_id()]) {
                        $content->setVariable("PHASE_SELECTED", "selected");
                    }
                    $content->parse("BLOCK_PHASE_OPTION");
                }
                if ($group->is_admin($members[$count])) {
                    $content->setVariable("ADMIN_SELECTED", "checked");
                    $content->setVariable("FORMER_ADMIN", "on");
                } else {
                    $content->setVariable("FORMER_ADMIN", "off");
                }
                if ($members[$count]->get_id() === $user->get_id() || $members[$count]->get_id()  === $pyramidRoom->get_creator()->get_id()) {
                    $content->setVariable("ADMIN_DISABLED", "disabled");
                }
                $content->parse("BLOCK_PYRAMID_USERS_ELEMENT");
            }
        }
        $content->setVariable("SAVE_CHANGES", "Änderungen speichern");
        $content->setVariable("BACK_LINK", $pyramiddiscussionExtension->getExtensionUrl() . "Index/" . $this->id);
        $content->setVariable("BACK_LABEL", "Zurück");
        $content->parse("BLOCK_PYRAMID_USERS");

        $rawWidget = new \Widgets\RawHtml();
        $rawWidget->setHtml($content->get());
        $frameResponseObject->addWidget($rawWidget);
        $frameResponseObject->setHeadline(array(
            array("name" => "Pyramidendiskussion", "link" => $pyramiddiscussionExtension->getExtensionUrl() . "Index/" . $this->id),
            array("name" => "Teilnehmerverwaltung"),
        ));
        return $frameResponseObject;
    }
}
?>
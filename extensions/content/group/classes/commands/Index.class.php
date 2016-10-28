<?php

namespace Group\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;
    private $template;
    private $excludedGroupNames = array("sTeam", "everyone");

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = (int) $this->params[0] : "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $this->current_user = \lms_steam::get_current_user();

        if (!isset($this->id) || !is_int($this->id) || $this->id < 1) {
            $errorHtml = new \Widgets\RawHtml();
            $errorHtml->setHtml("Die übergebene ID ist entweder keine Zahl oder keine gültige ID.");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;
        }

        $this->group = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        if (!$this->group instanceof \steam_group) {
            $errorHtml = new \Widgets\RawHtml();
            $errorHtml->setHtml("Das Objekt zu der übergebenen ID " . $this->id . " ist keine Gruppe.");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;
        }
        $frameResponseObject->setTitle($this->group->get_name());
        if (in_array($this->group->get_name(), $this->excludedGroupNames)) {
            $errorHtml = new \Widgets\RawHtml();
            $errorHtml->setHtml("Diese Gruppe wurde aus Datenschutzgründen von der Ansicht ausgeschlossen.");
            $frameResponseObject->addWidget($errorHtml);
            return $frameResponseObject;
        }

        return $this->execute($frameResponseObject);
    }

    public function execute(\FrameResponseObject $frameResponseObject) {

        $this->template = \Group::getInstance()->loadTemplate("index.template.html");

        $members = $this->group->get_members();

        $groupName = "<u>" . $this->group->get_name() . "</u>";
        $parent = $this->group->get_parent_group();
        while ($parent instanceof \steam_group) {
            $groupName = "<a href='/group/index/" . $parent->get_id() . "'>" . $parent->get_name() . "</a>." . $groupName;
            $parent = $parent->get_parent_group();
        }

        $this->template->setVariable("GROUP_NAME", $groupName);

        //save the surnames of the users in an separate array which will be alphabetically sorted
        $members_user = array();
        $members_user_name = array();
        foreach ($members as $member) {
            $id = $member->get_id();
            if ($member instanceof \steam_user) {
                $members_user[$id] = $member;
                $members_user_name[$id] = $member->get_attribute("USER_FULLNAME");
            }
        }
        natcasesort($members_user_name);

        //then use tue sorted array to display the users in the correct order
        $counter = 0;
        foreach ($members_user_name as $id => $member) {
            $this->template->setCurrentBlock("BLOCK_MEMBERS");
            $this->template->setVariable("MEMBER_ID", $members_user[$id]->get_id());
            $this->template->setVariable("MEMBER_LOGIN_NAME", $members_user[$id]->get_name());
            $this->template->setVariable("MEMBER_FIRST_NAME", $members_user[$id]->get_attribute("USER_FIRSTNAME"));
            $this->template->setVariable("MEMBER_LAST_NAME", $members_user[$id]->get_attribute("USER_FULLNAME"));

            $user_icon = $members_user[$id]->get_attribute("OBJ_ICON");
            if(!$user_icon instanceof \steam_document){
              $user_icon = \steam_factory::get_object_by_name($GLOBALS[ "STEAM" ]->get_id(), "/images/doctypes/user_unknown.jpg");
            }

            $this->template->setVariable("MEMBER_PIC_LINK", PATH_URL . "download/image/" . $user_icon->get_id() . "/50/60/");
            $this->template->parse("BLOCK_MEMBERS");
            $counter++;
        }

        if ($counter === 0) {
            $this->template->setVariable("NO_MEMBERS", "Es sind keine Mitglieder in dieser Gruppe");
        }

        $rawHtml = new \Widgets\RawHtml();
        $rawHtml->setHtml($this->template->get());
        $frameResponseObject->addWidget($rawHtml);

        return $frameResponseObject;
    }

}

?>

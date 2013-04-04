<?php
namespace Pyramiddiscussion\Commands;

class Create extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \AjaxRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params["id"]) ? $this->id = $this->params["id"] : "";
        }
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");

        if (!isset($this->params["group"])) {
            $rawWidget = new \Widgets\RawHtml();
            $rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
            $ajaxResponseObject->addWidget($rawWidget);
            return $ajaxResponseObject;
        }

        if ($this->params["group"] == 1) {
            // course
            if (!(isset($this->params["course"]))) {
                $rawWidget = new \Widgets\RawHtml();
                $rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
                $ajaxResponseObject->addWidget($rawWidget);
                return $ajaxResponseObject;
            }
            $course = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["course"]);
            $subgroups = $course->get_subgroups();
            foreach ($subgroups as $subgroup) {
                if ($subgroup->get_name() == "staff") {
                    $admingroup = $subgroup->get_id();
                } else if ($subgroup->get_name() == "learners") {
                    $basegroup = $subgroup->get_id();
                }
            }
        } else {
            // group
            if (!(isset($this->params["basegroup"])) || !(isset($this->params["admingroup"]))) {
                $rawWidget = new \Widgets\RawHtml();
                $rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
                $ajaxResponseObject->addWidget($rawWidget);
                return $ajaxResponseObject;
            }
            $basegroup = $this->params["basegroup"];
            $admingroup = $this->params["admingroup"];
        }
        $start = $this->params["startElements"];
        $maxcol = intval(log($start, 2) + 1);

        $private_group = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "PrivGroups");
        $basegroup_original = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $basegroup);
        
        if ($basegroup_original->count_members() > 64) {
            $rawWidget = new \Widgets\RawHtml();
            $rawWidget->setHtml("Error: Gruppe enthält mehr als 64 Mitglieder.");
            $ajaxResponseObject->addWidget($rawWidget);
            return $ajaxResponseObject;
        }
        
        $container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $pyramidRoom = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $this->params["title"], $container, $this->params["title"]);
        $basegroup = \steam_factory::create_group($GLOBALS["STEAM"]->get_id(), "pyramid_" . $pyramidRoom->get_id(), $private_group);
        $basegroup->set_attribute("GROUP_INVISIBLE", true);
        $basegroup->set_sanction_all($basegroup);
        
        if ($admingroup != 0) {
            $admingroup = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $admingroup);
        }
        /*$user = $GLOBALS["STEAM"]->get_current_steam_user();
        foreach ($basegroup_original->get_members() as $member) {
            if ($member instanceof \steam_user) {
                $basegroup->add_member($member);
            }
        }
        $admins = array();
        if ($admingroup != 0) {
            $admingroup = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $admingroup);
            foreach ($admingroup->get_members() as $member) {
                if (!$basegroup->is_member($member) && $member instanceof \steam_user) {
                    $basegroup->add_member($member);
                } 
                if ($member instanceof \steam_user) {
                    $basegroup->set_admin($member);
                    array_push($admins, $member);
                }
            }
        }
        if (!$basegroup->is_member($user)) {
            $basegroup->add_member($user);
        }
        if (!$basegroup->is_admin($user)) {
            $basegroup->set_admin($user);
            array_push($admins, $user);
        }*/
        
        //$pyramidRoom->set_sanction_all($admingroup);
        $pyramidRoom->set_sanction_all($basegroup);
        $pyramidRoom->set_attribute("OBJ_TYPE", "container_pyramiddiscussion");
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_ACTCOL", 0);
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_ADMINGROUP", $admingroup);
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_BASEGROUP", $basegroup_original);
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_PRIVGROUP", $basegroup);
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_DEADLINES", array());
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_EDITOR", $this->params["editor"]);
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_INITIALIZED", 1);
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_MAX", $start);
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_MAXCOL", $maxcol);
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_OVERRIDE_DEADLINES", 0);
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_USEDEADLINES", "no");
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_VERSION", "koala_3.0");

        /*$participants = array();
        $members = $basegroup->get_members();
        foreach ($members as $member) {
            if ($member instanceof \steam_user) {
                $participants[$member->get_id()] = 0;
            }
        }
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_PARTICIPANT_MANAGEMENT", $participants);

        $adminconfig = array();
        foreach ($admins as $admin) {
            if ($admin instanceof \steam_user) {
                $options = array();
                $options["show_adminoptions"] = "true";
                $adminconfig[$admin->get_id()] = $options;
            }
        }
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_ADMINCONFIG", $adminconfig);*/

        //$pyramidGroup = \steam_factory::create_group($GLOBALS["STEAM"]->get_id(), "pyramid_" . $pyramidRoom->get_id(), $basegroup);
        // create position documents and corresponding groups
        /*$groups = array();
        for ($count = 1; $count <= $maxcol; $count++) {
            for ($count2 = 1; $count2 <= $start / pow(2, $count - 1); $count2++) {
                $newGroup = \steam_factory::create_group($GLOBALS["STEAM"]->get_id(), "group_" . $count . "_" . $count2, $basegroup);
                $newGroup->set_insert_access($basegroup);
                $newGroup->set_attribute("GROUP_INVISIBLE", true);
                $groups[$count . $count2] = $newGroup;

                $newPosition = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "Position_" . $count . "_" . $count2, "", $this->params["editor"], $pyramidRoom, "Position " . $count . "-" . $count2);
                $newPosition->set_attribute("PYRAMIDDISCUSSION_COLUMN", $count);
                $newPosition->set_attribute("PYRAMIDDISCUSSION_ROW", $count2);
                $newPosition->set_attribute("PYRAMIDDISCUSSION_POS_READ_STATES", array());
                $newPosition->set_attribute("PYRAMIDDISCUSSION_POS_TITLE", "");
                $newPosition->set_attribute("PYRAMIDDISCUSSION_RELGROUP", $newGroup);
            }
        }
        // generate group structure
        for ($count = 2; $count <= $maxcol; $count++) {
            for ($count2 = 1; $count2 <= $start / pow(2, $count - 1); $count2++) {
                $groups[$count . $count2]->add_member($groups[$count - 1 . ($count2 * 2 - 1)]);
                $groups[$count . $count2]->add_member($groups[$count - 1 . ($count2 * 2)]);
            }
        }
        foreach ($groups as $group) {
            $basegroup->add_member($group);
        }*/
        $pyramidRoom->set_attribute("PYRAMIDDISCUSSION_INITIALIZED", 0);
        $basegroup->set_attribute("PYRAMIDDISCUSSION_INSTANCES", array($pyramidRoom->get_id()));

        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setJs(<<<END
		closeDialog();
		sendRequest("LoadContent", {"id":"{$this->id}"}, "explorerWrapper", "updater", null, null, "explorer");
END
        );
        $ajaxResponseObject->addWidget($jswrapper);
        return $ajaxResponseObject;
    }
}
?>
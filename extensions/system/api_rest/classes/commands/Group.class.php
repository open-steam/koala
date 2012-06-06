<?php
namespace Rest\Commands;
class Group extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $function;
    private $functionList = array("getWorkroom", "getGroupByName", "getAllGroups", "getMembers", "addMember", "removeMember");

    public function httpAuth(\IRequestObject $requestObject) {
        return true;
    }

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->function = $this->params[0]: "";
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        if (!in_array(strtolower($this->function), array_map("strtolower",$this->functionList))) {
             $this->function = $this->functionList[0];
        }
        array_shift($this->params);
        $result = call_user_func_array(array(__CLASS__, $this->function), $this->params?:array());
        $resultArray = getSerializedObject($result);
        echo json_encode($resultArray);
        exit;
    }

    public function getWorkroom($id) {
        $steamGroup = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamGroup instanceof \steam_group) {
            $workRoom = $steamGroup->get_workroom();
            if ($workRoom instanceof \steam_room) {
                return $workRoom;
            }
        }
        HTTPStatus(400);
    }

    public function getGroupByName($groupName) {
        $steamGroup = \steam_factory::get_group($GLOBALS["STEAM"]->get_id(), $groupName);
        if ($steamGroup instanceof \steam_group) {
            return $steamGroup;
        }
        HTTPStatus(400);
    }

    public function getAllGroups() {
        $groupsModule = $GLOBALS["STEAM"]->get_module("groups");
        if ($groupsModule instanceof \steam_object) {
            $allGroups = $GLOBALS["STEAM"]->predefined_command($groupsModule, "get_groups", array(), false);
            return $allGroups;
        }
        HTTPStatus(400);
    }

    public function getMembers($id) {
        $steamGroup = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if($steamGroup instanceof \steam_group) {
            return $steamGroup->get_members();
        }
        HTTPStatus(400);
    }

    public function addMember($id, $userId) {
        $steamGroup = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if($steamGroup instanceof \steam_group) {
            $steamUser = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $userId);
            if ($steamUser instanceof \steam_user) {
                return $steamGroup->add_member($steamUser);
            }
        }
        HTTPStatus(400);
    }

    public function removeMember($id, $userId) {
        $steamGroup = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if($steamGroup instanceof \steam_group) {
            $steamUser = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $userId);
            if ($steamUser instanceof \steam_user) {
                return $steamGroup->remove_member($steamUser);
            }
        }
        HTTPStatus(400);
    }
}
?>
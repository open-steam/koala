<?php
namespace Rest\Commands;
class User extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $function;
    private $functionList = array("getMyUser", "getWorkroom", "getUserByName", "getAllUsers", "getMemberships");

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

    public function getMyUser() {
        $steamUser = $GLOBALS["STEAM"]->get_current_steam_user();
        return $steamUser;
    }

    public function getWorkroom($id) {
        $steamUser = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamUser instanceof \steam_user) {
            $workRoom = $steamUser->get_workroom();
            return $workRoom;
        }
        HTTPStatus(400);
    }

    public function getUserByName($name) {
        $steamUser = \steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $name);
        if ($steamUser instanceof \steam_user) {
            return $steamUser;
        }
        HTTPStatus(400);
    }

    public function getAllUsers() {
        $usersModule = $GLOBALS["STEAM"]->get_module("users");
        if ($usersModule instanceof \steam_object) {
            $allUsers = $GLOBALS["STEAM"]->predefined_command($usersModule, "get_users", array(), false);
            return $allUsers;
        }
        HTTPStatus(400);
    }

    public function getMemberships($id) {
        $steamUser = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        if ($steamUser instanceof \steam_user) {
            return $steamUser->get_groups();
        }
        HTTPStatus(400);
    }

}
?>
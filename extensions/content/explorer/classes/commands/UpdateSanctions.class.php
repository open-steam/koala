<?php

namespace Explorer\Commands;

class UpdateSanctions extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $object;
    private $sanctionId;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $this->id = $this->params["id"];
        if (isset($this->params["sanctionId"])) {
            $this->sanctionId = $this->params["sanctionId"];
        }
        $this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $type = $this->params["type"];
        $value = $this->params["value"];
        //var_dump($this->object);die;
        //echo $this->object->get_name();die;
        $sanction = $this->object->get_sanction();
        $attrib = $this->object->get_attributes(array(OBJ_NAME, OBJ_DESC, "bid:doctype"));
        $bid_doctype = isset($attrib["bid:doctype"]) ? $attrib["bid:doctype"] : "";
        $docTypeQuestionary = strcmp($attrib["bid:doctype"], "questionary") == 0;
        $docTypeMessageBoard = $this->object instanceof \steam_messageboard;

        $objType = $this->object->get_attribute("OBJ_TYPE");


        // in questionaries the write right is limited to insert rights only
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
        //SET ACQUIRE RIGHTS
        if ($type == "acquire") {
            $currentUser = \lms_steam::get_current_user();
            if ($value == "acq") {
                $this->object->set_acquire_from_environment();
                foreach ($sanction as $id => $sanct) {
                    if ($id !== $currentUser->get_id()) {
                        $this->object->sanction(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id));
                        $this->object->sanction_meta(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id));
                    }
                }
            } else {


                $this->object->sanction(SANCTION_ALL, $currentUser);
                $this->object->sanction_meta(SANCTION_ALL, $currentUser);
                $this->object->set_acquire(0);
            }
        }
        //SET CRUDE RIGHTS
        elseif ($type == "crude") {
            $everyone = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "everyone");
            $everyoneId = $everyone->get_id();
            $steamGroup = \steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "sTeam");
            $steamGroupId = $steamGroup->get_id();
            $currentUser = \lms_steam::get_current_user();
            $currentUserId = $currentUser->get_id();

            if ($value == "privat") {
                foreach ($sanction as $id => $sanct) {
                    if ($currentUserId != $id) {
                        $this->object->sanction(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id, CLASS_OBJECT));
                        $this->object->sanction_meta(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id, CLASS_OBJECT));
                    }
                }
            } elseif ($value == "user_public") {
                //DENY GLOBAL ACCESS
                $this->object->sanction(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $everyoneId, CLASS_OBJECT));
                $this->object->sanction_meta(ACCESS_DENIED, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $everyoneId, CLASS_OBJECT));
                //SET LOCAL SERVER ACCESS
                if (!isset($sanction[$steamGroupId])) {
                    $this->object->sanction(SANCTION_READ, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $steamGroupId, CLASS_OBJECT));
                    $this->object->sanction_meta(SANCTION_READ, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $steamGroupId, CLASS_OBJECT));
                }
            } elseif ($value == "server_public") {
                $this->object->sanction(SANCTION_READ, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $everyoneId, CLASS_OBJECT));
                $this->object->sanction_meta(SANCTION_READ, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $everyoneId, CLASS_OBJECT));
            }
        }
        //SET SPECIFIC RIGHTS
        elseif ($type == "sanction") {
            $postboxHack = false;
            if ($objType === "postbox" && !($this->object instanceof \steam_document)) {
                $inventory = $this->object->get_inventory();
                $container = $inventory[0];
                $postboxHack = true;
            }
            $currentSanction = ACCESS_DENIED;
            $additionalSanction = ACCESS_DENIED;
            if ($value >= 1) {
                $currentSanction|= SANCTION_READ;
            }
            if ($value >= 2) {
                $currentSanction|= $SANCTION_WRITE_FOR_CURRENT_OBJECT;
            }
            if ($value == 3) {
                $currentSanction |= SANCTION_SANCTION;
                $additionalSanction = SANCTION_ALL;
            }
            $this->object->sanction($currentSanction, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->sanctionId, CLASS_OBJECT));
            // set the new meta rights
            $this->object->sanction_meta($additionalSanction, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->sanctionId, CLASS_OBJECT));
            if ($postboxHack) {
                if ($value >= 2) {
                    $container->sanction($currentSanction, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->sanctionId, CLASS_OBJECT));
                } else {
                    $container->sanction(SANCTION_INSERT, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->sanctionId, CLASS_OBJECT));
                }
                $container->sanction_meta($additionalSanction, \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->sanctionId, CLASS_OBJECT));
            }
        }
        $ajaxResponseObject->setStatus("ok");
        return $ajaxResponseObject;
    }

}

?>
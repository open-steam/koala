<?php

class Ellenberg extends AbstractExtension implements IObjectExtension {

    public function getName() {
        return "Ellenberg";
    }

    public function getDesciption() {
        return "Extension Ellenberg";
    }

    public function getVersion() {
        return "v1.0.0";
    }

    public function getAuthors() {
        $result = array();
        $result[] = new Person("Andreas", "Schultz", "schultza@mail.uni-paderborn.de");
        return $result;
    }

    public function getObjectReadableName() {
        return "Ellenberg";
    }

    public function getObjectReadableDescription() {
        return "";
    }

    public function getObjectIconUrl() {
        return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/postbox.png";
    }

    public function getCreateNewCommand(IdRequestObject $idEnvironment) {
        return new \Ellenberg\Commands\NewEllenberg();
    }

    public function getCommandByObjectId(IdRequestObject $idRequestObject) {
        $ellenberg = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
        $ellenbergType = $ellenberg->get_attribute("OBJ_TYPE");
        if ($ellenbergType === "ellenberg") {
            return new \Ellenberg\Commands\Index();
        }
        return null;
    }

    public function getCurrentObject(UrlRequestObject $urlRequestObject) {
        $params = $urlRequestObject->getParams();
        $id = $params[0];
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
        return $object;
        
    }

    public function getPriority() {
        return 8;
    }

}

?>
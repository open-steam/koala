<?php

class Postbox extends AbstractExtension implements IObjectExtension {

    public function getName() {
        return "Postbox";
    }

    public function getDesciption() {
        return "Extension Postbox";
    }

    public function getVersion() {
        return "v1.0.0";
    }

    public function getAuthors() {
        $result = array();
        $result[] = new Person("Christoph", "Sens", "csens@mail.uni-paderborn.de");
        return $result;
    }

    public function getObjectReadableName() {
        return "Hausaufgabenabgabekasten";
    }

    public function getObjectReadableDescription() {
        return "";
    }

    public function getObjectIconUrl() {
        return Explorer::getInstance()->getAssetUrl() . "icons/mimetype/gallery.png";
    }

    public function getCreateNewCommand(IdRequestObject $idEnvironment) {
        return new \Postbox\Commands\NewPostbox();
    }

    public function getCommandByObjectId(IdRequestObject $idRequestObject) {
        $postbox = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $idRequestObject->getId());
        $postboxType = $postbox->get_attribute("OBJ_TYPE");
        if ($postboxType === "postbox") {
            return new \Postbox\Commands\Index();
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
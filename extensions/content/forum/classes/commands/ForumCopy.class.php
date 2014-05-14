<?php

namespace Forum\Commands;

class ForumCopy extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $user;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    private static function copyPicture($obj) {
        $attribute = $obj->get_attribute("bid:forum:category:picture_id");

        if ($attribute !== 0) {
            $orgPic = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $attribute);
            if ($orgPic instanceof \steam_document) {
                $copyPic = $orgPic->copy();
                $picId = $copyPic->get_id();
                $obj->set_attribute("bid:forum:category:picture_id", $picId);
            }else{
                $obj->set_attribute("bid:forum:category:picture_id", 0);

            }
        }
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        $currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
        $objectId = $this->params["objectId"];
        $object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        \logging::append_log(LOG_ERROR, "Hallo");

        $copyObj = $object->copy();
        $topics = $copyObj->get_annotations();
        //TODO: TESTE für leere Themen und auch den Fall ohne Antworten.
        foreach ($topics as $topic) {
            self::copyPicture($topic);
            $answers = $topic->get_annotations();
            foreach ($answers as $answer) {
                self::copyPicture($answer);
            }
        }
        $copyObj->move($currentUser);
    }

    public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
        $ajaxResponseObject->setStatus("ok");
        $jswrapper = new \Widgets\JSWrapper();
        $jswrapper->setJs(<<<END
        window.location.reload();
END
        );
        $ajaxResponseObject->addWidget($jswrapper);
        return $ajaxResponseObject;
    }

}

<?php

namespace PortletMsg\Commands;

class CreateMessage extends \AbstractCommand implements \IAjaxCommand {

    private $insertTop = false;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $params = $requestObject->getParams();
        $parentObjectId = $params["id"];
        $title = $params["title"];
        $text = $params["text"];
        
        if($params["insertOption"] === "0"){
            $this->insertTop = true;
        }else{
            $this->insertTop = false;
        }
        
        //check diffrent types of parameter
        if (is_string($parentObjectId)) {

            $portletObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $parentObjectId);
        } else {

            $portletObject = $parentObjectId;
        }

        if (strlen($title) == 0) {
            $pName = "Neue Meldung";
        } else {
            $pName = $title;
        }
        if (strlen($text) == 0) {
            $pContent = "Bitte geben Sie hier den Meldungstext ein.";
        } else {
            $pContent = $text;
        }
        $pMimeType = "text/plain";
        $pEnvironment = $portletObject; //default is FALSE
        $pDescription = "";

        $messageObject = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), strip_tags($pName), $pContent, $pMimeType, $pEnvironment, $pDescription);

        $messageObject->set_attribute("bid:doctype", "portlet:msg");
        $messageObject->set_attribute("bid:portlet:msg:link_open", "checked");
        $messageObject->set_attribute("bid:portlet:msg:link_url", "");
        $messageObject->set_attribute("bid:portlet:msg:link_url_label", "");
        $messageObject->set_attribute("bid:portlet:msg:picture_alignment", "left");
        $messageObject->set_attribute("bid:portlet:msg:picture_width", "");

        $this->addMessageIdToPortlet($portletObject, $messageObject);
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

    private function addMessageIdToPortlet($portletObject, $messageObject) {
        //add attributes to messages portlet
        $content = $portletObject->get_attribute("bid:portlet:content");
        if ($content == "0") {
            $content = array();
        }

        $id = $messageObject->get_id();
        if($this->insertTop){
            $newContent = array();
            $newContent[0] = $id;
            foreach($content as $i => $c){
                $newContent[$i+1] = $c;
            }
            $portletObject->set_attribute("bid:portlet:content", $newContent);

        }else{
            $content[] = $id;
            $portletObject->set_attribute("bid:portlet:content", $content);

        }
            }

}

?>
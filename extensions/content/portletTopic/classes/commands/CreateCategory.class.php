<?php

namespace PortletTopic\Commands;

class CreateCategory extends \AbstractCommand implements \IAjaxCommand {

    private $params;
    private $id;
    private $content;
    private $dialog;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $params = $requestObject->getParams();
        $objectId = $params["id"];

        $topicObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $content = $topicObject->get_attribute("bid:portlet:content");

        $checkbox = "";
        if($params["window"] == "true"){
          $checkbox = "checked";
        }

        //prepare the category
        $Topic = array(
            "description" => $params["desc"],
            "link_target" => $checkbox,
            "link_url" => $params["link"],
            "title" => $params["title"]);

        $topics = array($Topic);
        $newCategory = array("title" => "", "topics" => $topics);

        //add entry
        if ($content == "") { //content not existing
            $content = array();
            $content[] = $newCategory;
        }
        else{ //content existing, add entry to the last topic
          array_push($content, $newCategory);
        }

        //persistate the new category
        $topicObject->set_attribute("bid:portlet:content", $content);

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

?>

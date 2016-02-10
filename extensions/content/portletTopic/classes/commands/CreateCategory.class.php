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
        //$desc = $params["desc"];
        //$link = $params["link"];
        //$window = $params["window"];

        /*
        if (isset($params["title"])) {
            $categoryTitle = $params["title"];
        } else {
            $categoryTitle = "Link";
        }
        */

        $topicObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
        $content = $topicObject->get_attribute("bid:portlet:content");

        //prepare the category
        $Topic = array(
            "description" => $params["desc"],
            "link_target" => $params["window"],
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





/*
Entry:

    		$topics = $content[$categoryIndex]["topics"];
    		$categoryTitle = $content[$categoryIndex]["title"];

    		//prepare the category
    		$newTopic = array(	"description" => $desc,
    							"link_target" => $window == "true" ? "checked" : "",
    							"link_url" => $link,
    							"title" => $categoryTitle1);

    		$topics[] = $newTopic;

    		$content[$categoryIndex] = array("title" => $categoryTitle, "topics" => $topics);

*/








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

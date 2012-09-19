<?php
namespace PortletTopic\Commands;
class CreateEntry extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();             
		$objectId = $params["id"];
		$categoryIndex = $params["categoryIndex"];
                $desc = $params["desc"];
                $link = $params["link"];
                $window = $params["window"];
		
		if(isset($params["title"])){
			$categoryTitle1 = $params["title"];
		}else{
			$categoryTitle1 = "Neue Kategorie";
		}
                
		
		$topicObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$content = $topicObject->get_attribute("bid:portlet:content");
		$topics = $content[$categoryIndex]["topics"];
		$categoryTitle = $content[$categoryIndex]["title"];
		
		//prepare the category
		$newTopic = array(	"description" => $desc,
							"link_target" => $window == "true" ? "checked" : "",
							"link_url" => $link,	
							"title" => $categoryTitle1);
		
		$topics[] = $newTopic;
		
		$content[$categoryIndex] = array("title" => $categoryTitle, "topics" => $topics);
		
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
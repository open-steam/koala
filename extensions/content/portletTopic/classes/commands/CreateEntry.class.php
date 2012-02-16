<?php
namespace PortletTopic\Commands;
class CreateEntry extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["portletId"];
		$categoryIndex = $params["categoryIndex"];
		
		if(isset($params["categoryTitle"])){
			$categoryTitle = $params["categoryTitle"];
		}else{
			$categoryTitle = "Neue Kategorie";
		}
		
		$topicObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$content = $topicObject->get_attribute("bid:portlet:content");
		$topics = $content[$categoryIndex]["topics"];
		$categoryTitle = $content[$categoryIndex]["title"];
		
		//prepare the category
		$newTopic = array(	"description" => "",
							"link_target" => "checked",
							"link_url" => "",	
							"title" => "Neuer Eintrag");
		
		$topics[] = $newTopic;
		
		$content[$categoryIndex] = array("title" => $categoryTitle, "topics" => $topics);
		
		//persistate the new category
		$topicObject->set_attribute("bid:portlet:content", $content);
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
		//no response
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		// no response
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
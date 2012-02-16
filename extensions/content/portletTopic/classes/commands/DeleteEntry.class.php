<?php
namespace PortletTopic\Commands;
class DeleteEntry extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	private $categoryIndex;
	private $entryIndex;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["portletId"];
		$this->categoryIndex = $params["categoryIndex"];
		$this->entryIndex = $params["entryIndex"];
		
		$topicObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$content = $topicObject->get_attribute("bid:portlet:content");
		$entries = $content[$this->categoryIndex]["topics"];
		
		$i=0;
		$newEnties = array();
		foreach ($entries as $entry){
			if(!($i==$this->entryIndex)){
				$newEnties[] = $entry;
			}
			$i++;
		}
		
		$content[$this->categoryIndex]["topics"]=$newEnties;
		
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
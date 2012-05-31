<?php
namespace PortletPoll\Commands;
class VoteItem extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $rawHtmlWidget;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		
		$parentObjectId = $params["portletObjectId"];
		$voteItemId = $params["voteItemId"];
		
		//check diffrent types of parameter
		if(is_string($parentObjectId)){
			$portletObject =  \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$parentObjectId);
		}else{
			$portletObject = $parentObjectId;
		}
		
		//read options
		$content = $portletObject->get_attribute("bid:portlet:content");
		$optionsVoteCount = $content["options_votecount"];
		
		$optionsVoteCount[$voteItemId]++;
		
		//write options back
		$content["options_votecount"]=$optionsVoteCount;
		$portletObject->set_attribute("bid:portlet:content", $content);
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
	
	}

	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	}

	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		alert("Die Stimme wurde gezählt.");
		window.location.reload();
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
	
}
?>
<?php
namespace PortletMsg\Commands;
class DeleteMessage extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		
		$portletObjectId = $params["portletObjectId"];
		$messageObjectId = $params["messageObjectId"];
		
		//message
		if(is_string($messageObjectId)){
			$messageObject =  \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$messageObjectId);
		}else{
			$messageObject = $messageObjectId;
		}
		
		//portlet
		if(is_string($messageObjectId)){
			$portletObject =  \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$portletObjectId);
		}else{
			$portletObject = $messageObjectId;
		}
		
		//delete the object
		$trashbin = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("USER_TRASHBIN");
		//$messageObject->move($trashbin);
		$this->removeMessageIdFromPortlet($portletObject, $messageObject);
		$messageObject->delete();
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
	
	
	private function removeMessageIdFromPortlet($portletObject , $messageObject){
		$content = $portletObject->get_attribute("bid:portlet:content");
		if($content==0){
			return true;
		}
		$newContent = array();
		foreach($content as $messageId){
			if(!($messageId==$messageObject->get_id())) $newContent[] = $messageId;
		}
		$portletObject->set_attribute("bid:portlet:content",$newContent);
	}
}
?>
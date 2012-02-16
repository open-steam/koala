<?php
namespace PortletMsg\Commands;
class CreateMessage extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
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
		$name = "Neue Meldung";
		
		//check diffrent types of parameter
		if(is_string($parentObjectId)){
			$portletObject =  \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$parentObjectId);
		}else{
			$portletObject = $parentObjectId;
		}
		
		$pName = "Neue Meldung";
		$pContent = "Bitte geben Sie hier den Meldungstext ein.";
		$pMimeType = "text/plain";
		$pEnvironment = $portletObject; //default is FALSE
		$pDescription = "";
		
		$messageObject = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), $pName, $pContent, $pMimeType, $pEnvironment, $pDescription);
	    	
	    $messageObject->set_attribute("bid:doctype", "portlet:msg");
	    $messageObject->set_attribute("bid:portlet:msg:link_open", "checked");
	    $messageObject->set_attribute("bid:portlet:msg:link_url", "");
	    $messageObject->set_attribute("bid:portlet:msg:link_url_label", "");
	    $messageObject->set_attribute("bid:portlet:msg:picture_alignment", "left");
	    $messageObject->set_attribute("bid:portlet:msg:picture_width", "");
	    
	    $this->addMessageIdToPortlet($portletObject, $messageObject);
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
	
	}

	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
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
	
	private function addMessageIdToPortlet($portletObject, $messageObject){
		//add attributes to messages portlet
		$content = $portletObject->get_attribute("bid:portlet:content");
		if($content=="0"){
			$content = array();
		}
		
		$id = $messageObject->get_id();
		
		$content[] = $id;
		$portletObject->set_attribute("bid:portlet:content",$content);
	}
	
}
?>
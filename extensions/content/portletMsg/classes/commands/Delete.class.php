<?php
namespace PortletMsg\Commands;
class Delete extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["portletObjectId"];
		
		$steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		
		//select object for deletion
		if($steamObject->get_attribute("bid:doctype")==="portlet:msg"){
			//delete parent of steam object
			$portletObject = $steamObject->get_environment();
		} else if ($steamObject->get_attribute("bid:portlet")==="msg"){
			//delete steam object
			$portletObject = $steamObject;
		} else {
			//error not a valid object
			return false;
		}
		
		//delete the object
		$trashbin = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("USER_TRASHBIN");
		$portletObject->move($trashbin);
		//$portletObject->delete();
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
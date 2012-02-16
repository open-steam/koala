<?php
namespace PortletMsg\Commands;
class CopyMsg extends \AbstractCommand implements \IAjaxCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$params = $requestObject->getParams();
		$objectId = $params["id"];
		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		$portletOrginal = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		
		$portletCopy = \steam_factory::create_copy($GLOBALS["STEAM"]->get_id(), $portletOrginal);
		
		//correct message ids
		if($portletCopy->get_attribute("bid:portlet")==="msg"){
			//get ids in attribute
			$oldIds = $portletCopy->get_attribute("bid:portlet:content");
			$newIds = array();
			
			//delete wrong references messages
			foreach ($portletCopy->get_inventory() as $oldMessageObject) {
				$oldMessageObject->delete();
			}
			
			foreach ($oldIds as $messageId){
				//copy to here
				//make new id list
				$msgObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $messageId );
				$msgCopy = \steam_factory::create_copy($GLOBALS["STEAM"]->get_id(), $msgObject);
				$msgCopy->move($portletCopy);
				$newIds[]=$msgCopy->get_id();
				//handle included pics
				$pictrueId = $msgObject->get_attribute("bid:portlet:msg:picture_id");
				if($pictrueId!=""){
					$pictureObject = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $pictrueId );
					$pictuteCopy = \steam_factory::create_copy($GLOBALS["STEAM"]->get_id(), $pictureObject);
					$msgCopy->set_attribute("bid:portlet:msg:picture_id",$pictuteCopy->get_id());
					$msgCopy->move($portletCopy);
				}
				
			}
		//save in attrubute
		$portletCopy->set_attribute("bid:portlet:content",$newIds);
		}
		
		$portletCopy->move($currentUser);
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
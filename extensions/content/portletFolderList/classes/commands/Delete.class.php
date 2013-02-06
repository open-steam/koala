<?php
namespace PortletFolderList\Commands;

class Delete extends \AbstractCommand implements \IAjaxCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $params["portletId"]);
		
		//delete the object
		$trashbin = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("USER_TRASHBIN");
		$steamObject->move($trashbin);
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
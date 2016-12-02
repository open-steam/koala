<?php
namespace PortletChronic\Commands;

class Delete extends \AbstractCommand implements \IAjaxCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $params["portletId"]);
		
		//delete the object
		$trashbin = \lms_steam::get_current_user()->get_attribute("USER_TRASHBIN");
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
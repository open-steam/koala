<?php
namespace PortletSubscription\Commands;

class Delete extends \AbstractCommand implements \IAjaxCommand {

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$steamObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $params["portletId"]);
		$oldPortalID = $steamObject->get_environment()->get_environment()->get_id();

		//delete the object
		$trashbin = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("USER_TRASHBIN");
		$steamObject->move($trashbin);

		\ExtensionMaster::getInstance()->getExtensionById("HomePortal")->updateSubscriptions($oldPortalID);
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

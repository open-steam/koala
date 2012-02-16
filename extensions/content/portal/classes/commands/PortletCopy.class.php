<?php
namespace Portal\Commands;
class PortletCopy extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $user;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		if ($object instanceof \steam_link) { 
			$copy = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object->get_link_object());
		} else {
			$copy = \steam_factory::create_copy($GLOBALS["STEAM"]->get_id(), $object);
		}
		$copy->move($this->user);
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
<?php
namespace Postbox\Commands;
class Create extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");

		$envRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		$obj= \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $this->params["name"], $envRoom);
		$obj->set_attribute("OBJ_TYPE","postbox");
		 
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		closeDialog();
		location.reload();
		
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>
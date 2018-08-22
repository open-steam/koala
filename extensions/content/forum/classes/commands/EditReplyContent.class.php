<?php
namespace Forum\Commands;
class EditReplyContent extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$object= \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		$attributes = array("OBJ_DESC" => trim($this->params["title"]));
		$object->set_attributes($attributes, 0);
		$object->set_content($this->params["content"]);
		
		$ajaxResponseObject->setStatus("ok");
		$widget = new \Widgets\JSWrapper();
		$widget->setJs("location.reload();");
		$ajaxResponseObject->addWidget($widget);
		return $ajaxResponseObject;

	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>

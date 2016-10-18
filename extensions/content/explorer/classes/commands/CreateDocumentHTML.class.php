<?php
namespace Explorer\Commands;
class CreateDocumentHTML extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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

		$current_room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
    $portal = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), strip_tags($this->params["name"]), "", "text/html", $current_room);

		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs('closeDialog(); location.reload();');
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>

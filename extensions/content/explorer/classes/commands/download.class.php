<?php

namespace Explorer\Commands;

class Download extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $name;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$this->name = $object->get_name();
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");

		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml("<iframe style='display:none;' src='" . PATH_URL . "Download/Document/" . $this->id . "/" . $this->name . "'></iframe>");
		$ajaxResponseObject->addWidget($rawHtml);

		return $ajaxResponseObject;
	}
}
?>

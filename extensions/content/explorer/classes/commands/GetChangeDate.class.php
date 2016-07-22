<?php
namespace Explorer\Commands;
class GetChangeDate extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $object;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$rawHtml = new \Widgets\RawHtml();
		$html = getReadableDate($this->object->get_attribute("OBJ_LAST_CHANGED"));
		$rawHtml->setHtml($html);
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;
	}
}
?>

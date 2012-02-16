<?php
namespace Worksheet\Commands;

class SetSampleDisplayed extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;

	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

		
		
		$block = new \Worksheet\Block($this->id);
		
		$block->setSampleDisplayed();
		
		$value = "ok";
		
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($value);
		$ajaxResponseObject->addWidget($rawHtml);
		
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	
	}
	
}
?>
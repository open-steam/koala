<?php
namespace Worksheet\Commands;

class GetSampleText extends \AbstractCommand implements \IAjaxCommand {
	
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

		/* get the sample text for a block identified by $this->id */
		
		
		$block = new \Worksheet\Block($this->id);
		
		$value = $block->getSampleText();
		
		
		
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($value);
		$ajaxResponseObject->addWidget($rawHtml);
		
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	
	}
	
}
?>
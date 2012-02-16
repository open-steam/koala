<?php
namespace Help\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml("<center>Noch nicht fertig.</center>");
		$frameResponseObject->setTitle("Hilfe");
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
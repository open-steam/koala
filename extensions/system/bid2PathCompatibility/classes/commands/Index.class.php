<?php
namespace bid2PathCompatibility\Commands;
class Index extends \AbstractCommand implements \IFrameCommand{
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
        
        }
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
                $rawWidget = new \Widgets\RawHtml();
                $rawWidget->setHtml("Test bid2PathCompatibility");
                $frameResponseObject->addWidget($rawWidget);
                return $frameResponseObject;
	}
}
?>
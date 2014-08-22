<?php
namespace Rest\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	
	public function httpAuth(\IRequestObject $requestObject) {
		return true;
	}
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
        echo "hallo Welt";
        die;
	}
}
?>
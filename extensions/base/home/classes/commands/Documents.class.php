<?php
namespace Home\Commands;
class Documents extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		header("Location: " . PATH_URL . "explorer/");
		die;
	}
}
?>
<?php
namespace SignIn\Commands;
class Request extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	
	public function isGuestAllowed(\IRequestObject $iRequestObject) {
		return true;
	}
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$command = new SignIn();
		
		if (!empty($this->params)) {
			$requestUrl = "/";
			foreach ($this->params as $param) {
				$requestUrl .= $param . "/";
			}
			$command->setRequest($requestUrl);
		}

		return $command->frameResponse($frameResponseObject);
	}
}
?>
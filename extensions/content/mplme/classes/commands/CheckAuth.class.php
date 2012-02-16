<?php
namespace Mplme\Commands;
class CheckAuth extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;
	
	public function httpAuth(\IRequestObject $requestObject) {
		return true;
	}

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		//$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		//$obj->check_access_read();
		echo $this->id . " ok";
		die;
	}

}
?>
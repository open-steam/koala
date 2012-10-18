<?php
namespace Webarena\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$myExtension = \Webarena::getInstance();

		$obj = \steam_factory::get_object($GLOBALS[ "STEAM" ]->get_id(), $this->id);

		if ($obj->get_attribute("isWebarena") == 1) {

			$host = WEBARENA_HOST;
			$port = WEBARENA_PORT;

			if ($host == "localhost") {
				$host = $_SERVER['HTTP_HOST'];
			}

			header("Location: http://".$host.":".$port."/room/".$this->id);
		}

		return $frameResponseObject;
	}
}
?>

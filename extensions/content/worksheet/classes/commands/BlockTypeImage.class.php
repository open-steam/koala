<?php
namespace Worksheet\Commands;
class BlockTypeImage extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $type;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->type = $this->params[0]: "";
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {

		header('Content-type: image/jpeg');
		
		$filename = dirname(__FILE__)."/../../blocks/".$this->type."/preview.jpg";
		
		readfile($filename);

		die();
		
		return $frameResponseObject;
		
	}
}
?>
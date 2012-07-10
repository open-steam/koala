<?php
namespace AsciiSvgGenerator\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
                //do nothing
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		//include something
                //header
                echo "funky svg graphic";
                die;
	}
}
?>
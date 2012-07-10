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
		$pathSvgImg = PATH_EXTENSIONS . "content/asciiSvgGenerator/asset/asciisvg/svgimg.php";
                if(file_exists($pathSvgImg)){
                    echo "funky svg";
                    include($pathSvgImg);
                }else{
                    echo "asciisvg not found";
                }
                die;
	}
}
?>
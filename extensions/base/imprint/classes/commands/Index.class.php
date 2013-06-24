<?php
namespace Imprint\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {
	
	public function isGuestAllowed(\IRequestObject $requestObject) {
		return true;
	}
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
            
                if(defined("IMPRINT_LINK")){
                    header( 'Location: ' . IMPRINT_LINK);
                    die;
                }  else {
                    $rawHtml = new \Widgets\RawHtml();
                    $rawHtml->setHtml("<center>Impressum wird in Kürze eingebunden.</center>");
                    $frameResponseObject->setTitle("Impressum");
                    $frameResponseObject->addWidget($rawHtml);
                    return $frameResponseObject;
                }
        }
}
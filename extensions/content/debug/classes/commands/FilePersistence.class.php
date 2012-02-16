<?php
namespace Debug\Commands;
class FilePersistence extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		if (!defined("FILE_PERSISTENCE") || !FILE_PERSISTENCE) {
			$html = "FILE_PERSISTENCE must be <b>true</b>";
		} else {
			$html = "FILE_PERSISTENCE is enabled.<br>";
			$html .= "FILE_PERSISTENCE_PATH is " . FILE_PERSISTENCE_PATH . ".<br><br>Create a new document in your home \"filePersistenceTest.txt\".";
			$doc = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "filePersistenceTest.txt", "I am a file on file system.", "text/plain");
			$home = $GLOBALS["STEAM"]->get_current_steam_user()->get_workroom();
			$doc->move($home);
			
			
		}
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>
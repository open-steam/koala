<?php
namespace Debug\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$filePersistence = PATH_URL . "debug/filePersistence/";
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml(<<<END
<h1>Platform Tests</h1>
<a href="{$filePersistence}">Testing File-Persistence.</a>
END
);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>
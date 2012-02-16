<?php
namespace Webdav\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	private $id;
	
	public function httpAuth(\IRequestObject $requestObject) {
		return true;
	}
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
		$root = array(
		    new \SteamDavDirectory($GLOBALS["STEAM"]->get_current_steam_user()->get_workroom()),
		    new \Sabre_DAV_SimpleDirectory("Lesezeichen"),
		    new \Sabre_DAV_SimpleDirectory("Gruppen"),
		    new \Sabre_DAV_SimpleDirectory("Kurse")
		);
		
		$server = new \Sabre_DAV_Server($root);
		
		$server->setBaseUri("/webdav/index/");
		// Support for html frontend
		$browser = new \Sabre_DAV_Browser_Plugin();
		$server->addPlugin($browser);
		// And off we go!
		$server->exec();
		exit;
	}
/*		$filePersistence = PATH_URL . "debug/filePersistence/";
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml(<<<END
<h1>Platform Tests</h1>
<a href="{$filePersistence}">Testing File-Persistence.</a>
END
);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}*/
}
?>
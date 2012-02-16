<?php
namespace School\Commands;
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
		if (isset($this->id)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			if ($object instanceof \steam_exit) {
				$object = $object->get_exit();
				$this->id = $object->get_id();
			}
		} else {
			$object = \School\Model\FolderSchoolBookmark::getSchoolBookmarkFolderObject();
			$this->id = $object->get_id();
		}
		
		if ($object && $object instanceof \steam_container) {
			$objects = $object->get_inventory();
		} else {
			$objects = array();
		}
		
		$this->getExtension()->addJS();
		
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array(array("name"=>"Schul-Lesezeichenordner")));
		
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(array("name"=>"Neues Lesezeichen", "link"=>"#"), array("name"=>"Ordner anlegen", "link"=>"#")));
		
	//	$actionBar->setActions(array(array("name"=>"Neues Lesezeichen", "ajax"=>array("onclick"=>array("command"=>"newBookmark", "params"=>array("id"=>$this->id), "requestType"=>"popup"))), array("name"=>"Ordner anlegen", "ajax"=>array("onclick"=>array("command"=>"createFolder", "params"=>array("id"=>$this->id), "requestType"=>"popup")))));
		//$actionBar->setActions(array(array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newelement"))), array("name"=>"Eigenschaften", "link"=>PATH_URL."explorer/properties/"), array("name"=>"Rechte", "link"=>PATH_URL."explorer/rights/")));
		
		$loader = new \Widgets\Loader();
		$loader->setWrapperId("schoolBookmarksWrapper");
		$loader->setMessage("Lade meine Schule...");
		$loader->setCommand("loadSchoolBookmarks");
		$loader->setParams(array("id"=>$this->id));
		$loader->setElementId("schoolBookmarksWrapper");
		$loader->setType("updater");

	
		$frameResponseObject->setTitle("Meine Schule");
		$frameResponseObject->setHeadline(array(array("name" => "DIESE SEITE FUNTIONIERT NACH DEM NÄCHSTEN UPDATE.")));
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($loader);
		return $frameResponseObject;
	}
}
?>
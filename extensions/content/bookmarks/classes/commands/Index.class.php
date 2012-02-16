<?php
namespace Bookmarks\Commands;
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
		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		if (isset($this->id)) {
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			if ($object instanceof \steam_exit) {
				$object = $object->get_exit();
				$this->id = $object->get_id();
			}
		} else {
			$object = $currentUser->get_attribute("USER_BOOKMARKROOM");
			$this->id = $object->get_id();
		}


		if ($object && $object instanceof \steam_container) {
			$objects = $object->get_inventory();
		} else {
			$objects = array();
		}

		$this->getExtension()->addJS();
		$this->getExtension()->addCSS();
		$title = "Lesezeichen";
		$bookmarkParentFolderId = $currentUser->get_attribute("USER_BOOKMARKROOM")->get_id();
		if($this->id != $bookmarkParentFolderId){
			$title.=" - ".$object->get_name();
		}
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array("", array("name" => "<img src=\"".PATH_URL."explorer/asset/icons/mimetype/reference_folder.png\"></img> " . $title . " ")));
		
		//$bookmarkIcon = \Bookmarks::getInstance()->getAssetUrl() . "icons/bookmark.png";
		//$breadcrumb = new \Widgets\Breadcrumb();
		//$breadcrumb->setData(array(array("name"=>"<img src=\"{$bookmarkIcon}\"> Lesezeichenordner")));

		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(array("name"=>"Ordner anlegen", "ajax"=>array("onclick"=>array("command"=>"newElement", "params"=>array("id"=>$this->id), "requestType"=>"popup")))));
		//$actionBar->setActions(array(array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newelement"))), array("name"=>"Eigenschaften", "link"=>PATH_URL."explorer/properties/"), array("name"=>"Rechte", "link"=>PATH_URL."explorer/rights/")));

		$loader = new \Widgets\Loader();
		$loader->setWrapperId("bookmarksWrapper");
		$loader->setMessage("Lade Lesezeichen ...");
		$loader->setCommand("loadBookmarks");
		$loader->setParams(array("id"=>$this->id));
		$loader->setElementId("bookmarksWrapper");
		$loader->setType("updater");


		$frameResponseObject->setTitle("Lesezeichen");
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($breadcrumb);
		$frameResponseObject->addWidget($loader);
		return $frameResponseObject;
	}
}
?>
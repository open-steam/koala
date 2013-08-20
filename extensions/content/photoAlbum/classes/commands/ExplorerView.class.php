<?php
namespace PhotoAlbum\Commands;

class ExplorerView extends \AbstractCommand implements \IFrameCommand {
	
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
			$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
			$object = $currentUser->get_workroom();
			$this->id = $object->get_id();
		}
		
		if (!$object instanceof \steam_object) {
			\ExtensionMaster::getInstance()->send404Error();
			die;
		}
		
		$objectModel = \AbstractObjectModel::getObjectModel($object);
		
		if ($object && $object instanceof \steam_container) {
			$count = $object->count_inventory();
			if ($count > 500) {
				die ("Es befinden sich $count Objekte in diesem Ordner. Das Laden ist nicht möglich.");
			}
			$objects = $object->get_inventory();
		} else {
			$objects = array();
		}
		$title = getCleanName($object);
		
		$parent = $object->get_environment();
		if ($parent instanceof \steam_container) {
			//$parentLink = array("name"=>"nach oben", "link"=>PATH_URL . "explorer/Index/" . $parent->get_id() . "/");
			$parentLink = "";
		} else {
			$parentLink = "";
		}
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array($parentLink, array("name" => "<img src=\"".PATH_URL."explorer/asset/icons/mimetype/".deriveIcon($object)."\"></img> " . $title . " " . \Explorer\Model\Sanction::getMarkerHtml($object, false))));
		
		$this->getExtension()->addJS();
		$this->getExtension()->addCSS();
		
		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(array("name"=> "Galerie-Ansicht", "link" => PATH_URL."photoAlbum/index/".$this->id."/"),array("name"=>"Neues Bild", "ajax"=>array("onclick"=>array("command"=>"Addpicture", "params"=>array("id"=>$this->id), "requestType"=>"popup"))),array("name"=>"Eigenschaften", "ajax"=>array("onclick"=>array("command"=>"Properties", "params"=>array("id"=>$this->id), "requestType"=>"popup", "namespace"=>"explorer"))), array("name"=>"Rechte", "ajax"=>array("onclick"=>array("command"=>"Sanctions", "params"=>array("id"=>$this->id), "requestType"=>"popup", "namespace"=>"explorer")))));
		//$actionBar->setActions(array(array("name"=>"Neu", "ajax"=>array("onclick"=>array("command"=>"newelement"))), array("name"=>"Eigenschaften", "link"=>PATH_URL."explorer/properties/"), array("name"=>"Rechte", "link"=>PATH_URL."explorer/rights/")));
		
		
		
		$environment = new \Widgets\RawHtml();
		$environment->setHtml("<input type=\"hidden\" id=\"environment\" name=\"environment\" value=\"{$this->id}\">");
		
		$loader = new \Widgets\Loader();
		$loader->setWrapperId("explorerWrapper");
		$loader->setMessage("Lade Dokumente ...");
		$loader->setCommand("loadContent");
		$loader->setParams(array("id"=>$this->id));
		$loader->setElementId("explorerWrapper");
		$loader->setType("updater");
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml("<div id=\"explorerContent\">".$breadcrumb->getHtml().$environment->getHtml().$loader->getHtml()."</div>");
		
		$rawHtml->addWidget($breadcrumb);
		$rawHtml->addWidget($environment);
		$rawHtml->addWidget($loader);
	
		$frameResponseObject->setTitle($title);
		$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;
	}
}
?>
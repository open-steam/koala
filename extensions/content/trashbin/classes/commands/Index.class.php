<?php
namespace Trashbin\Commands;

class Index extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
			$object = $currentUser->get_trashbin();
			$this->id = $object->get_id();
		}

		if ($object && $object instanceof \steam_container) {
			$objects = $object->get_inventory();
		} else {
			$objects = array();
		}

		$this->getExtension()->addJS();
		$this->getExtension()->addCSS();

		$title = "Papierkorb";

		$actionBar = new \Widgets\ActionBar();
		$actionBar->setActions(array(array("name"=>"Papierkorb leeren", "ajax"=>array("onclick"=>array("command"=>"EmptyTrashbin", "params"=>array(), "requestType"=>"popup", "namespace"=>"explorer")))));

		$loader = new \Widgets\Loader();
		$loader->setWrapperId("trashbinWrapper");
		$loader->setMessage("Lade Daten ...");
		$loader->setCommand("loadContent");
		$loader->setParams(array("id"=>$this->id));
		$loader->setElementId("trashbinWrapper");
		$loader->setType("updater");


		$frameResponseObject->setTitle($title);

		$frameResponseObject->addWidget($actionBar);
			
		$frameResponseObject->addWidget($loader);
		
		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;

	}
}



?>
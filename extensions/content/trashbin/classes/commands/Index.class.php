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
		//chronic
		\ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentOther("trashbin");

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
		$breadcrumb = new \Widgets\Breadcrumb();
		$breadcrumb->setData(array("", array("name" => "<svg style='width:16px; height:16px; color:#3a6e9f;'><use xlink:href='" .  PATH_URL . "explorer/asset/icons/trashbin.svg#trashbin'/></svg>" . $title)));

		//$actionBar = new \Widgets\ActionBar();
		//$actionBar->setActions(array(array("name"=>"Papierkorb leeren", "ajax"=>array("onclick"=>array("command"=>"EmptyTrashbin", "params"=>array(), "requestType"=>"popup", "namespace"=>"explorer")))));

		$loader = new \Widgets\Loader();
		$loader->setWrapperId("trashbinWrapper");
		$loader->setMessage("Lade gelöschte Objekte...");
		$loader->setParams(array("id"=>$this->id));
		$loader->setNamespace("Trashbin");
		$loader->setElementId("trashbinWrapper");
		$loader->setType("updater");

		//check the explorer view attribute which is specified in the profile
		$viewAttribute = $currentUser->get_attribute("EXPLORER_VIEW");
		if($viewAttribute && $viewAttribute == "gallery"){
			$loader->setCommand("loadGalleryContent");
			$selectAll = new \Widgets\RawHtml();
			$selectAll->setHtml("<div id='selectAll' style='float:right; margin-right:22px;'><p style='float:left; margin-top:1px;'>Alle auswählen: </p><input onchange='elements = jQuery(\".galleryEntry > input\"); for (i=0; i<elements.length; i++) { if (this.checked != elements[i].checked) { elements[i].click() }}' type='checkbox'></div>");
			$frameResponseObject->addWidget($selectAll);
		}
		else{
			$loader->setCommand("loadContent");
		}

		$frameResponseObject->setTitle($title);
		$frameResponseObject->addWidget($breadcrumb);
		//$frameResponseObject->addWidget($actionBar);
		$frameResponseObject->addWidget($loader);

		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;

	}
}



?>

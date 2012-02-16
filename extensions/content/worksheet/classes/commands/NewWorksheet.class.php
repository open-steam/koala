<?php
namespace Worksheet\Commands;
class NewWorksheet extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");

		$env_room = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		$worksheetObject= \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $this->params["name"], $env_room);
		 
		$worksheet = new \Worksheet\Worksheet($worksheetObject->get_id());
		$worksheet->setup();
		
		$worksheet->setName($worksheet->getName()." (Vorlage)");
		
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		closeDialog();
		sendRequest("LoadContent", {"id":"{$this->id}"}, "explorerWrapper", "updater", null, null, "explorer");
		
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>
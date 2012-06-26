<?php
namespace Wiki\Commands;
class Create extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$Wiki = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $this->params["title"], $container, $this->params["title"]);
		$Wiki->set_attribute("OBJ_TYPE", "container_wiki_koala");
		
		$ajaxResponseObject->setStatus("ok");
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
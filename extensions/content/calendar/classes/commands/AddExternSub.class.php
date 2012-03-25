<?php
namespace Calendar\Commands;
class AddExternSub extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand{

	private $params;
	private $id;
	private $name;
	private $url;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
			
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			$array = explode("/", $this->params["path"]);
			$this->id = $array[2];	
			$this->url= $this->params["url"];
			$this->name=$this->params["name"];		
		}
		$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$this->id);

		if(!($obj instanceof \steam_room || $obj->get_attribute("OBJ_TYPE") == "calendar")){
			throw new \Exception("$calendar is not a Calendar!");
		}
			
		$url = $this->url;
		$name = $this->name;
		
		$newObj = \steam_factory::create_docextern($GLOBALS["STEAM"]->get_id(), $name, $url, $obj); ;

		$subscriptions = array();
		$subscriptions = $obj->get_attribute("CALENDAR_SUBSCRIPTIONS");
		$subscriptions[count($subscriptions)]=$newObj;
		$obj->set_attribute("CALENDAR_SUBSCRIPTIONS", $subscriptions);

	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jsWrapper = new \Widgets\JSWrapper();
		$jsWrapper->setPostJsCode("closeDialog();return false;");
		$ajaxResponseObject->addWidget($jsWrapper);
		return $ajaxResponseObject;

	}
}
?>
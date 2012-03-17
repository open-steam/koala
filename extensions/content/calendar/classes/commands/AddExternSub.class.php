<?php
namespace Calendar\Commands;
class AddExternSub extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand{

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
		$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(),$this->id);

		if(!($obj instanceof \steam_room || $obj->get_attribute("OBJ_TYPE") == "calendar")){
			throw new \Exception("$calendar is not a Calendar!");
		}
			
		$url = "http://www.feiertage.de/";
		$name = "Feiertage";
		$newObj = \steam_factory::create_docextern($GLOBALS["STEAM"]->get_id(), $name, $url, $obj); ;

		$subscriptions = array();
		$subscriptions = $obj->get_attribute("CALENDAR_SUBSCRIPTIONS");
		$subscriptions[count($subscriptions)]=$newObj;
		$obj->set_attribute("CALENDAR_SUBSCRIPTIONS", $subscriptions);
		return $requestObject;

	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;

	}
}
?>
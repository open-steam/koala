<?php
namespace Calendar\Commands;
class DeleteEvent extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;

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
		$obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		if($obj instanceof \steam_date){
			$obj->delete();
		}else{
			throw new \Exception("object isn't type of steam_date");
		}
		

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

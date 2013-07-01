<?php
namespace Gallery\Commands;

class Addpicture extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	
	//public function getExtension() {
	//	return \DocumentObject::getInstance();
	//}
	
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
		$ajaxDialog = new \Widgets\Dialog();
		$ajaxDialog->setTitle("Neue Bilder hinzufügen");
		$ajaxUploader = new \Widgets\AjaxUploader();
                $ajaxUploader->setSizeLimit(return_bytes(ini_get('post_max_size')));
		$ajaxUploader->setNamespace("explorer");
		$ajaxUploader->setDestId($this->id);
		$ajaxDialog->addWidget($ajaxUploader);
		$ajaxDialog->setForceReload(true);
		$ajaxResponseObject->addWidget($ajaxDialog);
		return $ajaxResponseObject;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	}
}
?>
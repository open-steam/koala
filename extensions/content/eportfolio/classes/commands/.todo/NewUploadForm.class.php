<?php
namespace Portfolio\Commands;

class NewUploadForm extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	
	public function getExtension() {
		return \DocumentObject::getInstance();
	}
	
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
		$current_container = \ArtefactModel::getArtefactsContainer();
		
		$ajaxUploader = new \Widgets\AjaxUploader();
		//$ajaxUploader->setBackend(PATH_URL . "explorer/");
		$ajaxUploader->setEnvId($current_container->get_id());
	
		$ajaxResponseObject->addWidget($ajaxUploader);
		return $ajaxResponseObject;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		
	}
}
?>
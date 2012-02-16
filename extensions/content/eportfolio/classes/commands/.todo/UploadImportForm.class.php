<?php
namespace Portfolio\Commands;

class UploadImportForm extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $params;
	private $id;

	public function getExtension() {
		return \Artefact::getInstance();
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


		$ajaxUploader = new \Widgets\AjaxUploader();
		$ajaxUploader->setSizeLimit(return_bytes(ini_get('post_max_size')));
		$ajaxUploader->setBackend(PATH_URL . "portfolio/");
		$ajaxUploader->setEnvId($this->id);
		$ajaxUploader->setCommand("UploadImport");
		$ajaxUploader->setNamespace("Portfolio");

		$ajaxResponseObject->addWidget($ajaxUploader);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>
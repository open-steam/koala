<?php
namespace Explorer\Commands;

class NewDocumentForm extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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


		$ajaxUploader = new \Widgets\AjaxUploader();
		$ajaxUploader->setSizeLimit(return_bytes(ini_get('post_max_size')));
		$ajaxUploader->setNamespace("Explorer");
		$ajaxUploader->setDestId($this->id);

		$rawHTML = new \Widgets\RawHtml();
		$rawHTML->setHtml("<div><input id=\"override-cb\" type=\"checkbox\" /> Gleichnamige Dateien ersetzen</div><div style=\"float:right\"><a class=\"bidButton\" onclick=\"closeDialog();window.location.reload();return false;\" href=\"#\">Schließen</a></div>");

		$ajaxResponseObject->addWidget($ajaxUploader);
		$ajaxResponseObject->addWidget($rawHTML);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>

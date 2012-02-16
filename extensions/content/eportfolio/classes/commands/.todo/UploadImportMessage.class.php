<?php

namespace Portfolio\Commands;
class UploadImpoertMessage extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {

	private $params;
	private $id;
	private $content;
	private $dialog;

	public function getExtension() {
		return \Artefact::getInstance();
	}

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		//		$objectId = $params["messageObjectId"];
		//		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		//var_dump($params);

		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Portfolio Importieren");

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);

		$ajaxUploader = new \Widgets\AjaxUploader();
		$imgWidget = new \Widgets\RawHtml();
		$imgWidget->setHtml("Um ein Portfolio zu importieren ziehen sie die Datei auf dieses Feld oder doppelklicken sie hier.<br>");
		//$ajaxUploader->setPreview($imgWidget);
		//		}
		$ajaxUploader->setSizeLimit(return_bytes(ini_get('post_max_size')));
		$ajaxUploader->setNamespace("Portfolio");
		$ajaxUploader->setCommand("UploadImport");
		$ajaxUploader->setDestId($params["id"]);
		$ajaxUploader->setMultiUpload(false);
		//		$ajaxUploader->setOnComplete("function(id, fileName, responseJSON){document.getElementById('uploaderArtefact').src = '" . PATH_URL . "download/document/' + responseJSON.oid; jQuery('#uploaderArtefact').addClass('saved')}");
		$ajaxUploader->setOnComplete("function(id, fileName, responseJSON){location.reload();}");
		
		$dialog->addWidget($ajaxUploader);
		//		$dialog->addWidget($raw);

		$dialog->addWidget(new \Widgets\Clearer());


		$this->dialog = $dialog;
	}

	public function idResponse(\IdResponseObject $idResponseObject) {
		$idResponseObject->setContent($this->content);
		return $idResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle("Portal");
		$frameResponseObject->setContent($this->content);
		return $frameResponseObject;
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>
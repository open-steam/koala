<?php
namespace Portfolio\Commands;
class NewArtefactForm extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Erstelle ein neues Artefakt");
		
		$textInput = new \Widgets\TextInput();
		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->addWidget($textInput);
		$ajaxForm->setSubmitCommand("CreateArtefactCertificate");
		$ajaxForm->setSubmitNamespace("Portfolio");

		$ajaxForm->setHtml(<<<END
	<div id="wizard">
		<input type="hidden" name="id" value="{$this->id}">
		<div class="widgets_lable">Titel:</div>
		<div class="widgets_textinput"><input type="text" value="" name="name"></div><br clear="all">
		<div class="widgets_lable">Beschreibung:</div>
		<div class="widgets_textinput"><input type="text" value="" name="desc"></div><br clear="all">
	</div>
END
		);
		$dialog->addWidget($ajaxForm);
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>
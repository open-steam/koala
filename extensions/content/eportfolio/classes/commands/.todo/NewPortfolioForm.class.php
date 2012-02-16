<?php
namespace Portfolio\Commands;
class NewPortfolioForm extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $params;
	private $id;

	public function getExtension() {
		return \PortfolioObject::getInstance();
		//return \Portfolio::getInstance();
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
		$dialog->setTitle("Erstelle ein neues Portfolio");
		
		$textInput = new \Widgets\TextInput();
		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->addWidget($textInput);
		$ajaxForm->setSubmitCommand("CreatePortfolio");
		$ajaxForm->setSubmitNamespace("Portfolio");

		$ajaxForm->setHtml(<<<END
	<div id="wizard_wrapper">
	<input type="hidden" name="id" value="{$this->id}">
	<div class="widgets_lable">Name:</div>
	<div class="widgets_textinput"><input type="text" value="" name="name"></div><br clear="all">
	<div class="widgets_lable">Beschreibung:</div>
	<div class="widgets_textinput"><input type="text" value="" name="desc"></div><br clear="all">
	</div>
END
		);
		$dialog->addWidget($ajaxForm);
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;




		/*
		 $ajaxUploader = new \Widgets\AjaxUploader();
		 $ajaxUploader->setSizeLimit(return_bytes(ini_get('post_max_size')));
		 $ajaxUploader->setBackend(PATH_URL . "explorer/");
		 $ajaxUploader->setEnvId($this->id);
		 //ROLF
		 $ajaxUploader->setCommand("Upload");
		 $ajaxUploader->setNamespace("Portfolio");
		 $ajaxResponseObject->addWidget($ajaxUploader);
		 */
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>
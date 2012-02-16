<?php
namespace Portfolio\Commands;
class NewArtefactForm2 extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$dialog->setCloseButtonLabel(null);

		$submitCommand = "CreateArtefact";
		$submitNamespace = "Portfolio";
		$html = "<div id=\"wizard\" style=\"margin-left: 20px; margin-right: 20px\">";
		$html .=
		<<<END
	<form id="ajaxform" onsubmit="sendAjaxFrom(); return false;">
		<input type="hidden" name="id" value="{$this->id}">
		<div class="widgets_lable">Titel:</div>
		<div class="widgets_textinput"><input type="text" value="" name="name"></div><br clear="all">
		<div class="widgets_lable">Beschreibung:</div>
		<div class="widgets_textinput"><input type="text" value="" name="desc"></div><br clear="all">
	</form>
	<script>
	function sendAjaxFrom() {
		form = formToObject("ajaxform");
		sendRequest("{$submitCommand}", form, "wizard", "wizard", null, null, "{$submitNamespace}"); 
	}
	</script>
END
		;
		$html .= "<div style=\"float:right\"><a href=\"#\" class=\"button pill left\" onclick=\"sendAjaxFrom(); return false;\"><b>Weiter</b></a><a class=\"button pill negative\" onclick=\"closeDialog();return false;\" href=\"#\">Abbrechen</a></div></div>";

		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($html);
		$dialog->addWidget($rawHtml);
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;

	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>
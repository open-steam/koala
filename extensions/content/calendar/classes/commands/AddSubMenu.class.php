<?php
namespace Calendar\Commands;
class AddSubMenu extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand{

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



	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		return $frameResponseObject;
	}
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxForm = new \Widgets\AjaxForm();
		$ajaxForm->setHtml(<<<END
	<div class="widgets_lable">ID der Extension, deren Kalender abonniert werden soll:</div>
	<div class="widgets_textinput"><input type="text" value="" name="id"></div><br clear="all">
	<input type="hidden" name="calendar" value="{$this->id}">
	
END
		);

		$ajaxForm->setSubmitNamespace("Calendar");
		$ajaxForm->setSubmitCommand("AddSubscription");
		$dialog = new \Widgets\Dialog();
		$dialog->addWidget($ajaxForm);

		$ajaxResponseObject->setStatus("ok");
		$dialog->setCloseButtonLabel(null);
		$ajaxResponseObject->addWidget($dialog);
		//$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;

	}

}
?>

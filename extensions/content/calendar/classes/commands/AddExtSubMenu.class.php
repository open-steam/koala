<?php
namespace Calendar\Commands;
class AddExtSubMenu extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand{

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
	<div class="widgets_lable">URL der Terminquelle:</div>
	<div class="widgets_textinput"><input type="text" value="" name="url"></div><br clear="all">
	<div class="widgets_lable">Name der Terminquelle:</div>
	<div class="widgets_textinput"><input type="text" value="" name="name"></div><br clear="all">
	
END
		);

		$ajaxForm->setSubmitNamespace("Calendar");
		$ajaxForm->setSubmitCommand("AddExternSub");
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
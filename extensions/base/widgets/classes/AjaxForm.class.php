<?php
namespace Widgets;

class AjaxForm extends Widget {
	private $html;
	private $submitCommand;
	private $submitNamespace = "";
	
	public function setHtml($html) {
		$this->html = $html;
	}
	
	public function setSubmitCommand($submitCommand) {
		$this->submitCommand = $submitCommand;
	}
	
	public function setSubmitNamespace($submitNamespace) {
		$this->submitNamespace = $submitNamespace;
	}
	
	public function getHtml() {
		if ($this->html) {
			$this->getContent()->setCurrentBlock("AJAX_FORM");
			$this->getContent()->setVariable("AJAX_FORM_NAME", "ajaxform");
			$this->getContent()->setVariable("AJAX_FORM_CONTENT", $this->html);
			$this->getContent()->setVariable("AJAX_SENDCODE", "form = formToObject('ajaxform'); sendRequest('{$this->submitCommand}', form, 'wizard_wrapper', 'wizard', null, null, '{$this->submitNamespace}');");
			$this->getContent()->parse("AJAX_FORM");
			return $this->getContent()->get();
		} else {
			return "";
		}
	}
}
?>
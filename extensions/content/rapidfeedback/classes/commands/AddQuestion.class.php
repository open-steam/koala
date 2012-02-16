<?php
namespace Rapidfeedback\Commands;
class AddQuestion extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		
		switch (intval($this->params["questionType"])) {
			case 0:
				$newquestion = new \Rapidfeedback\Model\TextQuestion();
				break;
		}
		
		$newquestion->setQuestionText(rawurldecode($this->params["questionText"]));
		$newquestion->setHelpText(rawurldecode($this->params["questionHelp"]));
		$newquestion->setRequired(intval($this->params["questionRequired"]));
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($newquestion->getEditHTML($this->params["questionID"]));
			
		$ajaxResponseObject->addWidget($rawHtml);
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	}
}
?>
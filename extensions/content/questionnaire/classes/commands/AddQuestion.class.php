<?php
namespace Questionnaire\Commands;
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
				$newquestion = new \Questionnaire\Model\TextQuestion();
				$newquestion->setInputLength(intval($this->params["questionInputLength"]));
				break;
			case 1:
				$newquestion = new \Questionnaire\Model\TextAreaQuestion();
				$newquestion->setRows(intval($this->params["questionRows"]));
				break;
			case 2:
				$newquestion = new \Questionnaire\Model\SingleChoiceQuestion();
				foreach ($this->params["options"] as $option) {
					$newquestion->addOption(rawurldecode($option));
				}
				$newquestion->setArrangement($this->params["questionArrangement"]);
				break;
			case 3:
				$newquestion = new \Questionnaire\Model\MultipleChoiceQuestion();
        foreach ($this->params["options"] as $option) {
					$newquestion->addOption(rawurldecode($option));
				}
				$newquestion->setArrangement($this->params["questionArrangement"]);
				break;
			case 4:
				$newquestion = new \Questionnaire\Model\MatrixQuestion();
				foreach ($this->params["columns"] as $column) {
					$newquestion->addColumn(rawurldecode($column));
				}
				foreach ($this->params["rows"] as $row) {
					$newquestion->addRow(rawurldecode($row));
				}
				break;
			case 5:
				$newquestion = new \Questionnaire\Model\GradingQuestion();
				foreach ($this->params["rows"] as $row) {
					$newquestion->addRow(rawurldecode($row));
				}
				break;
			case 6:
				$newquestion = new \Questionnaire\Model\TendencyQuestion();
				$count = 0;
				foreach ($this->params["options"] as $option) {
					if ($count == 0) {
						$options = array();
						$options[0] = rawurldecode($option);
						$count++;
					} else {
						$options[1] = rawurldecode($option);
						$count = 0;
						$newquestion->addOption($options);
					}
				}
				$newquestion->setSteps($this->params["questionArrangement"]);
				break;
		}
		$newquestion->setQuestionText(rawurldecode($this->params["questionText"]));

		$newquestion->setHelpText(rawurldecode($this->params["questionHelp"]));

		$newquestion->setRequired(intval($this->params["questionRequired"]));

		$rawHtml = new \Widgets\RawHtml();

		$rawHtml->setHtml($newquestion->getEditHTML($this->id, $this->params["questionID"]));

		$ajaxResponseObject->addWidget($rawHtml);
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	}
}
?>

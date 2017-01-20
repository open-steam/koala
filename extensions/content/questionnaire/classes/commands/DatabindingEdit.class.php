<?php
namespace Questionnaire\Commands;

class DatabindingEdit extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $survey;
	private $sortables;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->sortables = $this->params["sortables"];
		$this->survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");

		$questionnaire = $this->survey->get_environment();
		$survey_object = new \Questionnaire\Model\Survey($questionnaire);

		$questioncounter = 0;
		$sortedQuestions = $this->sortables;
		$sortedQuestions != '' ? ($sortedQuestions = explode(',', $sortedQuestions)) : '';
		foreach ($sortedQuestions as $question) {
				if ($question != "newquestion" && $question != "newlayout" && $question != "") {
						if (isset($this->params[$question])) {
								$questionValues = $this->params[$question];
								$questionValues != '' ? ($questionValues = explode(',', $questionValues)) : '';
								if (isset($questionValues[0])) {
										switch ($questionValues[0]) {
												case 0:
														$newquestion = new \Questionnaire\Model\TextQuestion();
														$newquestion->setInputLength($questionValues[4]);
														break;
												case 1:
														$newquestion = new \Questionnaire\Model\TextareaQuestion();
														$newquestion->setRows($questionValues[4]);
														break;
												case 2:
														$newquestion = new \Questionnaire\Model\SingleChoiceQuestion();
														$options = $this->params[$question . "_options"];
														$options != '' ? ($options = explode(',', $options)) : '';
														foreach ($options as $option) {
																$newquestion->addOption(rawurldecode($option));
														}
														$newquestion->setArrangement($questionValues[4]);
														break;
												case 3:
														$newquestion = new \Questionnaire\Model\MultipleChoiceQuestion();
														$options = $this->params[$question . "_options"];
														$options != '' ? ($options = explode(',', $options)) : '';
														foreach ($options as $option) {
																$newquestion->addOption(rawurldecode($option));
														}
														$newquestion->setArrangement($questionValues[4]);
														break;
												case 4:
														$newquestion = new \Questionnaire\Model\MatrixQuestion();
														$columns = $this->params[$question . "_columns"];
														$columns != '' ? ($columns = explode(',', $columns)) : '';
														foreach ($columns as $column) {
																$newquestion->addcolumn(rawurldecode($column));
														}
														$rows = $this->params[$question . "_rows"];
														$rows != '' ? ($rows = explode(',', $rows)) : '';
														foreach ($rows as $row) {
																$newquestion->addRow(rawurldecode($row));
														}
														break;
												case 5:
														$newquestion = new \Questionnaire\Model\GradingQuestion();
														$options = $this->params[$question . "_rows"];
														$options != '' ? ($options = explode(',', $options)) : '';
														foreach ($options as $option) {
																$newquestion->addRow(rawurldecode($option));
														}
														break;
												case 6:
														$newquestion = new \Questionnaire\Model\TendencyQuestion();
														$options = $this->params[$question . "_options"];
														$options != '' ? ($options = explode(',', $options)) : '';
														$newquestion->setSteps($questionValues[4]);
														for ($count = 0; $count < count($options); $count = $count + 2) {
																$newquestion->addOption(array(rawurldecode($options[$count]), rawurldecode($options[$count + 1])));
														}
														break;
												case 7:
														$newquestion = new \Questionnaire\Model\DescriptionLayoutElement();
														$newquestion->setDescription(rawurldecode($questionValues[1]));
														break;
												case 8:
														$newquestion = new \Questionnaire\Model\HeadlineLayoutElement();
														$newquestion->setHeadline(rawurldecode($questionValues[1]));
														break;
												case 9:
														$newquestion = new \Questionnaire\Model\PageBreakLayoutElement();
														break;
												case 10:
														$newquestion = new \Questionnaire\Model\JumpLabel();
														$newquestion->setText(rawurldecode($questionValues[1]));
														$newquestion->setTo(rawurldecode($questionValues[2]));
														break;
										}

										if ($questionValues[0] < 7) {
												$newquestion->setQuestionText(rawurldecode($questionValues[1]));
												$newquestion->setHelpText(rawurldecode($questionValues[2]));
												$newquestion->setRequired($questionValues[3]);
										}

										$survey_object->addQuestion($newquestion);
								}
						}
				}
		}
		$survey_object->createSurvey(intval($this->id));
		//$frameResponseObject->setConfirmText("Änderungen erfolgreich gespeichert.");

		return $ajaxResponseObject;
	}

}
?>

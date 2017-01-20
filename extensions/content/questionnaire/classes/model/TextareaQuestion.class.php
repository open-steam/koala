<?php
namespace Questionnaire\Model;
class TextareaQuestion extends AbstractQuestion {
	protected $rows = 4;

	function __construct($question = null) {
		if ($question != null) {
			$this->questionText = $question->questiontext;
			$this->helpText = $question->helptext;
			$this->required = $question->required;
			$this->rows = $question->rows;
		}
	}

	public function saveXML($question) {
		$question->addChild("type", 1);
		$question->addChild("questiontext", $this->questionText);
		$question->addChild("helptext", $this->helpText);
		$question->addChild("required", $this->required);
		$question->addChild("rows", $this->rows);
		return $question;
	}

	public function setRows($rows) {
		if (is_numeric($rows)) {
			$this->rows = $rows;
		} else {
			$this->rows = 4;
		}
	}

	public function getRows() {
		return $this->rows;
	}

	public function setResults($results) {
		$this->results = $results;
	}

	function getEditHTML($questionnaireId, $id, $number = -1) {
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/textareaquestion.template.html");
		$content->setCurrentBlock("BLOCK_EDIT");
    if($number != -1){
      $content->setVariable("NUMBER", $number);
    }
		$content->setVariable("ELEMENT_ID", $id);
		if ($this->required == 1) {
			$content->setVariable("QUESTION_TEXT", $this->questionText . " (Pflichtfrage)");
		} else {
			$content->setVariable("QUESTION_TEXT", $this->questionText);
		}
		$content->setVariable("HELP_TEXT", $this->helpText);
		$data = "1," . rawurlencode($this->questionText) . "," . rawurlencode($this->helpText) . "," . $this->required . "," . $this->rows;
		$content->setVariable("ELEMENT_DATA", $data);
		$content->setVariable("ELEMENT_ROWS", $this->rows);

		$popupMenu = new \Widgets\PopupMenu();
		$popupMenu->setCommand("GetPopupMenuEdit");
		$popupMenu->setNamespace("Questionnaire");
		$popupMenu->setData(\steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $questionnaireId));
		$popupMenu->setElementId("edit-overlay");
		$popupMenu->setParams(array(array("key" => "questionId", "value" => $id)));
		$content->setVariable("POPUPMENUANKER", $popupMenu->getHtml());

		$content->parse("BLOCK_EDIT");
		return $content->get();
	}

	function getViewHTML($id, $disabled, $error, $input = "") {
		if ($input == -1) $input = "";

		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/textareaquestion.template.html");
		$content->setCurrentBlock("BLOCK_VIEW");
		$content->setVariable("ELEMENT_ID", $id);
		if ($error == 1) {
			$content->setVariable("ERROR_BORDER", "border: 2px solid red;");
		}
		if ($this->required == 1) {
			$content->setVariable("QUESTION_TEXT", '<span id="'. ($id+1) .'">'. ($id+1) . '</span>' . ". " . $this->questionText . " (Pflichtfrage)");
		} else {
			$content->setVariable("QUESTION_TEXT", '<span id="'. ($id+1) .'">'. ($id+1) . '</span>' . ". " . $this->questionText);
		}
		$content->setVariable("HELP_TEXT", $this->helpText);
		$content->setVariable("ELEMENT_INPUT", $input);
		if ($disabled == 1) {
			$content->setVariable("QUESTION_DISABLED", "disabled");
		}
		$content->setVariable("ELEMENT_ROWS", $this->rows);
		$content->parse("BLOCK_VIEW");
		return $content->get();
	}

	function getResultHTML($id) {
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/textareaquestion.template.html");
		if (count($this->results) == 0) {
			$content->setCurrentBlock("BLOCK_NO_RESULTS");
			$content->setVariable("QUESTION_TEXT", $id . ". " . $this->questionText);
			$content->setVariable("NO_RESULTS", "Keine Antworten zu dieser Frage vorhanden.");
			$content->parse("BLOCK_NO_RESULTS");
		} else {
			$content->setCurrentBlock("BLOCK_RESULTS");
			$content->setVariable("QUESTION_TEXT", $id . ". " . $this->questionText);
			foreach ($this->results as $result) {
				$content->setCurrentBlock("BLOCK_RESULT");
				$content->setVariable("RESULT_TEXT", $result);
				$content->parse("BLOCK_RESULT");
			}
			$content->parse("BLOCK_RESULTS");
		}
		return $content->get();
	}

	function getIndividualResult($result) {
		return array($result);
	}
}
?>

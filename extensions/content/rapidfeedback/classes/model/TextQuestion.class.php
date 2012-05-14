<?php
namespace Rapidfeedback\Model;
class TextQuestion extends AbstractQuestion {
	protected $inputLength = 0;
	
	function __construct($question = null) {
		if ($question != null) {
			$this->questionText = $question->questiontext;
			$this->helpText = $question->helptext;
			$this->required = $question->required;
			$this->inputLength = $question->inputlength;
		}
	}
	
	public function saveXML($question) {
		$question->addChild("type", 0);
		$question->addChild("questiontext", $this->questionText);
		$question->addChild("helptext", $this->helpText);
		$question->addChild("required", $this->required);
		$question->addChild("inputlength", $this->inputLength);
		return $question;
	}
	
	public function setInputLength($inputLength) {
		if (is_numeric($inputLength)) {
			$this->inputLength = $inputLength;
		} else {
			$this->inputLength = 0;
		}
	}
	
	public function getInputLength($inputLength) {
		return $this->inputLength;
	}
	
	public function setResults($results) {
		$this->results = $results;
	}
	
	function getEditHTML($id) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/textquestion.template.html");
		$content->setCurrentBlock("BLOCK_EDIT");
		$content->setVariable("ELEMENT_ID", $id);
		$content->setVariable("ASSETURL", $RapidfeedbackExtension->getAssetUrl() . "icons/");
		$content->setVariable("EDIT_LABEL", "Bearbeiten");
		$content->setVariable("COPY_LABEL", "Kopieren");
		$content->setVariable("DELETE_LABEL", "Löschen");
		if ($this->required == 1) {
			$content->setVariable("QUESTION_TEXT", $this->questionText . " (Pflichtfrage)");
		} else {
			$content->setVariable("QUESTION_TEXT", $this->questionText);
		}
		$content->setVariable("HELP_TEXT", $this->helpText);
		$data = "0," . rawurlencode($this->questionText) . "," . rawurlencode($this->helpText) . "," . $this->required . "," . $this->inputLength;
		$content->setVariable("ELEMENT_DATA", $data);
		$content->parse("BLOCK_EDIT");
		return $content->get();
	}
	
	function getViewHTML($id, $disabled, $error, $input = "") {
		if ($input == -1) $input = "";
		
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/textquestion.template.html");
		$content->setCurrentBlock("BLOCK_VIEW");
		$content->setVariable("ELEMENT_ID", $id);
		if ($error == 1) {
			$content->setVariable("ERROR_BORDER", "border-right-color:red;");
		}
		if ($this->required == 1) {
			$content->setVariable("QUESTION_TEXT", ($id+1) . ". " . $this->questionText . " (Pflichtfrage)");
		} else {
			$content->setVariable("QUESTION_TEXT", ($id+1) . ". " . $this->questionText);
		}
		$content->setVariable("HELP_TEXT", $this->helpText);
		$content->setVariable("ELEMENT_INPUT", $input);
		if ($disabled == 1) {
			$content->setVariable("QUESTION_DISABLED", "disabled");
		}
		if ($this->inputLength != 0) {
			$content->setVariable("ELEMENT_MAXLENGTH", $this->inputLength);
		}
		$content->parse("BLOCK_VIEW");
		return $content->get();
	}
	
	function getResultHTML($id) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/textquestion.template.html");
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
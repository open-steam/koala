<?php
namespace Rapidfeedback\Model;
class MultipleChoiceQuestion extends AbstractQuestion {
	protected $options = array();
	protected $resultCount = 0;
	protected $arrangement = 1;
	
	function __construct($question = null) {
		if ($question != null) {
			$this->questionText = $question->questiontext;
			$this->helpText = $question->helptext;
			$this->required = $question->required;
			$this->arrangement = $question->arrangement;
			foreach ($question->option as $option) {
				$this->addOption($option);
			}
		}
	}
	
	public function addOption($option) {
		array_push($this->options, $option);
	}
	
	public function getOptions() {
		return $this->options;
	}
	
	public function setArrangement($rows) {
		$this->arrangement = $rows;
	}
	
	public function setResults($results) {
		$this->results = array();
		for ($count = 0; $count < count($this->options); $count++) {
			$this->results[$count] = 0;
		}
		foreach ($results as $result) {
			for ($count = 0; $count < count($this->options); $count++) {
				if (in_array($count, $result)) {
					$this->results[$count] = ($this->results[$count])+1;
				}
			}
			$this->resultCount++;
		}
	}
	
	public function saveXML($question) {
		$question->addChild("type", 3);
		$question->addChild("questiontext", $this->questionText);
		$question->addChild("helptext", $this->helpText);
		$question->addChild("required", $this->required);
		$question->addChild("arrangement", $this->arrangement);
		foreach ($this->options as $option) {
			$question->addChild("option", $option);
		}
		return $question;
	}
	
	function getEditHTML($id) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/multiplechoicequestion.template.html");
		$content->setCurrentBlock("BLOCK_EDIT");
		$content->setVariable("ELEMENT_ID", $id);
		$content->setVariable("ASSETURL", $RapidfeedbackExtension->getAssetUrl() . "icons/");
		$content->setVariable("EDIT_LABEL", "Bearbeiten");
		$content->setVariable("COPY_LABEL", "Kopieren");
		$content->setVariable("DELETE_LABEL", "Löschen");
		if ($this->required == 1) {
			$content->setCurrentBlock("BLOCK_EDIT_REQUIRED");
			$content->setVariable("QUESTION_TEXT", $this->questionText);
			$content->setVariable("HELP_TEXT", $this->helpText);
			$content->parse("BLOCK_EDIT_REQUIRED");
		} else {
			$content->setCurrentBlock("BLOCK_EDIT_NOT_REQUIRED");
			$content->setVariable("QUESTION_TEXT", $this->questionText);
			$content->setVariable("HELP_TEXT", $this->helpText);
			$content->parse("BLOCK_EDIT_NOT_REQUIRED");
		}
		$options = "";
		$counter = 0;
		foreach ($this->options as $option) {
			$content->setCurrentBlock("BLOCK_EDIT_OPTION");
			$content->setVariable("QUESTION_ID", $id);
			$content->setVariable("OPTION_COUNT", $counter);
			$content->setVariable("OPTION_LABEL", $option);
			$content->parse("BLOCK_EDIT_OPTION");
			$options = $options . rawurlencode($option) . ",";
			$counter++;
		}
		$options = substr($options, 0, strlen($options)-1);
		$data = "3," . rawurlencode($this->questionText) . "," . rawurlencode($this->helpText) . "," . $this->required . "," . $this->arrangement;
		$content->setVariable("ELEMENT_DATA", $data);
		$content->setVariable("OPTION_DATA", $options);
		$content->parse("BLOCK_EDIT");
		return $content->get();
	}
	
	function getViewHTML($id, $error, $input = array()) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/multiplechoicequestion.template.html");
		$content->setCurrentBlock("BLOCK_VIEW");
		if ($error == 1) {
			$content->setVariable("ERROR_BORDER", "border-right-color:red;");
		}
		if ($this->required == 1) {
			$content->setCurrentBlock("BLOCK_VIEW_REQUIRED");
			$content->setVariable("QUESTION_TEXT", ($id+1) . ". " . $this->questionText);
			$content->parse("BLOCK_VIEW_REQUIRED");
		} else {
			$content->setCurrentBlock("BLOCK_VIEW_NOT_REQUIRED");
			$content->setVariable("QUESTION_TEXT", ($id+1) . ". " . $this->questionText);
			$content->parse("BLOCK_VIEW_NOT_REQUIRED");
		}
		$content->setVariable("HELP_TEXT", $this->helpText);
		
		$counter = 0;
		foreach ($this->options as $option) {
			if ($counter == $input) {
				$content->setCurrentBlock("BLOCK_OPTION_SELECTED");
				$content->setVariable("QUESTION_ID", $id);
				$content->setVariable("OPTION_COUNT", $counter);
				$content->setVariable("OPTION_LABEL", $option);
				if ((($counter+1) % $this->arrangement) == 0) {
					$content->setVariable("INSERT_BR", "<br>");
				}
				$content->parse("BLOCK_OPTION_SELECTED");
			} else {
				$content->setCurrentBlock("BLOCK_OPTION_VIEW");
				$content->setVariable("QUESTION_ID", $id);
				$content->setVariable("OPTION_COUNT", $counter);
				$content->setVariable("OPTION_LABEL", $option);
				if ((($counter+1) % $this->arrangement) == 0) {
					$content->setVariable("INSERT_BR", "<br>");
				}
				$content->parse("BLOCK_OPTION_VIEW");
			}
			$counter++;
		}
		$content->parse("BLOCK_VIEW");
		return $content->get();
	}
	
	function getResultHTML($id) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/multiplechoicequestion.template.html");
		$content->setCurrentBlock("BLOCK_RESULTS");
		$content->setVariable("QUESTION_TEXT", $id . ". " . $this->questionText);
		
		$counter = 0;
		foreach ($this->options as $option) {
			$content->setCurrentBlock("BLOCK_RESULTS_OPTION");
			$content->setVariable("OPTION_LABEL", $option);
			$content->setVariable("OPTION_RESULT", $this->results[$counter]);
			$content->setVariable("OPTION_PERCENT", round((($this->results[$counter] / $this->resultCount)*100),1));
			$content->parse("BLOCK_RESULTS_OPTION");
			$counter++;
		}
		
		$content->setVariable("QUESTION_ID", $id);
		$content->setVariable("OPTION_COUNT", count($this->options));
		$counter = 0;
		foreach ($this->options as $option) {
			$content->setCurrentBlock("BLOCK_SCRIPT_OPTION");
			$content->setVariable("OPTION_SCRIPT_LABEL", $option);
			$content->setVariable("OPTION_COUNTER", $counter);
			$content->setVariable("OPTION_SCRIPT_RESULT", round((($this->results[$counter] / $this->resultCount)*100),1));
			$content->parse("BLOCK_SCRIPT_OPTION");
			$counter++;
		}
		
		$content->parse("BLOCK_RESULTS");
		return $content->get();
	}
}
?>
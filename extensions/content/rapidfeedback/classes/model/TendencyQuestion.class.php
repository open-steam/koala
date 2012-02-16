<?php
namespace Rapidfeedback\Model;
class TendencyQuestion extends AbstractQuestion {
	protected $options = array();
	protected $steps = 0;
	 
	function __construct($question = null) {
		if ($question != null) {
			$this->questionText = $question->questiontext;
			$this->helpText = $question->helptext;
			$this->required = $question->required;
			$this->steps = $question->steps;
			foreach ($question->option as $option) {
				$this->addOption(array($option->first, $option->second));
			}
		}
	}
	
	public function addOption($option) {
		array_push($this->options, $option);
	}
	
	public function getOptions() {
		return $this->options;
	}
	
	public function saveXML($question) {
		$question->addChild("type", 6);
		$question->addChild("questiontext", $this->questionText);
		$question->addChild("helptext", $this->helpText);
		$question->addChild("required", $this->required);
		$question->addChild("steps", $this->steps);
		foreach ($this->options as $option) {
			$option_tag = $question->addChild("option");
			$option_tag->addChild("first", $option[0]);
			$option_tag->addChild("second", $option[1]);
		}
		return $question;
	}
	
	public function setSteps($steps) {
		$this->steps = $steps;
	}
	
	function getEditHTML($id) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/tendencyquestion.template.html");
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
			$content->setCurrentBlock("BLOCK_OPTION_EDIT");
			$content->setVariable("LABEL_OPTION1", $option[0]);
			$content->setVariable("LABEL_OPTION2", $option[1]);
			for ($count = 0; $count < $this->steps; $count++) {
				$content->setCurrentBlock("BLOCK_EDIT_STEP");
				$content->setVariable("QUESTION_ID", $id);
				$content->setVariable("STEP_COUNTER", $counter);
				$content->parse("BLOCK_EDIT_STEP");
			}
			$content->parse("BLOCK_OPTION_EDIT");
			$options = $options . rawurlencode($option[0]) . "," . rawurlencode($option[1]) . ",";
			$counter++;
		}
		$options = substr($options, 0, strlen($options)-1);
		
		$data = "6," . rawurlencode($this->questionText) . "," . rawurlencode($this->helpText) . "," . $this->required . "," . $this->steps;
		$content->setVariable("ELEMENT_DATA", $data);
		$content->setVariable("OPTION_DATA", $options);
		$content->parse("BLOCK_EDIT");
		return $content->get();
	}
	
	function getViewHTML($id, $error, $input = -1) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/tendencyquestion.template.html");
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
			$content->setCurrentBlock("BLOCK_OPTION_VIEW");
			$content->setVariable("OPTION_VIEW1", $option[0]);
			$content->setVariable("OPTION_VIEW2", $option[1]);
			for ($count = 0; $count < $this->steps; $count++) {
				$content->setCurrentBlock("BLOCK_STEP_VIEW");
				$content->setVariable("QUESTION_ID", $id);
				$content->setVariable("STEP_COUNTER", $counter);
				$content->setVariable("STEP_VALUE", $count);
				$content->parse("BLOCK_STEP_VIEW");
			}
			$content->parse("BLOCK_OPTION_VIEW");
			$counter++;
		}
		
		$content->parse("BLOCK_VIEW");
		return $content->get();
	}
}
?>
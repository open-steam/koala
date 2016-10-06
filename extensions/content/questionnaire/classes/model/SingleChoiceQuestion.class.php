<?php
namespace Questionnaire\Model;
class SingleChoiceQuestion extends AbstractQuestion {
	protected $options = array();
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
			$this->results[$result] = ($this->results[$result])+1;
		}
	}

	public function saveXML($question) {
		$question->addChild("type", 2);
		$question->addChild("questiontext", $this->questionText);
		$question->addChild("helptext", $this->helpText);
		$question->addChild("required", $this->required);
		$question->addChild("arrangement", $this->arrangement);
		foreach ($this->options as $option) {
			$question->addChild("option", $option);
		}
		return $question;
	}

	function getEditHTML($id , $number = -1) {
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/singlechoicequestion.template.html");
		$content->setCurrentBlock("BLOCK_EDIT");
                if($number != -1){
                    $content->setVariable("NUMBER", $number);
                }
		$content->setVariable("ELEMENT_ID", $id);
		$content->setVariable("ASSETURL", $QuestionnaireExtension->getAssetUrl() . "icons/");
		$content->setVariable("EDIT_LABEL", "Bearbeiten");
		$content->setVariable("COPY_LABEL", "Kopieren");
		$content->setVariable("DELETE_LABEL", "Löschen");
		if ($this->required == 1) {
			$content->setVariable("QUESTION_TEXT", $this->questionText . " (Pflichtfrage)");
		} else {
			$content->setVariable("QUESTION_TEXT", $this->questionText);
		}
		$content->setVariable("HELP_TEXT", $this->helpText);
		$options = "";
		$counter = 0;
		foreach ($this->options as $option) {
			if ((($counter) % $this->arrangement) == 0 || $counter == 0) {
				$content->setCurrentBlock("BLOCK_ROW_VIEW");
			}
			$content->setCurrentBlock("BLOCK_COLUMN_VIEW");
			$content->setCurrentBlock("BLOCK_OPTION_VIEW");
			$content->setVariable("QUESTION_ID", $id);
			$content->setVariable("OPTION_LABEL", $option);
			$content->parse("BLOCK_OPTION_VIEW");
			$content->parse("BLOCK_COLUMN_VIEW");
			if ((($counter+1) % $this->arrangement) == 0) {
				$content->parse("BLOCK_ROW_VIEW");
			}
			$options = $options . rawurlencode($option) . ",";
			$counter++;
		}
		$options = substr($options, 0, strlen($options)-1);
		$data = "2," . rawurlencode($this->questionText) . "," . rawurlencode($this->helpText) . "," . $this->required . "," . $this->arrangement;
		$content->setVariable("ELEMENT_DATA", $data);
		$content->setVariable("OPTION_DATA", $options);
		$content->parse("BLOCK_EDIT");
		return $content->get();
	}

	function getViewHTML($id, $disabled, $error, $input = -1) {
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/singlechoicequestion.template.html");
		$content->setCurrentBlock("BLOCK_VIEW");
		if ($error == 1) {
			$content->setVariable("ERROR_BORDER", "border-right-color:red;");
		}
		if ($this->required == 1) {
			$content->setVariable("QUESTION_TEXT", '<span id="'. ($id+1) .'">'. ($id+1) . '</span>' . ". " . $this->questionText . " (Pflichtfrage)");
		} else {
			$content->setVariable("QUESTION_TEXT", '<span id="'. ($id+1) .'">'. ($id+1) . '</span>' . ". " . $this->questionText);
		}
		$content->setVariable("HELP_TEXT", $this->helpText);

		$counter = 0;
		foreach ($this->options as $option) {
			if ((($counter) % $this->arrangement) == 0 || $counter == 0) {
				$content->setCurrentBlock("BLOCK_ROW_VIEW");
			}
			$content->setCurrentBlock("BLOCK_COLUMN_VIEW");
			$content->setCurrentBlock("BLOCK_OPTION_VIEW");
			$content->setVariable("QUESTION_ID", $id);
			$content->setVariable("OPTION_LABEL", $option);
			$content->setVariable("QUESTION_COUNTER", $counter);
			if ($counter == $input) {
				$content->setVariable("OPTION_CHECKED", "checked");
			}
			if ($disabled == 1) {
				$content->setVariable("QUESTION_DISABLED", "disabled");
			}
			$content->parse("BLOCK_OPTION_VIEW");
			$content->parse("BLOCK_COLUMN_VIEW");
			if ((($counter+1) % $this->arrangement) == 0) {
				$content->parse("BLOCK_ROW_VIEW");
			}
			$counter++;
		}
		$content->parse("BLOCK_VIEW");
		return $content->get();
	}

	function getResultHTML($id) {
		$resultCount = 0;
		foreach ($this->results as $result) {
			$resultCount = $resultCount + $result;
		}

		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/singlechoicequestion.template.html");
		if ($resultCount == 0) {
			$content->setCurrentBlock("BLOCK_NO_RESULTS");
			$content->setVariable("QUESTION_TEXT", $id . ". " . $this->questionText);
			$content->setVariable("NO_RESULTS", "Keine Antworten zu dieser Frage vorhanden.");
			$content->parse("BLOCK_NO_RESULTS");
		} else {
			$content->setCurrentBlock("BLOCK_RESULTS");
			$content->setVariable("QUESTION_TEXT", $id . ". " . $this->questionText);
			$content->setVariable("POSSIBLE_ANSWER_LABEL", "Antwortmöglichkeit");
			$content->setVariable("POSSIBLE_ANSWER_AMOUNT", "Antworten");
			$content->setVariable("POSSIBLE_ANSWER_PERCENT", "% der Befragten");

			$counter = 0;
			foreach ($this->options as $option) {
				$content->setCurrentBlock("BLOCK_RESULTS_OPTION");
				if($counter % 2 != 0) {
					$content->setVariable("ROW_STYLE", "style='background-color:white;'");
				}
				$content->setVariable("OPTION_LABEL", $option);
				$content->setVariable("OPTION_RESULT", $this->results[$counter]);
				if ($resultCount != 0) {
					$content->setVariable("OPTION_PERCENT", round((($this->results[$counter] / $resultCount)*100),1));
				} else {
					$content->setVariable("OPTION_PERCENT", 0);
				}
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
				$content->setVariable("OPTION_SCRIPT_RESULT", $this->results[$counter]);
				$content->parse("BLOCK_SCRIPT_OPTION");
				$counter++;
			}

			// calculate statistics
			$counter = 0;
			$resultArray = array();
			for ($count = 0; $count < count($this->options); $count++) {
				for ($count2 = 0; $count2 < $this->results[$count]; $count2++){
					$resultArray[$counter] = $count+1;
					$counter++;
				}
			}
			// arithmetic mean
			$mw = 0;
			for ($count = 0; $count < count($resultArray); $count++) {
				$mw = $mw + $resultArray[$count];
			}
			$mw = round(($mw / $resultCount),1);
			// median
			$md = 0;
			if ($resultCount % 2 == 0) {
				$md = 0.5 * ($resultArray[($resultCount / 2) - 1] + $resultArray[$resultCount / 2]);
			} else {
				$md = $resultArray[(($resultCount+1) / 2)-1];
			}
			// standard deviation
			$s = 0;
			for ($count = 0; $count < count($resultArray); $count++) {
				$s = $s + ($resultArray[$count] - $mw) * ($resultArray[$count] - $mw);
			}
			$s = round(sqrt($s), 1);
			$content->setVariable("QUESTION_STATS", "Anzahl: " . $resultCount . " Mittelwert: " . $mw . " Median: " . $md . " Standardabweichung: " . $s);
			$content->parse("BLOCK_RESULTS");
		}
		return $content->get();
	}

	function getIndividualResult($result) {
		return array($this->options[$result]);
	}
}
?>

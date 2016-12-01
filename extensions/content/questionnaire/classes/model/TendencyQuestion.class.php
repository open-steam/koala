<?php
namespace Questionnaire\Model;
class TendencyQuestion extends AbstractQuestion {
	protected $options = array();
	protected $steps = 0;
	protected $resultCount = array();
	protected $allResults = 0;

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

	public function setResults($results) {
		$this->results = array();
		for ($count = 0; $count < count($this->options); $count++) {
			$this->results[$count] = array();
			for ($count2 = 0; $count2 < $this->steps; $count2++) {
				$this->results[$count][$count2] = 0;
			}
			$this->resultCount[$count] = 0;
		}
		foreach ($results as $result) {
			for ($count = 0; $count < count($this->options); $count++) {
				if ($result[$count] != -1) {
					$this->results[$count][$result[$count]] = ($this->results[$count][$result[$count]])+1;
					$this->allResults++;
					$this->resultCount[$count]++;
				}
			}
		}
	}

	function getEditHTML($questionnaireId, $id, $number = -1) {
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/tendencyquestion.template.html");
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

	function getViewHTML($id, $disabled, $error, $input = array()) {
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/tendencyquestion.template.html");
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
			$content->setCurrentBlock("BLOCK_OPTION_VIEW");
			$content->setVariable("OPTION_VIEW1", $option[0]);
			$content->setVariable("OPTION_VIEW2", $option[1]);
			for ($count = 0; $count < $this->steps; $count++) {
				$content->setCurrentBlock("BLOCK_STEP_VIEW");
				$content->setVariable("QUESTION_ID", $id);
				$content->setVariable("OPTION_COUNTER", $counter);
				$content->setVariable("STEP_VALUE", $count);
				if (isset($input[$counter]) && $input[$counter] == $count) {
					$content->setVariable("STEP_CHECKED", "checked");
				}
				if ($disabled == 1) {
					$content->setVariable("QUESTION_DISABLED", "disabled");
				}
				$content->parse("BLOCK_STEP_VIEW");
			}
			$content->parse("BLOCK_OPTION_VIEW");
			$counter++;
		}

		$content->parse("BLOCK_VIEW");
		return $content->get();
	}

	function getResultHTML($id) {
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/tendencyquestion.template.html");
		if ($this->allResults == 0) {
			$content->setCurrentBlock("BLOCK_NO_RESULTS");
			$content->setVariable("QUESTION_TEXT", $id . ". " . $this->questionText);
			$content->setVariable("NO_RESULTS", "Keine Antworten zu dieser Frage vorhanden.");
			$content->parse("BLOCK_NO_RESULTS");
		} else {
			$content->setCurrentBlock("BLOCK_RESULTS");
			$content->setVariable("QUESTION_TEXT", $this->questionText);
			$content->setVariable("QUESTION_ID", $id);
			$content->setVariable("STATS_LABEL", "Statistik");
			for ($count = 0; $count < $this->steps; $count++) {
				$content->setCurrentBlock("BLOCK_FIRST_ROW_STEP");
				$content->setVariable("FIRST_ROW_ID", $id);
				//$content->setVariable("FIRST_ROW_COUNTER", $count+1);
				$content->parse("BLOCK_FIRST_ROW_STEP");
			}
			$counter = 0;
			foreach ($this->options as $option) {
				$content->setCurrentBlock("BLOCK_RESULTS_ROW");
				if($counter % 2 != 0) {
					$content->setVariable("ROW_STYLE", "style='background-color:white;'");
				}
				else{
					$content->setVariable("ROW_STYLE", "style='background-color:#CFCFCF'");
				}
				$content->setVariable("ROW_LABEL1", $option[0]);
				$content->setVariable("ROW_LABEL2", $option[1]);
				for ($count = 0; $count < $this->steps; $count++) {
					$content->setCurrentBlock("BLOCK_RESULTS_ROW_ELEMENT");
					if ($this->resultCount[$counter] != 0) {
						$content->setVariable("RESULT_ELEMENT", $this->results[$counter][$count] . " (" . round(($this->results[$counter][$count] / $this->resultCount[$counter])*100, 1) . "%)");
					} else {
						$content->setVariable("RESULT_ELEMENT", "0 (0%)");
					}
					$content->parse("BLOCK_RESULTS_ROW_ELEMENT");
				}
				if ($this->resultCount[$counter] > 0) {
					// calculate statistics
					$countsteps = 0;
					$resultArray = array();
					for ($count = 0; $count < $this->steps; $count++) {
						for ($count2 = 0; $count2 < $this->results[$counter][$count]; $count2++){
							$resultArray[$countsteps] = $count+1;
							$countsteps++;
						}
					}
					// arithmetic mean
					$mw = 0;
					for ($count = 0; $count < count($resultArray); $count++) {
						$mw = $mw + $resultArray[$count];
					}
					$mw = round(($mw / $this->resultCount[$counter]),1);
					// median
					$md = 0;
					if ($this->resultCount[$counter] % 2 == 0) {
						$md = 0.5 * ($resultArray[($this->resultCount[$counter] / 2) - 1] + $resultArray[$this->resultCount[$counter] / 2]);
					} else {
						$md = $resultArray[(($this->resultCount[$counter]+1) / 2)-1];
					}
					// standard deviation
					$s = 0;
					for ($count = 0; $count < count($resultArray); $count++) {
						$s = $s + ($resultArray[$count] - $mw) * ($resultArray[$count] - $mw);
					}
					$s = round(sqrt($s), 1);
				} else {
					$n = 0;
					$mw = 0;
					$md = 0;
					$s = 0;
				}
				//$tipsy = new \Widgets\Tipsy();
				//$tipsy->setElementId("tipsy" . $id . "_" . $counter);
				//$tipsy->setHtml("<div>n = " . $this->resultCount[$counter] . "<br>mw = " . $mw . "<br>md = " . $md . "<br>s = " . $s . "</div>");
				//$content->setVariable("TIPSY_ID", "tipsy" . $id . "_" . $counter);
				//$content->setVariable("TIPSY_HTML", "<script>" . $tipsy->getHtml() . "</script>");
				$content->setVariable("STATS", "Anzahl: " . $this->resultCount[$counter] . " Mittelwert: " . $mw . " Median: " . $md . " Standardabweichung: " . $s);
				$content->parse("BLOCK_RESULTS_ROW");
				$counter++;
			}

			for ($count = 0; $count < count($this->options); $count = $count+2) {
				$content->setCurrentBlock("BLOCK_CHART_ROW");
				$content->setVariable("CHART_ID", $id);
				$content->setVariable("CHART_COUNTER", $count);
				$content->setVariable("CHART_COUNTER2", $count+1);
				$content->parse("BLOCK_CHART_ROW");
			}

			$counter = 0;
			foreach ($this->options as $option) {
				if ($this->resultCount[$counter] > 0) {
					$content->setCurrentBlock("BLOCK_CHART_SCRIPT");
					$content->setVariable("CHART_ID_SCRIPT", $id);
					$content->setVariable("COUNTER_ID", $counter);
					$content->setVariable("OPTION_COUNT", $this->steps);
					$content->setVariable("CHART_TITLE", $option[0] . " - " . $option[1]);
					for ($count = 0; $count < $this->steps; $count++) {
						$content->setCurrentBlock("BLOCK_CHART_SCRIPT_OPTION");
						$content->setVariable("OPTION_COUNTER", $count);
						$content->setVariable("OPTION_LABEL", ($count+1));
						$content->setVariable("OPTION_RESULT", $this->results[$counter][$count]);
						$content->parse("BLOCK_CHART_SCRIPT_OPTION");
					}
					$content->parse("BLOCK_CHART_SCRIPT");
				}
				$counter++;
			}
			$content->parse("BLOCK_RESULTS");
		}
		return $content->get();
	}

	function getIndividualResult($result) {
		$return = array();
		for ($count = 0; $count < count($this->options); $count++) {
			if ($result[$count] != -1) {
				$return[$count] = $result[$count]+1;
			} else {
				$return[$count] = "";
			}
		}
		return $return;
	}
}
?>

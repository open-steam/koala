<?php
namespace Questionnaire\Model;
class MatrixQuestion extends AbstractQuestion {
	protected $rows = array();
	protected $columns = array();
	protected $resultCount = array();
	protected $allResults = 0;
	protected $type = 4;

	function __construct($question = null) {
		if ($question != null) {
			$this->questionText = $question->questiontext;
			$this->helpText = $question->helptext;
			$this->required = $question->required;
			foreach ($question->row as $row) {
				$this->addRow($row);
			}
			foreach ($question->column as $column) {
				$this->addColumn($column);
			}
		}
	}

	public function addRow($row) {
		array_push($this->rows, $row);
	}

	public function getRows() {
		return $this->rows;
	}

	public function addColumn($column) {
		array_push($this->columns, $column);
	}

	public function getColumns() {
		return $this->columns;
	}

	public function setResults($results) {
		$this->results = array();
		for ($count = 0; $count < count($this->rows); $count++) {
			$this->results[$count] = array();
			for ($count2 = 0; $count2 < count($this->columns); $count2++) {
				$this->results[$count][$count2] = 0;
			}
			$this->resultCount[$count] = 0;
		}
		foreach ($results as $result) {
			for ($count = 0; $count < count($this->rows); $count++) {
				if (isset($result[$count]) && $result[$count] != -1) {
					$this->results[$count][$result[$count]] = ($this->results[$count][$result[$count]])+1;
					$this->resultCount[$count]++;
					$this->allResults++;
				}
			}
		}
	}

	public function saveXML($question) {
		$question->addChild("type", $this->type);
		$question->addChild("questiontext", $this->questionText);
		$question->addChild("helptext", $this->helpText);
		$question->addChild("required", $this->required);
		foreach ($this->rows as $row) {
			$question->addChild("row", $row);
		}
		if ($this->type == 4) {
			foreach ($this->columns as $column) {
				$question->addChild("column", $column);
			}
		}
		return $question;
	}

	function getEditHTML($questionnaireId, $id, $number = -1) {
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/matrixquestion.template.html");
		$content->setCurrentBlock("BLOCK_EDIT");
		$content->setVariable("ELEMENT_ID", $id);
    if($number != -1){
      $content->setVariable("NUMBER", $number);
    }
		if ($this->required == 1) {
			$content->setVariable("QUESTION_TEXT", $this->questionText . " (Pflichtfrage)");
		} else {
			$content->setVariable("QUESTION_TEXT", $this->questionText);
		}
		$content->setVariable("HELP_TEXT", $this->helpText);

		$columns = count($this->columns);
		$columnData = "";
		foreach ($this->columns as $column) {
			$content->setCurrentBlock("BLOCK_COLUMN_TITLE");
			$content->setVariable("COLUMN_LABEL", $column);
			$content->parse("BLOCK_COLUMN_TITLE");
			$columnData = $columnData . rawurlencode($column) . ",";
		}
		$columnData = substr($columnData, 0, strlen($columnData)-1);

		$rowData = "";
		$counter = 0;
		foreach ($this->rows as $row) {
			$content->setCurrentBlock("BLOCK_ROW");
			if (($counter % 2) == 0) {
				$content->setVariable("BG_COLOR", "#CFCFCF");
			} else {
				$content->setVariable("BG_COLOR", "#FFFFFF");
			}
			$content->setVariable("ROW_LABEL", $row);
			for ($count = 1; $count <= $columns; $count++) {
				$content->setCurrentBlock("BLOCK_ROW_ELEMENT");
				if (($counter % 2) == 0) {
					$content->setVariable("BG_COLOR_ELEMENT", "#CFCFCF");
				} else {
					$content->setVariable("BG_COLOR_ELEMENT", "#FFFFFF");
				}
				$content->setVariable("QUESTION_ID", $id);
				$content->setVariable("OPTION_COUNT", $counter);
				$content->parse("BLOCK_ROW_ELEMENT");
			}
			$content->parse("BLOCK_ROW");
			$rowData = $rowData . rawurlencode($row) . ",";
			$counter++;
		}
		$rowData = substr($rowData, 0, strlen($rowData)-1);

		$data = $this->type . "," . rawurlencode($this->questionText) . "," . rawurlencode($this->helpText) . "," . $this->required;
		$content->setVariable("ELEMENT_DATA", $data);
		$content->setVariable("COLUMN_DATA", $columnData);
		$content->setVariable("ROW_DATA", $rowData);

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
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/matrixquestion.template.html");
		$content->setCurrentBlock("BLOCK_VIEW");
		if ($error == 1) {
			$content->setVariable("ERROR_BORDER", "border: 2px solid red;");
		}
		if ($this->required == 1) {
			$content->setVariable("QUESTION_TEXT", '<span id="'. ($id+1) .'">'. ($id+1) . '</span>' . ". " . $this->questionText . " (Pflichtfrage)");
		} else {
			$content->setVariable("QUESTION_TEXT", '<span id="'. ($id+1) .'">'. ($id+1) . '</span>' . ". " . $this->questionText);
		}
		$content->setVariable("HELP_TEXT", $this->helpText);

		foreach ($this->columns as $column) {
			$content->setCurrentBlock("BLOCK_VIEW_COLUMN");
			$content->setVariable("COLUMN_LABEL", $column);
			$content->parse("BLOCK_VIEW_COLUMN");
		}
		$counter = 0;
		foreach ($this->rows as $row) {
			$bgcolor = "";
			$selected = -1;
			if (isset($input[$counter])) $selected = $input[$counter];
			$content->setCurrentBlock("BLOCK_VIEW_ROW");
			if (($counter % 2) == 0) {
				$content->setVariable("BG_COLOR", "#CFCFCF");
			} else {
				$content->setVariable("BG_COLOR", "#FFFFFF");
			}
			$content->setVariable("ROW_LABEL", $row);
			for ($count = 1; $count <= count($this->columns); $count++) {
				$content->setCurrentBlock("BLOCK_VIEW_ROW_ELEMENT");
				$content->setVariable("QUESTION_ID", $id);
				$content->setVariable("OPTION_COUNT", $counter);
				$content->setVariable("OPTION_VALUE", ($count-1));
				if (($counter % 2) == 0) {
					$content->setVariable("BG_COLOR_ELEMENT", "#CFCFCF");
					if ($selected+1 == $count) {
						$content->setVariable("QUESTION_CHECKED", "checked");
					}
				} else {
					$content->setVariable("BG_COLOR_ELEMENT", "#FFFFFF");
					if ($selected+1 == $count) {
						$content->setVariable("QUESTION_CHECKED", "checked");
					}
				}
				if ($disabled == 1) {
					$content->setVariable("QUESTION_DISABLED", "disabled");
				}
				$content->parse("BLOCK_VIEW_ROW_ELEMENT");
			}
			$content->parse("BLOCK_VIEW_ROW");
			$counter++;
		}
		$content->parse("BLOCK_VIEW");
		return $content->get();
	}

	function getResultHTML($id) {
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$content = $QuestionnaireExtension->loadTemplate("questiontypes/matrixquestion.template.html");

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

			foreach ($this->columns as $column) {
				$content->setCurrentBlock("BLOCK_RESULTS_COLUMN");
				$content->setVariable("COLUMN_LABEL", $column);
				$content->parse("BLOCK_RESULTS_COLUMN");
			}

			$counter = 0;
			foreach ($this->rows as $row) {
				$content->setCurrentBlock("BLOCK_RESULTS_ROW");
				if($counter % 2 != 0) {
					$content->setVariable("ROW_STYLE", "style='background-color:white;'");
				}
				else{
					$content->setVariable("ROW_STYLE", "style='background-color:#CFCFCF'");
				}
				$content->setVariable("ROW_LABEL", $row);
				for ($count = 0; $count < count($this->columns); $count++) {
					$content->setCurrentBlock("BLOCK_RESULTS_ROW_ELEMENT");
					if ($this->resultCount[$counter] == 0) {
						$content->setVariable("RESULT_ELEMENT", "0 (0%)");
					} else {
						$content->setVariable("RESULT_ELEMENT", $this->results[$counter][$count] .' (' . round((($this->results[$counter][$count] / $this->resultCount[$counter])*100),1) . '%)');
					}
					$content->parse("BLOCK_RESULTS_ROW_ELEMENT");
				}
				if ($this->resultCount[$counter] > 0) {
					// calculate statistics
					$countcolumns = 0;
					$resultArray = array();
					for ($count = 0; $count < count($this->columns); $count++) {
						for ($count2 = 0; $count2 < $this->results[$counter][$count]; $count2++){
							$resultArray[$countcolumns] = $count+1;
							$countcolumns++;
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
				$content->setVariable("STATS", "Anzahl: " . $this->resultCount[$counter] . " Mittelwert: " . $mw . " Median: " . $md . " Standardabweichung: " . $s);
				//$tipsy = new \Widgets\Tipsy();
				//$tipsy->setElementId("tipsy" . $id . "_" . $counter);
				//$tipsy->setHtml("<div>n = " . $this->resultCount[$counter] . "<br>mw = " . $mw . "<br>md = " . $md . "<br>s = " . $s . "</div>");
				//$content->setVariable("TIPSY_ID", "tipsy" . $id . "_" . $counter);
				//$content->setVariable("TIPSY_HTML", "<script>" . $tipsy->getHtml() . "</script>");
				$content->parse("BLOCK_RESULTS_ROW");
				$counter++;
			}
			for ($count = 0; $count < count($this->rows); $count = $count+2) {
				$content->setCurrentBlock("BLOCK_CHART_ROW");
				$content->setVariable("CHART_ID", $id);
				$content->setVariable("CHART_COUNTER", $count);
				$content->setVariable("CHART_COUNTER2", $count+1);
				$content->parse("BLOCK_CHART_ROW");
			}

			$counter = 0;
			foreach ($this->rows as $row) {
				if ($this->resultCount[$counter] > 0) {
					$content->setCurrentBlock("BLOCK_CHART_SCRIPT");
					$content->setVariable("CHART_ID_SCRIPT", $id);
					$content->setVariable("COUNTER_ID", $counter);
					$content->setVariable("OPTION_COUNT", count($this->columns));
					$content->setVariable("CHART_TITLE", $this->rows[$counter]);
					for ($count = 0; $count < count($this->columns); $count++) {
						$content->setCurrentBlock("BLOCK_CHART_SCRIPT_OPTION");
						$content->setVariable("OPTION_COUNTER", $count);
						$content->setVariable("OPTION_LABEL", $this->columns[$count]);
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
		for ($count = 0; $count < count($this->rows); $count++) {
			if (isset($result[$count]) && $result[$count] != -1) {
				$return[$count] = $this->columns[$result[$count]];
			} else {
				$return[$count] = "";
			}
		}
		return $return;
	}
}
?>

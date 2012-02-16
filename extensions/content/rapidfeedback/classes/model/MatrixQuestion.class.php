<?php
namespace Rapidfeedback\Model;
class MatrixQuestion extends AbstractQuestion {
	protected $rows = array();
	protected $columns = array();
	protected $resultCount;
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
		$this->resultCount = count($results);
		$this->results = array();
		for ($count = 0; $count < count($this->rows); $count++) {
			$this->results[$count] = array();
			for ($count2 = 0; $count2 < count($this->columns); $count2++) {
				$this->results[$count][$count2] = 0;
			}
		}
		foreach ($results as $result) {
			for ($count = 0; $count < count($this->rows); $count++) {
				if ($result[$count] != -1) {
					$this->results[$count][$result[$count]] = ($this->results[$count][$result[$count]])+1;
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
	
	function getEditHTML($id) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/matrixquestion.template.html");
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
		
		$columns = count($this->columns);
		$content->setVariable("ELEMENT_COLS", $columns+1);
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
			$content->setVariable("ROW_LABEL", $row);
			for ($count = 1; $count <= $columns; $count++) {
				$content->setCurrentBlock("BLOCK_ROW_ELEMENT");
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
		$content->parse("BLOCK_EDIT");
		return $content->get();
	}
	
	function getViewHTML($id, $error, $input = array()) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/matrixquestion.template.html");
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
				$content->parse("BLOCK_VIEW_ROW_ELEMENT");
			}
			$content->parse("BLOCK_VIEW_ROW");
			$counter++;
		}
		$content->parse("BLOCK_VIEW");
		return $content->get();
	}
	
	function getResultHTML($id) {
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$content = $RapidfeedbackExtension->loadTemplate("questiontypes/matrixquestion.template.html");
		$content->setCurrentBlock("BLOCK_RESULTS");
		$content->setVariable("QUESTION_TEXT", $id . ". " . $this->questionText);
		$content->setVariable("QUESTION_ID", $id);
		$content->setVariable("COL_WIDTH", (1/(count($this->columns)+1)));
		$content->setVariable("COL_SPAN", (count($this->columns)+1));
		
		foreach ($this->columns as $column) {
			$content->setCurrentBlock("BLOCK_RESULTS_COLUMN");
			$content->setVariable("COLUMN_LABEL", $column);
			$content->parse("BLOCK_RESULTS_COLUMN");
		}
		
		$counter = 0;
		foreach ($this->rows as $row) {
			$content->setCurrentBlock("BLOCK_RESULTS_ROW");
			$content->setVariable("ROW_LABEL", $row);
			for ($count = 0; $count < count($this->columns); $count++) {
				$content->setCurrentBlock("BLOCK_RESULTS_ROW_ELEMENT");
				$content->setVariable("RESULT_ELEMENT", $this->results[$counter][$count] .' (' . round((($this->results[$counter][$count] / $this->resultCount)*100),1) . '%)');
				$content->parse("BLOCK_RESULTS_ROW_ELEMENT");
			}
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
			$content->setCurrentBlock("BLOCK_CHART_SCRIPT");
			$content->setVariable("CHART_ID_SCRIPT", $id);
			$content->setVariable("COUNTER_ID", $counter);
			$content->setVariable("OPTION_COUNT", count($this->columns));
			$content->setVariable("CHART_TITLE", $this->rows[$counter]);
			for ($count = 0; $count < count($this->columns); $count++) {
				$content->setCurrentBlock("BLOCK_CHART_SCRIPT_OPTION");
				$content->setVariable("OPTION_COUNTER", $count);
				$content->setVariable("OPTION_LABEL", $this->columns[$count]);
				$content->setVariable("OPTION_RESULT", round((($this->results[$counter][$count] / $this->resultCount)*100), 1));
				$content->parse("BLOCK_CHART_SCRIPT_OPTION");
			}
			$content->parse("BLOCK_CHART_SCRIPT");
			$counter++;
		}

		$content->parse("BLOCK_RESULTS");
		return $content->get();
	}
}
?>
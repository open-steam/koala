<?php

namespace Questionnaire\Model;

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
                    $this->results[$count] = ($this->results[$count]) + 1;
                }
            }
            if (!empty($result)) {
                $this->resultCount++;
            }
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

    function getEditHTML($questionnaireId, $id, $number = -1) {

        $QuestionnaireExtension = \Questionnaire::getInstance();
        $content = $QuestionnaireExtension->loadTemplate("questiontypes/multiplechoicequestion.template.html");
        $content->setCurrentBlock("BLOCK_EDIT");
        if($number == -1){
          $number = 0;
        }
        $content->setVariable("NUMBER", $number);
        $content->setVariable("ELEMENT_ID", $id);
        if ($this->required == 1) {
            $content->setVariable("QUESTION_TEXT", $this->questionText . " (Pflichtfrage)");
        } else {
            $content->setVariable("QUESTION_TEXT", $this->questionText);
        }

        $content->setVariable("HELP_TEXT", $this->helpText);
        $options = "";
        if($this->arrangement == "horizontal"){
          $content->setCurrentBlock("BLOCK_ROW_VIEW");
        }
        foreach ($this->options as $option) {
          if ($this->arrangement == "vertikal"){
            $content->setCurrentBlock("BLOCK_ROW_VIEW");
          }
          $content->setCurrentBlock("BLOCK_COLUMN_VIEW");
          $content->setCurrentBlock("BLOCK_OPTION_VIEW");
          $content->setVariable("QUESTION_ID", $id);
          $content->setVariable("QUESTION_DISABLED", "disabled");
          $content->setVariable("OPTION_LABEL", $option);
          $content->parse("BLOCK_OPTION_VIEW");
          $content->parse("BLOCK_COLUMN_VIEW");
          if($this->arrangement == "vertikal") {
            $content->parse("BLOCK_ROW_VIEW");
          }
          $options = $options . rawurlencode($option) . ",";
        }
        if($this->arrangement == "horizontal"){
          $content->parse("BLOCK_ROW_VIEW");
        }
        $options = substr($options, 0, strlen($options) - 1);
        $data = "3," . rawurlencode($this->questionText) . "," . rawurlencode($this->helpText) . "," . $this->required . "," . $this->arrangement;
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
        $content = $QuestionnaireExtension->loadTemplate("questiontypes/multiplechoicequestion.template.html");
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

        if($this->arrangement == "horizontal"){
          $content->setCurrentBlock("BLOCK_ROW_VIEW");
        }
        foreach ($this->options as $key=>$option) {
          if ($this->arrangement == "vertikal"){
            $content->setCurrentBlock("BLOCK_ROW_VIEW");
          }
          $content->setCurrentBlock("BLOCK_COLUMN_VIEW");
          $content->setCurrentBlock("BLOCK_OPTION_VIEW");
          $content->setVariable("QUESTION_ID", $id);
          $content->setVariable("OPTION_COUNT", $key);
          $content->setVariable("OPTION_LABEL", $option);
          if (in_array($key, $input)) {
               $content->setVariable("OPTION_CHECKED", "checked");
           }
          if ($disabled == 1) {
            $content->setVariable("QUESTION_DISABLED", "disabled");
          }
          $content->parse("BLOCK_OPTION_VIEW");
          $content->parse("BLOCK_COLUMN_VIEW");
          if($this->arrangement == "vertikal") {
            $content->parse("BLOCK_ROW_VIEW");
          }
          $options = $options . rawurlencode($option) . ",";
        }
        if($this->arrangement == "horizontal"){
          $content->parse("BLOCK_ROW_VIEW");
        }

    		$content->parse("BLOCK_VIEW");
    		return $content->get();
    }

    function getResultHTML($id) {
        $QuestionnaireExtension = \Questionnaire::getInstance();
        $content = $QuestionnaireExtension->loadTemplate("questiontypes/multiplechoicequestion.template.html");
        if ($this->resultCount == 0) {
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
                else{
                  $content->setVariable("ROW_STYLE", "style='background-color:#CFCFCF'");
                }
                $content->setVariable("OPTION_LABEL", $option);
                $content->setVariable("OPTION_RESULT", $this->results[$counter]);
                $content->setVariable("OPTION_PERCENT", round((($this->results[$counter] / $this->resultCount) * 100), 1));
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
                $content->setVariable("OPTION_SCRIPT_RESULT", round((($this->results[$counter] / $this->resultCount) * 100), 1));
                $content->parse("BLOCK_SCRIPT_OPTION");
                $counter++;
            }
            $content->setVariable("QUESTION_STATS", "Anzahl: " . $this->resultCount);
            $content->setVariable("INFO_TEXT", "Die Prozentwerte können zusammengerechnet mehr als 100% ergeben, da die Befragten mehrere Antworten auswählen konnten.");
            $content->parse("BLOCK_RESULTS");
        }
        return $content->get();
    }

    function getIndividualResult($result) {
        $return = array();
        foreach ($result as $option) {
            array_push($return, $this->options[$option]);
        }
        return $return;
    }

}

?>

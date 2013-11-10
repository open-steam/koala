<?php

namespace Rapidfeedback\Commands;

class Edit extends \AbstractCommand implements \IFrameCommand {

    private $params;
    private $id;

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        if ($requestObject instanceof \UrlRequestObject) {
            $this->params = $requestObject->getParams();
            isset($this->params[0]) ? $this->id = $this->params[0] : "";
        }
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {
        $rapidfeedback = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        $RapidfeedbackExtension = \Rapidfeedback::getInstance();
        $RapidfeedbackExtension->addCSS();
        $RapidfeedbackExtension->addJS();
        $create_label = "Neuen Fragebogen erstellen";

        $cssWidgetNumbers = new \Widgets\RawHtml();
        $cssWidgetNumbers->setCss('.number{position:absolute;left:30px;}');
        $cssWidgetNumbers->setHtml("");
        $frameResponseObject->addWidget($cssWidgetNumbers);


        // access not allowed for non-admins
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        $staff = $rapidfeedback->get_attribute("RAPIDFEEDBACK_STAFF");
        $admin = 0;
        foreach ($staff as $group) {
            if ($group->is_member($user)) {
                $admin = 1;
                break;
            }
        }
        if ($rapidfeedback->get_creator()->get_id() == $user->get_id()) {
            $admin = 1;
        }
        // non admins are not allowed to edit survey
        if ($admin == 0) {
            $rawWidget = new \Widgets\RawHtml();
            $rawWidget->setHtml("<center>Zugang verwehrt. Sie sind kein Administrator in dieser Rapid Feedback Instanz</center>");
            $frameResponseObject->addWidget($rawWidget);
            return $frameResponseObject;
        }

        // create/edit survey got submitted
        $editID = 0;
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_survey"])) {
            $active = false;
            if (isset($_POST["editRF"]) && intval($_POST["editRF"]) != 0) {
                $survey_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), intval($_POST["editRF"]));
                if ($survey_container->get_attribute("RAPIDFEEDBACK_STATE") == 1) {
                    $active = true;
                }
                $editID = $_POST["editRF"];
            }
            // if survey is active do not change survey structure, only update some settings
            if ($active) {
                $survey_object = new \Rapidfeedback\Model\Survey($survey_container);
                $xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey_container->get_path() . "/survey.xml");
                $survey_object->parseXML($xml);

                $survey_object->setName($_POST["title"]);
                $survey_object->setBeginText($_POST["begintext"]);
                $survey_object->setEndText($_POST["endtext"]);
                if ($_POST["starttype"] == 1) {
                    $survey_object->setStartType(1, $_POST["begin"], $_POST["end"]);
                } else {
                    $survey_object->setStartType(0);
                }
                $survey_object->createSurvey($this->params[1]);
                $frameResponseObject->setConfirmText("Änderungen erfolgreich gespeichert.");
            } else {
                $survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
                if (isset($_POST["title"]) && trim($_POST["title"]) !== "") {

                    $survey_object->setName($_POST["title"]);
                }
                if (isset($_POST["begintext"])) {

                    $survey_object->setBeginText($_POST["begintext"]);
                }
                if (isset($_POST["endtext"])) {

                    $survey_object->setEndText($_POST["endtext"]);
                }

                $questioncounter = 0;
                $sortedQuestions = $_POST["sortable_array"];
                $sortedQuestions != '' ? ($sortedQuestions = explode(',', $sortedQuestions)) : '';
                foreach ($sortedQuestions as $question) {
                    if ($question != "newquestion" && $question != "newlayout" && $question != "") {
                        if (isset($_POST[$question])) {
                            $questionValues = $_POST[$question];
                            $questionValues != '' ? ($questionValues = explode(',', $questionValues)) : '';
                            if (isset($questionValues[0])) {
                                switch ($questionValues[0]) {
                                    case 0:
                                        $newquestion = new \Rapidfeedback\Model\TextQuestion();
                                        $newquestion->setInputLength($questionValues[4]);
                                        break;
                                    case 1:
                                        $newquestion = new \Rapidfeedback\Model\TextareaQuestion();
                                        $newquestion->setRows($questionValues[4]);
                                        break;
                                    case 2:
                                        $newquestion = new \Rapidfeedback\Model\SingleChoiceQuestion();
                                        $options = $_POST[$question . "_options"];
                                        $options != '' ? ($options = explode(',', $options)) : '';
                                        foreach ($options as $option) {
                                            $newquestion->addOption(rawurldecode($option));
                                        }
                                        $newquestion->setArrangement($questionValues[4]);
                                        break;
                                    case 3:
                                        $newquestion = new \Rapidfeedback\Model\MultipleChoiceQuestion();
                                        $options = $_POST[$question . "_options"];
                                        $options != '' ? ($options = explode(',', $options)) : '';
                                        foreach ($options as $option) {
                                            $newquestion->addOption(rawurldecode($option));
                                        }
                                        $newquestion->setArrangement($questionValues[4]);
                                        break;
                                    case 4:
                                        $newquestion = new \Rapidfeedback\Model\MatrixQuestion();
                                        $columns = $_POST[$question . "_columns"];
                                        $columns != '' ? ($columns = explode(',', $columns)) : '';
                                        foreach ($columns as $column) {
                                            $newquestion->addcolumn(rawurldecode($column));
                                        }
                                        $rows = $_POST[$question . "_rows"];
                                        $rows != '' ? ($rows = explode(',', $rows)) : '';
                                        foreach ($rows as $row) {
                                            $newquestion->addRow(rawurldecode($row));
                                        }
                                        break;
                                    case 5:
                                        $newquestion = new \Rapidfeedback\Model\GradingQuestion();
                                        $options = $_POST[$question . "_rows"];
                                        $options != '' ? ($options = explode(',', $options)) : '';
                                        foreach ($options as $option) {
                                            $newquestion->addRow(rawurldecode($option));
                                        }
                                        break;
                                    case 6:
                                        $newquestion = new \Rapidfeedback\Model\TendencyQuestion();
                                        $options = $_POST[$question . "_options"];
                                        $options != '' ? ($options = explode(',', $options)) : '';
                                        $newquestion->setSteps($questionValues[4]);
                                        for ($count = 0; $count < count($options); $count = $count + 2) {
                                            $newquestion->addOption(array(rawurldecode($options[$count]), rawurldecode($options[$count + 1])));
                                        }
                                        break;
                                    case 7:
                                        $newquestion = new \Rapidfeedback\Model\DescriptionLayoutElement();
                                        $newquestion->setDescription(rawurldecode($questionValues[1]));
                                        break;
                                    case 8:
                                        $newquestion = new \Rapidfeedback\Model\HeadlineLayoutElement();
                                        $newquestion->setHeadline(rawurldecode($questionValues[1]));
                                        break;
                                    case 9:
                                        $newquestion = new \Rapidfeedback\Model\PageBreakLayoutElement();
                                        break;
                                    case 10:   
                                        $newquestion = new \Rapidfeedback\Model\JumpLabel();
                                        $newquestion->setFrom($questionValues[1]);
                                        $newquestion->setTo($questionValues[2]);
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
                if ($_POST["starttype"] == 1) {
                    $survey_object->setStartType(1, $_POST["begin"], $_POST["end"]);
                } else {
                    $survey_object->setStartType(0);
                }
                if ($editID != 0) {
                    $survey_object->createSurvey($editID);
                    $frameResponseObject->setConfirmText("Änderungen erfolgreich gespeichert.");
                } else {
                    $con = $survey_object->createSurvey();
                    $editID = $con->get_id();
                    $frameResponseObject->setConfirmText("Fragebogen erfolgreich erstellt.");
                }
            }
        }

        // display actionbar
        $surveys = $rapidfeedback->get_inventory();
        $surveyCount = 0;
        foreach ($surveys as $survey) {
            if ($survey instanceof \steam_object && (!$survey instanceof \steam_user)) {
                $surveyCount++;
            }
        }
        if ($surveyCount > 0) {
            $actionbar = new \Widgets\Actionbar();
            $actions = array(
                array("name" => "Neuen Fragebogen erstellen", "link" => $RapidfeedbackExtension->getExtensionUrl() . "edit/" . $this->id . "/"),
                array("name" => "Import", "link" => $RapidfeedbackExtension->getExtensionUrl() . "import/" . $this->id . "/"),
                array("name" => "Konfiguration", "link" => $RapidfeedbackExtension->getExtensionUrl() . "configuration/" . $this->id . "/"),
                array("name" => "Übersicht", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $this->id . "/")
            );
            $actionbar->setActions($actions);
        } else {
            $actionbar = new \Widgets\Actionbar();
            $actions = array(
                array("name" => "Neuen Fragebogen erstellen", "link" => $RapidfeedbackExtension->getExtensionUrl() . "edit/" . $this->id . "/"),
                array("name" => "Import", "link" => $RapidfeedbackExtension->getExtensionUrl() . "import/" . $this->id . "/"),
                array("name" => "Konfiguration", "link" => $RapidfeedbackExtension->getExtensionUrl() . "configuration/" . $this->id . "/")
            );
            $actionbar->setActions($actions);
        }
        $frameResponseObject->addWidget($actionbar);


        // display edit form
        $content = $RapidfeedbackExtension->loadTemplate("rapidfeedback_edit.template.html");
        $content->setCurrentBlock("BLOCK_CREATE_SURVEY");
        $content->setVariable("CREATE_LABEL", "Fragebogen erstellen");
        $content->setVariable("TITLE_LABEL", "Titel:");
        $content->setVariable("BEGINTEXT_LABEL", "Willkommenstext:");


        $content->setVariable("ENDTEXT_LABEL", "Abschlusstext:");
        $content->setVariable("STARTTYPE_LABEL", "Durchführungszeitraum:");
        $content->setVariable("STARTTYPE0_LABEL", "Manuell");
        $content->setVariable("STARTTYPE1_LABEL", "Zeitgesteuert");
        $content->setVariable("START_LABEL", "von:");
        $content->setVariable("END_LABEL", "bis:");
        $content->setVariable("ELEMENT_COUNTER", 0);
        $content->setVariable("STARTTYPE_FIRST", "checked");
        $content->setVariable("DISPLAY_DATEPICKER", "none");
        $content->setVariable("QUESTION_LABEL", "Frage");
        $content->setVariable("HELPTEXT_LABEL", "Hilfetext");
        $content->setVariable("QUESTIONTYPE_LABEL", "Fragetyp");
        $content->setVariable("TEXTQUESTION_LABEL", "kurzer Text");
        $content->setVariable("TEXTAREAQUESTION_LABEL", "langer Text");
        $content->setVariable("SINGLECHOICE_LABEL", "Single Choice");
        $content->setVariable("MULTIPLECHOICE_LABEL", "Multiple Choice");
        $content->setVariable("MATRIX_LABEL", "Matrix");
        $content->setVariable("GRADING_LABEL", "Benotung");
        $content->setVariable("JUMP_LABEL", "Sprungmarke");
        $content->setVariable("TENDENCY_LABEL", "Tendenz");
        $content->setVariable("ANSWER_LABEL", "Antwort");
        $content->setVariable("AREA_ROWS", "Zeilen");
        $content->setVariable("MAX_INPUT_LABEL", "Maximale Eingabelänge");
        $content->setVariable("MAX_INPUT_HELP", "(0 = keine Beschränkung)");
        $content->setVariable("AREA_ROWS_LABEL", "Textfeldhöhe");
        $content->setVariable("ARRANGEMENT_LABEL", "Anordnung in");
        $content->setVariable("SCALE_LABEL", "Skala");
        $content->setVariable("STEPS_LABEL", "Schritte");
        $content->setVariable("POSSIBLEANSWERS_LABEL", "Antwortmöglichkeiten");
        $content->setVariable("ADDOPTION_LABEL", "Weitere Option hinzufügen");
        $content->setVariable("COLUMNS_LABEL", "Spalten");
        $content->setVariable("COLUMNSLABEL_LABEL", "Spalten Label");
        $content->setVariable("ROWSLABEL_LABEL", "Zeilen Label");
        $content->setvariable("ELEMENTS_LABEL", "Elemente");
        $content->setVariable("ADDROWS_LABEL", "Weitere Zeile hinzufügen");
        $content->setVariable("MANDATORY_LABEL", "Als Pflichtfrage definieren");
        $content->setVariable("SAVE_LABEL", "Frage hinzufügen");
        $content->setVariable("CANCEL_LABEL", "Abbrechen");
        $content->setVariable("ADDQUESTION_LABEL", "Neue Frage hinzufügen");
        $content->setVariable("LAYOUTELEMENT_LABEL", "Layout-Element");
        $content->setVariable("DESCRIPTIONLAYOUT_LABEL", "Beschreibung");
        $content->setVariable("HEADLINELAYOUT_LABEL", "Überschrift");
        $content->setVariable("PAGEBREAKLAYOUT_LABEL", "Seitenumbruch");
        $content->setVariable("ADDLAYOUT_LABEL", "Neues Layout-Element hinzufügen");
        $content->setVariable("CREATE_SURVEY", "Fragebogen erstellen");

        // if command is called with an object id load the corresponding survey data
        if ($editID == 0 && isset($this->params[1])) {
            $editID = $this->params[1];
        }
        if ($editID != 0) {
            $content->setVariable("EDIT_ID", $editID);
            $survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $editID);
            $survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
            $xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
            $survey_object->parseXML($xml);
            $content->setVariable("TITLE_VALUE", $survey_object->getName());
            $content->setVariable("BEGINTEXT_VALUE", $survey_object->getBeginText());
            $welcomeImageId = $survey->get_attribute("bid:rfb:picture_id");
            if ($welcomeImageId !== 0) {
                $content->setVariable("WELCOME_IMG_SRC", PATH_URL . "download/document/$welcomeImageId/");
            }
            $content->setVariable("ENDTEXT_VALUE", $survey_object->getEndText());
            $starttype = $survey->get_attribute("RAPIDFEEDBACK_STARTTYPE");
            if (is_array($starttype)) {
                $content->setVariable("STARTTYPE_FIRST", "");
                $content->setVariable("STARTTYPE_SECOND", "checked");
                $content->setVariable("DISPLAY_DATEPICKER", "");
                $content->setVariable("BEGIN_VALUE", date('d.m.Y H:i', $starttype[1]));
                $content->setVariable("END_VALUE", date('d.m.Y H:i', $starttype[0]));
            }
            $questions = $survey_object->getQuestions();
            $question_html = "";
            $id_counter = 0;
            $asseturl = $RapidfeedbackExtension->getAssetUrl() . "icons/";
            for ($count = 0; $count < count($questions); $count++) {
                $question_html = $question_html . $questions[$count]->getEditHTML($id_counter, $count + 1);
                $id_counter++;
            }
            $content->setVariable("ELEMENT_COUNTER", $id_counter);
            $content->setVariable("QUESTIONS_HTML", $question_html);
            $content->setVariable("BACK_URL", $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id() . "/");
            $content->setVariable("CREATE_LABEL", "Fragebogen bearbeiten");
            $content->setVariable("CREATE_SURVEY", "Änderungen speichern");
            $create_label = "Umfrage bearbeiten";
            if ($survey->get_attribute("RAPIDFEEDBACK_STATE") == 1) {
                $content->setVariable("DISPLAY_QUESTIONS", "none");
            }
        } else {
            $content->setVariable("EDIT_ID", 0);
        }

        $content->setVariable("ASSET_URL", $RapidfeedbackExtension->getAssetUrl() . "icons");
        $content->parse("BLOCK_CREATE_SURVEY");

        $rawWidget = new \Widgets\RawHtml();
        $rawWidget->setHtml($content->get());
        $frameResponseObject->addWidget($rawWidget);
        $pollingDummy = new \Widgets\PollingDummy();
        $frameResponseObject->addWidget($pollingDummy);
        return $frameResponseObject;
    }

}

?>
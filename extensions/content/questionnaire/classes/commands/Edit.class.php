<?php

namespace Questionnaire\Commands;

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
      $questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
      $QuestionnaireExtension = \Questionnaire::getInstance();
      $QuestionnaireExtension->addCSS();
      $QuestionnaireExtension->addJS();
      $active = \Questionnaire::getInstance()->isActive($this->id);
      $cssWidgetNumbers = new \Widgets\RawHtml();
      $cssWidgetNumbers->setCss('.number{position:absolute;left:30px;}');
      $cssWidgetNumbers->setHtml("");
      $frameResponseObject->addWidget($cssWidgetNumbers);
      $times = $questionnaire->get_attribute("QUESTIONNAIRE_PARTICIPATION_TIMES"); //0 multiple, else not
      $user = $GLOBALS["STEAM"]->get_current_steam_user();
      $creator = $questionnaire->get_creator();

      // check if current user is admin
      $staff = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
      $admin = 0;
      if ($creator->get_id() == $user->get_id() || \lms_steam::is_steam_admin($user)) {
        $admin = 1;
      }
      else{
        if(in_array($user, $staff)){
          $admin = 1;
        }
        else{
          foreach ($staff as $object) {
            if ($object instanceof steam_group && $object->is_member($user)) {
              $admin = 1;
              break;
            }
          }
        }
      }

      // non admins are not allowed to edit survey
      if ($admin == 0) {
          $rawWidget = new \Widgets\RawHtml();
          $rawWidget->setHtml("<center>Die Bearbeitung dieses Fragebogens ist den Administratoren vorbehalten.</center>");
          $frameResponseObject->addWidget($rawWidget);
          return $frameResponseObject;
      }

      $surveys = $questionnaire->get_inventory();
      $survey = $surveys[0];
      $editID = $survey->get_id();
      $resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");

      // create/edit survey got submitted
      //$editID = 0;
      if (1 == 0){ //$_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_survey"])) {

        $survey_object = new \Questionnaire\Model\Survey($questionnaire);
        if (isset($_POST["title"]) && trim($_POST["title"]) !== "") {
            $survey_object->setName($_POST["title"]);
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
                                $newquestion = new \Questionnaire\Model\TextQuestion();
                                $newquestion->setInputLength($questionValues[4]);
                                break;
                            case 1:
                                $newquestion = new \Questionnaire\Model\TextareaQuestion();
                                $newquestion->setRows($questionValues[4]);
                                break;
                            case 2:
                                $newquestion = new \Questionnaire\Model\SingleChoiceQuestion();
                                $options = $_POST[$question . "_options"];
                                $options != '' ? ($options = explode(',', $options)) : '';
                                foreach ($options as $option) {
                                    $newquestion->addOption(rawurldecode($option));
                                }
                                $newquestion->setArrangement($questionValues[4]);
                                break;
                            case 3:
                                $newquestion = new \Questionnaire\Model\MultipleChoiceQuestion();
                                $options = $_POST[$question . "_options"];
                                $options != '' ? ($options = explode(',', $options)) : '';
                                foreach ($options as $option) {
                                    $newquestion->addOption(rawurldecode($option));
                                }
                                $newquestion->setArrangement($questionValues[4]);
                                break;
                            case 4:
                                $newquestion = new \Questionnaire\Model\MatrixQuestion();
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
                                $newquestion = new \Questionnaire\Model\GradingQuestion();
                                $options = $_POST[$question . "_rows"];
                                $options != '' ? ($options = explode(',', $options)) : '';
                                foreach ($options as $option) {
                                    $newquestion->addRow(rawurldecode($option));
                                }
                                break;
                            case 6:
                                $newquestion = new \Questionnaire\Model\TendencyQuestion();
                                $options = $_POST[$question . "_options"];
                                $options != '' ? ($options = explode(',', $options)) : '';
                                $newquestion->setSteps($questionValues[4]);
                                for ($count = 0; $count < count($options); $count = $count + 2) {
                                    $newquestion->addOption(array(rawurldecode($options[$count]), rawurldecode($options[$count + 1])));
                                }
                                break;
                            case 7:
                                $newquestion = new \Questionnaire\Model\DescriptionLayoutElement();
                                $newquestion->setDescription(rawurldecode($questionValues[1]));
                                break;
                            case 8:
                                $newquestion = new \Questionnaire\Model\HeadlineLayoutElement();
                                $newquestion->setHeadline(rawurldecode($questionValues[1]));
                                break;
                            case 9:
                                $newquestion = new \Questionnaire\Model\PageBreakLayoutElement();
                                break;
                            case 10:
                                $newquestion = new \Questionnaire\Model\JumpLabel();
                                $newquestion->setText(rawurldecode($questionValues[1]));
                                $newquestion->setTo(rawurldecode($questionValues[2]));
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

        $survey_object->createSurvey($editID);
        //$frameResponseObject->setConfirmText("Änderungen erfolgreich gespeichert.");
      }

      // display actionbar
      $surveys = $questionnaire->get_inventory();
      $surveyCount = 0;
      foreach ($surveys as $survey) {
          if ($survey instanceof \steam_object && (!$survey instanceof \steam_user)) {
              $surveyCount++;
          }
      }
      if ($surveyCount > 0) {
          $actionbar = new \Widgets\Actionbar();
          $actions = array(
              //array("name" => "Neuen Fragebogen erstellen", "link" => $QuestionnaireExtension->getExtensionUrl() . "edit/" . $this->id . "/"),
              //array("name" => "Import", "link" => $QuestionnaireExtension->getExtensionUrl() . "import/" . $this->id . "/"),
              //array("name" => "Konfiguration", "link" => $QuestionnaireExtension->getExtensionUrl() . "configuration/" . $this->id . "/"),
              //array("name" => "Übersicht", "link" => $QuestionnaireExtension->getExtensionUrl() . "Index/" . $this->id . "/")
          );
          $actionbar->setActions($actions);
      } else {
          $actionbar = new \Widgets\Actionbar();
          $actions = array(
              //array("name" => "Neuen Fragebogen erstellen", "link" => $QuestionnaireExtension->getExtensionUrl() . "edit/" . $this->id . "/"),
              //array("name" => "Import", "link" => $QuestionnaireExtension->getExtensionUrl() . "import/" . $this->id . "/"),
              //array("name" => "Konfiguration", "link" => $QuestionnaireExtension->getExtensionUrl() . "configuration/" . $this->id . "/")
          );
          $actionbar->setActions($actions);
      }
      //$frameResponseObject->addWidget($actionbar);

      // display edit form
      $content = $QuestionnaireExtension->loadTemplate("questionnaire_edit.template.html");

      $content->setVariable("QUESTIONNAIRE_NAME", '<svg style="width:16px; height:16px; float:left; color:#3a6e9f; right:5px; position:relative;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' . PATH_URL . 'explorer/asset/icons/mimetype/svg/questionnaire.svg#questionnaire"></use></svg><h1>' . $questionnaire->get_name() . '</h1>');
      $content->setVariable("QUESTIONNAIRE_DESC", '<p style="color:#AAAAAA; clear:both; margin-top:0px">' . $questionnaire->get_attribute("OBJ_DESC") . '</p>');

      if($active){
        $content->setVariable("QUESTIONNAIRE_STATUS", "aktiv (Ende: " . $questionnaire->get_attribute("QUESTIONNAIRE_END") . " Uhr)");
        $content->setVariable("COLOR", "-green");
      }
      else{
        $content->setVariable("QUESTIONNAIRE_STATUS", "nicht aktiv");
        $content->setVariable("COLOR", "-red");
      }

      $content->setVariable("QUESTIONNAIRE_NUMBER_QUESTIONS", $survey->get_attribute("QUESTIONNAIRE_QUESTIONS"));
      $content->setVariable("QUESTIONNAIRE_NUMBER_SUBMISSIONS", $resultContainer->get_attribute("QUESTIONNAIRE_RESULTS"));

      $content->setVariable("QUESTIONNAIRE_SUBHEADLINE", "Fragebogen bearbeiten");

      if($resultContainer->get_attribute("QUESTIONNAIRE_RESULTS") > 0){
        $content->setVariable("QUESTIONNAIRE_WARNING", "Es liegen bereits Abgaben vor. Eine Bearbeitung des Fragebogens zu diesem Zeitpunkt kann dazu führen, dass diese Abgaben und das Endresultat ungültig werden.");
      }

      if($times == 0){
        $content->setVariable("QUESTIONNAIRE_MULTIPLE", "erlaubt");
      }
      else{
        $content->setVariable("QUESTIONNAIRE_MULTIPLE", "nicht erlaubt");
      }

      $participated = $resultContainer->get_attribute("QUESTIONNAIRE_PARTICIPANTS");
      $ownSubmissions = "";
      // show users results in the table
      if (isset($participated[$user->get_id()])) {
        $results = $participated[$user->get_id()];
        $count = 1;
        foreach ($results as $result) {
          $resultObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $result);
          $ownSubmissions .= '<div class="value">';
          if ($resultObject->get_attribute("QUESTIONNAIRE_RELEASED") != 0) {
            $ownSubmissions .= $count . ": Abgegeben (" . date("d.m.Y H:i:s", $resultObject->get_attribute("QUESTIONNAIRE_RELEASED")) . " Uhr)";
          } else {
            $questionCount = $survey->get_attribute("QUESTIONNAIRE_QUESTIONS");
            $questionsAnswered = 0;
            $attributeNames = $resultObject->get_attribute_names();
            for ($count2 = 0; $count2 < $questionCount; $count2++) {
              if (in_array("QUESTIONNAIRE_ANSWER_" . $count2, $attributeNames)) {
                $questionsAnswered++;
              }
            }
            $ownSubmissions .= $count . ": Aktiv (" . $questionsAnswered . " von " . $questionCount . " Fragen beantwortet)";
          }

          $popupMenu = new \Widgets\PopupMenu();
          $popupMenu->setCommand("GetPopupMenuSubmission");
          $popupMenu->setNamespace("Questionnaire");
          $popupMenu->setData($questionnaire);
          $popupMenu->setElementId("edit-overlay");
          $popupMenu->setParams(array(array("key" => "result", "value" => $result), array("key" => "id", "value" => $survey->get_id())));
          $ownSubmissions .= $popupMenu->getHtml();
          $ownSubmissions .= '</div>';

          $count++;
        }
        $content->setVariable("QUESTIONNAIRE_OWN_SUBMISSIONS", $ownSubmissions);
      }
      else{
        $ownSubmissions .= '<div class="value">keine</div>';
        $content->setVariable("QUESTIONNAIRE_OWN_SUBMISSIONS", $ownSubmissions);
      }

      $content->setCurrentBlock("BLOCK_CREATE_SURVEY");
      $content->setVariable("QUESTIONNAIRE_ID", $this->id);
      $content->setVariable("ELEMENT_COUNTER", 0);
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
      $content->setVariable("MAX_INPUT_LABEL", "Maximale Zeichenanzahl");
      $content->setVariable("MAX_INPUT_HELP", "(0 = keine Beschränkung)");
      $content->setVariable("AREA_ROWS_LABEL", "Textfeldhöhe");
      $content->setVariable("ARRANGEMENT_LABEL", "Anordnung");
      $content->setVariable("SCALE_LABEL", "Skala");
      $content->setVariable("STEPS_LABEL", "Schritte");
      $content->setVariable("POSSIBLEANSWERS_LABEL", "Antwortmöglichkeiten");
      $content->setVariable("ADDOPTION_LABEL", "Weitere Option hinzufügen");
      $content->setVariable("COLUMNS_LABEL", "Spalten");
      $content->setVariable("COLUMNSLABEL_LABEL", "Spalten");
      $content->setVariable("ROWSLABEL_LABEL", "Zeilen");
      $content->setVariable("ELEMENTS_LABEL", "Elemente");
      $content->setVariable("ADDROWS_LABEL", "Weitere Zeile hinzufügen");
      $content->setVariable("MANDATORY_LABEL", "Pflichtfrage");
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
          if ($surveyCount > 0) {
              $content->setVariable("EDIT_ID_MSG", $editID);
          }
          $survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $editID);
          $survey_object = new \Questionnaire\Model\Survey($questionnaire);
          $xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
          $survey_object->parseXML($xml);
          //$content->setVariable("TITLE_VALUE", $survey_object->getName());
          /*
          $welcomeImageId = $survey->get_attribute("bid:rfb:picture_id");
          if ($welcomeImageId !== 0) {
              $content->setVariable("WELCOME_IMG_SRC", PATH_URL . "download/document/$welcomeImageId/");
          }
          */
          $questions = $survey_object->getQuestions();
          $question_html = "";
          $id_counter = 0;
          $asseturl = $QuestionnaireExtension->getAssetUrl() . "icons/";
          $i = 1;
          for ($count = 0; $count < count($questions); $count++) {
              if ($questions[$count] instanceof \Questionnaire\Model\AbstractLayoutElement) {
                  $question_html = $question_html . $questions[$count]->getEditHTML($this->id, $id_counter);
              } else {
                  $question_html = $question_html . $questions[$count]->getEditHTML($this->id, $id_counter, $i);
                  $i++;
              }
              $id_counter++;
          }
          $content->setVariable("ELEMENT_COUNTER", $id_counter);
          $content->setVariable("QUESTIONS_HTML", $question_html);
          $content->setVariable("BACK_URL", $QuestionnaireExtension->getExtensionUrl() . "Index/" . $questionnaire->get_id() . "/");
          $content->setVariable("TITLE", $questionnaire->get_attribute("OBJ_NAME"));
          $content->setVariable("CREATE_SURVEY", "Änderungen speichern");
          $create_label = "Umfrage bearbeiten";
      } else {
          $content->setVariable("EDIT_ID", 0);
      }

      $content->setVariable("ASSET_URL", $QuestionnaireExtension->getAssetUrl() . "icons");
      $content->parse("BLOCK_CREATE_SURVEY");

      $rawWidget = new \Widgets\RawHtml();
      $PopupMenuStyle = \Widgets::getInstance()->readCSS("PopupMenu.css");
      $rawWidget->setHtml($content->get() . "<style>" . $PopupMenuStyle . "</style>");

      $frameResponseObject->addWidget($rawWidget);
      $pollingDummy = new \Widgets\PollingDummy();
      $frameResponseObject->addWidget($pollingDummy);
      return $frameResponseObject;
  }

}

?>

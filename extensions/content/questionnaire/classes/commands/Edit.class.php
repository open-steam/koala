<?php

namespace Questionnaire\Commands;

class Edit extends \AbstractCommand implements \IFrameCommand {

  private $params;
  private $id;
  private $surveyId;

  public function validateData(\IRequestObject $requestObject) {
      return true;
  }

  public function processData(\IRequestObject $requestObject) {
      if ($requestObject instanceof \UrlRequestObject) {
          $this->params = $requestObject->getParams();
          isset($this->params[0]) ? $this->id = $this->params[0] : "";
          isset($this->params[1]) ? $this->surveyId = $this->params[1] : "";
      }
  }

  public function frameResponse(\FrameResponseObject $frameResponseObject) {

      if ($this->id == "" || $this->surveyId == "") {
          $rawWidget = new \Widgets\RawHtml();
          $rawWidget->setHtml("<center>Der angeforderte Fragebogen existiert nicht.</center>");
          $frameResponseObject->addWidget($rawWidget);
          return $frameResponseObject;
      }

      $questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
      $survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->surveyId);

      if(!($survey instanceof \steam_container) || !($questionnaire instanceof \steam_room)){
        $rawWidget = new \Widgets\RawHtml();
        $rawWidget->setHtml("<center>Der angeforderte Fragebogen existiert nicht.</center>");
        $frameResponseObject->addWidget($rawWidget);
        return $frameResponseObject;
      }

      $QuestionnaireExtension = \Questionnaire::getInstance();
      $QuestionnaireExtension->addCSS();
      $QuestionnaireExtension->addJS();
      $active = \Questionnaire::getInstance()->isActive($this->id);
      $cssWidgetNumbers = new \Widgets\RawHtml();
      $cssWidgetNumbers->setCss('.number{position:absolute;left:30px;}');
      $cssWidgetNumbers->setHtml("");
      $frameResponseObject->addWidget($cssWidgetNumbers);
      $times = $questionnaire->get_attribute("QUESTIONNAIRE_PARTICIPATION_TIMES"); //0 multiple, else not
      $user = \lms_steam::get_current_user();
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
            if ($object instanceof \steam_group && $object->is_member($user)) {
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

      $resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");

      // display edit form
      $content = $QuestionnaireExtension->loadTemplate("questionnaire_edit.template.html");

      $content->setVariable("QUESTIONNAIRE_NAME", '<svg style="width:16px; height:16px; float:left; color:#3a6e9f; right:5px; position:relative;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' . PATH_URL . 'explorer/asset/icons/mimetype/svg/questionnaire.svg#questionnaire"></use></svg><h1>' . $questionnaire->get_name() . '</h1>');
      $content->setVariable("QUESTIONNAIRE_DESC", '<p style="color:#AAAAAA; clear:both; margin-top:0px">' . $questionnaire->get_attribute("OBJ_DESC") . '</p>');

      if($active){
        $content->setVariable("QUESTIONNAIRE_STATUS", "aktiv");
        $content->setVariable("COLOR", "-green");
      }
      else{
        $content->setVariable("QUESTIONNAIRE_STATUS", "nicht aktiv");
        $content->setVariable("COLOR", "-red");
      }

      $startDate = new \Widgets\DatePicker();
      $startDate->setData($questionnaire);
      $startDate->setDatePicker(true);
      $startDate->setTimePicker(true);
      $startDate->setAutosave(true);
      $startDate->setContentProvider(\Widgets\DataProvider::attributeProvider("QUESTIONNAIRE_START"));

      $endDate = new \Widgets\DatePicker();
      $endDate->setData($questionnaire);
      $endDate->setDatePicker(true);
      $endDate->setTimePicker(true);
      $endDate->setAutosave(true);
      $endDate->setContentProvider(\Widgets\DataProvider::attributeProvider("QUESTIONNAIRE_END"));

      $content->setVariable("QUESTIONNAIRE_START", $startDate->getHtml());
      $content->setVariable("QUESTIONNAIRE_END", $endDate->getHtml());

      $content->setVariable("QUESTIONNAIRE_NUMBER_QUESTIONS", $survey->get_attribute("QUESTIONNAIRE_QUESTIONS"));
      $content->setVariable("QUESTIONNAIRE_NUMBER_SUBMISSIONS", $resultContainer->get_attribute("QUESTIONNAIRE_RESULTS"));

      $content->setVariable("QUESTIONNAIRE_SUBHEADLINE", "Fragebogen bearbeiten");

      if($resultContainer->get_attribute("QUESTIONNAIRE_RESULTS") > 0){
        $content->setVariable("QUESTIONNAIRE_WARNING", "Es liegen bereits Abgaben vor. Eine Bearbeitung des Fragebogens zu diesem Zeitpunkt kann dazu führen, dass diese Abgaben und das Endresultat ungültig werden.");
      }

      $multipleFill = new \Widgets\RadioButton();
      $multipleFill->setData($questionnaire);
      $multipleFill->setType("horizontal");
      $multipleFill->setAutosave(true);
      $multipleFill->setContentProvider(\Widgets\DataProvider::attributeProvider("QUESTIONNAIRE_PARTICIPATION_TIMES"));
      $multipleFill->setOptions(array(array("name" => "erlaubt", "value" => 0), array("name" => "nicht erlaubt", "value" => 1)));

      $content->setVariable("QUESTIONNAIRE_MULTIPLE", $multipleFill->getHtml());

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

      if ($this->surveyId != 0) {
          $content->setVariable("EDIT_ID", $this->surveyId);
          if ($surveyCount > 0) {
              $content->setVariable("EDIT_ID_MSG", $this->surveyId);
          }
          $survey_object = new \Questionnaire\Model\Survey($questionnaire);
          $xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
          $survey_object->parseXML($xml);
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

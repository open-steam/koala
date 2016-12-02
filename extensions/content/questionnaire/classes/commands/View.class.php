<?php

namespace Questionnaire\Commands;

class View extends \AbstractCommand implements \IFrameCommand {

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
      $survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[0]);
      $questionnaire = $survey->get_environment();
      $QuestionnaireExtension = \Questionnaire::getInstance();
      $survey_object = new \Questionnaire\Model\Survey($questionnaire);
      $xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
      $survey_object->parseXML($xml);
      $questions = $survey_object->getQuestions();
      $user = \lms_steam::get_current_user();
      $QuestionnaireExtension->addCSS();
      $QuestionnaireExtension->addJS();
      $times = $questionnaire->get_attribute("QUESTIONNAIRE_PARTICIPATION_TIMES"); //0 multiple, else not
      $resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
      $participants = $resultContainer->get_attribute("QUESTIONNAIRE_PARTICIPANTS");

      if (!($questionnaire->check_access_read())) {
  				$errorHtml = new \Widgets\RawHtml();
  				$errorHtml->setHtml("Der Fragebogen kann nicht angezeigt werden, da Sie nicht über die erforderlichen Leserechte verfügen.");
  				$frameResponseObject->addWidget($errorHtml);
  				return $frameResponseObject;
  		}

      $creator = $questionnaire->get_creator();
      //check if current user is admin
      $staff = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
      $admin = 0;
      $allowed = false;
      $root = 0;
      if(\lms_steam::is_steam_admin($user)){
        $root = 1;
        $admin = 1;
        $allowed = 1;
      }
      else if ($creator->get_id() == $user->get_id()){
        $allowed = true;
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

      // check if user is allowed to view survey
      $possibleParticipants = $questionnaire->get_attribute("QUESTIONNAIRE_GROUP");
      if(in_array($user, $possibleParticipants)){
        $allowed = true;
      }
      else{
        foreach ($possibleParticipants as $object) {
          if ($object instanceof steam_group && $object->is_member($user)) {
            $allowed = true;
            break;
          }
        }
      }

      if (!$admin && !$allowed) {
          $errorHtml = new \Widgets\RawHtml();
          $errorHtml->setHtml("Der Fragebogen kann nicht angezeigt werden, da Sie nicht über die erforderlichen Rechte verfügen.");
          $frameResponseObject->addWidget($errorHtml);
          return $frameResponseObject;
      }

      // check if input is disabled
      $disabled = 0;
      if (isset($this->params[3])) {
          $disabled = 1;
      }

      // check if displaying preview or result
      $showPreview = 0;
      $showResult = 0;
      if (isset($this->params[2])){
        if($this->params[2] == "preview") {
          $showPreview = 1;
          $disabled = 1;
        }else{
          $showResult = 1;
          $resultId = $this->params[2];
        }
      }

      //check if the user has an active submission
      $participated = !is_null($participants[$user->get_id()]);
      if($participated) {
        $submissions = $participants[$user->get_id()];
        foreach ($submissions as $submission) {
          $submissionObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $submission);
          if($submissionObject->get_attribute("QUESTIONNAIRE_RELEASED") == 0) {
            $showResult = 1;
            $resultId = $submission;
            break;
          }
        }
      }

      // check which page should be displayed
      $pages = $survey->get_attribute("QUESTIONNAIRE_PAGES");
      $page = 1;
      if (isset($this->params[1])) {
          $page = $this->params[1];
      }

      // get result object if displayed result
      if ($showResult) {
          $resultObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $resultId);
          if($resultObject instanceof \steam_object) {
              if ($resultObject->get_creator()->get_id() == $user->get_id()) {
                $ownResult = true;
              } else{
                $ownResult = false;
              }
          }
          else{
            //no resultObject which can be displayed
            header('Location: ' . $QuestionnaireExtension->getExtensionUrl() . "view/" . $this->params[0] . "/");
            die;
          }
      }

      $active = \Questionnaire::getInstance()->isActive($questionnaire->get_id());

      //the user is admin or allowed to participate or both!!!

      $hint = "";
      $subheadline = "";

      if($showResult){ //user want to see or edit a result
        if($disabled){ //show result
          if($ownResult){ //show own result
            //showResult
            if($resultObject->get_attribute("QUESTIONNAIRE_RELEASED") == 0){
              $subheadline = "Meine aktive Abgabe";
            }
            else{
              $subheadline = "Meine Abgabe vom " . date("d.m.Y H:i:s", $resultObject->get_attribute("QUESTIONNAIRE_RELEASED")) . " Uhr";
            }
          }
          else{ //show result from other user
            if($admin){
              //show result
              $subheadline = "Abgabe von " . $resultObject->get_creator()->get_full_name() . " vom " . date("d.m.Y H:i:s", $resultObject->get_attribute("QUESTIONNAIRE_RELEASED")) . " Uhr";
            }
            else{
              $hint = "Es können nur eigene Abgaben betrachtet werden";
            }
          }
        }
        else{ //edit result
          if($active){
            if($ownResult){
              if($resultObject->get_attribute("QUESTIONNAIRE_RELEASED") == 0){
                //edit result
                $subheadline = "Fragebogen ausfüllen";
              }
              else if($questionnaire->get_attribute("QUESTIONNAIRE_OWN_EDIT") == 1 || ($admin && $questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1)){
                //edit result
                $subheadline = "Meine Abgabe vom " . date("d.m.Y H:i:s", $resultObject->get_attribute("QUESTIONNAIRE_RELEASED")) . " Uhr bearbeiten";
              }
              else{
                $hint = "Abgaben können nicht bearbeitet werden";
              }
            }
            else{ //edit of results of other user
              if($admin && $questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1){
                //edit result
                $subheadline = "Abgabe von " . $resultObject->get_creator()->get_full_name() . " vom " . date("d.m.Y H:i:s", $resultObject->get_attribute("QUESTIONNAIRE_RELEASED")) . " Uhr bearbeiten";
              }
              else{
                $hint = "Abgaben anderer Nutzer können nicht bearbeitet werden";
              }
            }
          }
          else{
            if($admin){ //admin can edit results even if not active
              if($ownResult){ //admin wants to edit own result
                if($resultObject->get_attribute("QUESTIONNAIRE_RELEASED") == 0 || $questionnaire->get_attribute("QUESTIONNAIRE_OWN_EDIT") == 1 || $questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1){
                  //edit result
                  $subheadline = "Meine Abgabe vom " . date("d.m.Y H:i:s", $resultObject->get_attribute("QUESTIONNAIRE_RELEASED")) . " Uhr bearbeiten";
                }
                else{
                  $hint = "Abgaben können nicht bearbeitet werden";
                }
              }
              else{ //admin wants to edit result from other user
                if($questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1){
                  //edit result
                  $subheadline = "Abgabe von " . $resultObject->get_creator()->get_name() . " vom " . date("d.m.Y H:i:s", $resultObject->get_attribute("QUESTIONNAIRE_RELEASED")) . " Uhr bearbeiten";
                }
                else{
                  $hint = "Abgaben anderer Nutzer können nicht bearbeitet werden";
                }
              }
            }
            else{
              $hint = "Abgaben können nicht bearbeitet werden, da der Fragebogen nicht aktiv ist";
            }
          }
        }
      }else if($showPreview){ //user wants to see preview
        if($admin){
          //show preview
          $subheadline = "Vorschau";
        }
        else{
          if($active){
            //show preview
            $subheadline = "Vorschau";
          }
          else{
            $hint = "Der Fragebogen kann nicht angezeigt werden, da er nicht aktiv ist";
          }
        }
      }else{ //user wants to fill in
        if($active){
          if($allowed){
            $participated = !is_null($participants[$user->get_id()]);
            if(!$participated || ($participated && $times == 0)){
              //show fill in
              $subheadline = "Fragebogen ausfüllen";
            }
            else{
                $showPreview = 1;
                $disabled = 1;
                $subheadline = "Vorschau";
              }
            }
            else{
              if($admin){
                $showPreview = 1;
                $disabled = 1;
                $subheadline = "Vorschau";
              }
              else{
                $hint = "Sie haben nicht das Recht den Fragebogen auszufüllen";
              }
          }
        }
        else{
          if($admin){
            $showPreview = 1;
            $disabled = 1;
            $subheadline = "Vorschau";
          }
          else{
            $hint = "Der Fragebogen kann nicht ausgefüllt werden, da er nicht aktiv ist";
          }
        }
      }

      if($root){
        $hint = "";
      }

      // collect user input if view got submitted (and check for errors)
      $values = array();
      $errors = array();
      if ($_SERVER["REQUEST_METHOD"] == "POST" && $showPreview == 0 && $disabled == 0) {
          $questionCounter = 0;
          $pageCounter = 1;
          if ($_POST["action"] == "next") {
              $resultPage = $page - 1;
          } else {
              $resultPage = $page + 1;
          }
          foreach ($questions as $question) {
              if ($question instanceof \Questionnaire\Model\AbstractQuestion) {
                  if ($resultPage == $pageCounter) {
                      if ($question instanceof \Questionnaire\Model\TextQuestion | $question instanceof \Questionnaire\Model\TextareaQuestion) {
                          $value = $_POST["question" . $questionCounter];
                          if ($question->getRequired() == 1 && trim($value) == "") {
                              array_push($errors, $questionCounter);
                          } else if (trim($value) == "") {
                              $values[$questionCounter] = -1;
                          } else {
                              $values[$questionCounter] = trim($value);
                          }
                      } else if ($question instanceof \Questionnaire\Model\SingleChoiceQuestion) {
                          if (!isset($_POST["question" . $questionCounter])) {
                              if ($question->getRequired() == 1) {
                                  array_push($errors, $questionCounter);
                              } else {
                                  $values[$questionCounter] = -1;
                              }
                          } else {
                              $values[$questionCounter] = $_POST["question" . $questionCounter];
                          }
                      } else if ($question instanceof \Questionnaire\Model\MultipleChoiceQuestion) {
                          $optionsCount = count($question->getOptions());
                          $results = array();
                          for ($count = 0; $count < $optionsCount; $count++) {
                              if (isset($_POST["question" . $questionCounter . "_" . $count])) {
                                  array_push($results, ($count));
                              }
                          }
                          if ($question->getRequired() == 1 && empty($results)) {
                              array_push($errors, $questionCounter);
                          } else {
                              $values[$questionCounter] = $results;
                          }
                      } else if ($question instanceof \Questionnaire\Model\MatrixQuestion) {
                          $rowCount = count($question->getRows());
                          $results = array();
                          for ($count = 0; $count < $rowCount; $count++) {
                              if (isset($_POST["question" . $questionCounter . "_" . $count])) {
                                  array_push($results, $_POST["question" . $questionCounter . "_" . $count]);
                              }
                          }
                          if ($question->getRequired() == 1 && (count($results) < $rowCount)) {
                              array_push($errors, $questionCounter);
                              $values[$questionCounter] = $results;
                          } else {
                              $values[$questionCounter] = $results;
                          }
                      } else if ($question instanceof \Questionnaire\Model\TendencyQuestion) {
                          $rowCount = count($question->getOptions());
                          $results = array();
                          $complete = true;
                          for ($count = 0; $count < $rowCount; $count++) {
                              if (isset($_POST["question" . $questionCounter . "_" . $count])) {
                                  $results[$count] = $_POST["question" . $questionCounter . "_" . $count];
                              } else {
                                  $results[$count] = -1;
                                  $complete = false;
                              }
                          }
                          if ($question->getRequired() == 1 && !$complete) {
                              array_push($errors, $questionCounter);
                              $values[$questionCounter] = $results;
                          } else {
                              $values[$questionCounter] = $results;
                          }
                      }
                  }
                  $questionCounter++;
              } else if ($question instanceof \Questionnaire\Model\PageBreakLayoutElement) {
                  $pageCounter++;
              }
          }
          // if there are errors show error msg, else save answers
          if (!empty($errors)) {
              $problemdescription = "Sie müssen noch folgende Pflichtfragen beantworten: ";
              foreach ($errors as $error) {
                  $problemdescription = $problemdescription . ($error + 1) . ", ";
              }
              $problemdescription = substr($problemdescription, 0, strlen($problemdescription) - 2);
              $frameResponseObject->setProblemDescription($problemdescription);
              if ($_POST["action"] == "next") {
                  $page = $page - 1;
              } else {
                  $page = $page + 1;
              }
          } else {
              $save = false;
              if ($resultObject instanceof \steam_object) {
                  // save changes on result object
                  if (($admin == 1 && $questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT")) == 1 || $ownResult) {
                      $save = true;
                  }
              } else {
                  // create new result object
                  if (!isset($participants[$user->get_id()]) || $times == 0) {
                      $resultIDs = array();
                      if (isset($participants[$user->get_id()])) {
                          $resultIDs = $participants[$user->get_id()];
                      }
                      $resultObject = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "result" . time(), "", "text/plain", $resultContainer, "result" . time());
                      array_push($resultIDs, $resultObject->get_id());
                      $participants[$user->get_id()] = $resultIDs;
                      $resultContainer->set_attribute("QUESTIONNAIRE_PARTICIPANTS", $participants);
                      $save = true;
                  }
              }
              if ($save) {
                  $questionCounter = 0;
                  $pageCounter = 1;
                  foreach ($questions as $question) {
                      if ($question instanceof \Questionnaire\Model\AbstractQuestion) {
                          if ($pageCounter == $resultPage) {
                              if (isset($values[$questionCounter])) {
                                  $resultObject->set_attribute("QUESTIONNAIRE_ANSWER_" . $questionCounter, $values[$questionCounter]);
                              } else {
                                  $resultObject->set_attribute("QUESTIONNAIRE_ANSWER_" . $questionCounter, -1);
                              }
                          }
                          $questionCounter++;
                      } else if ($question instanceof \Questionnaire\Model\PageBreakLayoutElement) {
                          $pageCounter++;
                      }
                  }
                  $resultId = $resultObject->get_id();
              }
          }
      }

      // display success msg if there was a submit, else just display survey
      if ($_SERVER["REQUEST_METHOD"] == "POST" && ($page > $pages) && empty($errors) && $showPreview == 0) {
          if ($resultObject->get_attribute("QUESTIONNAIRE_RELEASED") == 0) {
              $resultObject->set_attribute("QUESTIONNAIRE_RELEASED", time());
              $resultCount = $resultContainer->get_attribute("QUESTIONNAIRE_RESULTS");
              $resultContainer->set_attribute("QUESTIONNAIRE_RESULTS", ($resultCount + 1));
          }

          $html = '
    <center>
      <h1>Ihre Antworten wurden erfolgreich gespeichert.</h1>
      <div style="text-align:center" class="buttons">
        <a class="bidButton" href="' . $QuestionnaireExtension->getExtensionUrl() . "Index/" . $questionnaire->get_id() . '/">Zurück zur Übersicht</a>
      </div>
    </center>';
      } else {
          $welcomePictureId = $survey->get_attribute("bid:rfb:picture_id");
          $welcomePictureWidth = $survey->get_attribute("bid:rfb:picture_width");
          if ($welcomePictureWidth === 0) {
              $welcomePictureWidth = "";
          }

          // display survey
          $content = $QuestionnaireExtension->loadTemplate("questionnaire_view.template.html");

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

          if($admin){
            $content->setVariable("QUESTIONNAIRE_NUMBER_SUBMISSIONS", $resultContainer->get_attribute("QUESTIONNAIRE_RESULTS"));
          }
          else{
            $content->setVariable("QUESTIONNAIRE_SHOW_NUMBER_SUBMISSIONS", "display: none;");
          }

          if($times == 0){
            $content->setVariable("QUESTIONNAIRE_MULTIPLE", "erlaubt");
          }
          else{
            $content->setVariable("QUESTIONNAIRE_MULTIPLE", "nicht erlaubt");
          }

          $ownSubmissions = "";
          // show users results in the table
      		if (isset($participants[$user->get_id()])) {
      			$submissions = $participants[$user->get_id()];
      			$count = 1;
      			foreach ($submissions as $submission) {
      				$submissionObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $submission);
              $ownSubmissions .= '<div class="value">';
      				if ($submissionObject->get_attribute("QUESTIONNAIRE_RELEASED") != 0) {
                $ownSubmissions .= $count . ": Abgegeben (" . date("d.m.Y H:i:s", $submissionObject->get_attribute("QUESTIONNAIRE_RELEASED")) . " Uhr)";
      				} else {
      					$questionCount = $survey->get_attribute("QUESTIONNAIRE_QUESTIONS");
      					$questionsAnswered = 0;
      					$attributeNames = $submissionObject->get_attribute_names();
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
          		$popupMenu->setElementId("submission-overlay");
          		$popupMenu->setParams(array(array("key" => "result", "value" => $submission), array("key" => "id", "value" => $survey->get_id())));
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

          $content->setVariable("QUESTIONNAIRE_SUBHEADLINE", $subheadline);

          if($hint == ""){
            $content->setCurrentBlock("BLOCK_VIEW_SURVEY");

            if ($pages > 1) {
                $content->setVariable("SURVEY_PAGE", "<br>Seite " . $page . " von " . $pages);
            }

            $content->setVariable("DISPLAY_BEGIN", "none");
            $content->setVariable("DISPLAY_END", "none");

            if ($admin == 0 | $active) {
                $content->setVariable("DISPLAY_EDIT", "none");
            }
            $content->setVariable("ASSET_URL", $QuestionnaireExtension->getAssetUrl() . "icons");

            $html = "";
            $counter = 0;
            $layoutCounter = 0;
            $pageCounter = 1;
            foreach ($questions as $question) {
                if ($question instanceof \Questionnaire\Model\AbstractLayoutElement) {
                    if ($pageCounter == $page) {
                        if ($question instanceof \Questionnaire\Model\JumpLabel) {
                            $html = $html . $question->getViewHTML(-1, $questions, $this->id, $this->params);
                        }else{
                            $html = $html . $question->getViewHTML();

                        }
                        //$counter++;
                    }
                    $layoutCounter++;
                    if ($question instanceof \Questionnaire\Model\PageBreakLayoutElement) {
                        $pageCounter++;
                    }
                } else {
                    if ($pageCounter == $page) {
                        if ($resultObject instanceof \steam_object && $showResult) {
                            $attributes = $resultObject->get_attribute_names();
                            if (!isset($values[$counter]) && !in_array($counter, $errors)) {
                                if (in_array("QUESTIONNAIRE_ANSWER_" . $counter, $attributes)) {
                                    $values[$counter] = $resultObject->get_attribute("QUESTIONNAIRE_ANSWER_" . $counter);
                                }
                            }
                        }
                        if (in_array($counter, $errors)) {
                            if (isset($values[$counter])) {
                                $html = $html . $question->getViewHTML($counter, $disabled, 1, $values[$counter]);
                            } else {
                                $html = $html . $question->getViewHTML($counter, $disabled, 1);
                            }
                        } else {
                            if (isset($values[$counter])) {
                                $html = $html . $question->getViewHTML($counter, $disabled, 0, $values[$counter]);
                            } else {
                                $html = $html . $question->getViewHTML($counter, $disabled, 0);
                            }
                        }
                    }
                    $counter++;
                }
            }
            $content->setVariable("QUESTIONS_HTML", $html);
            // construct next/previous/submit urls

            if($showResult){
              if($disabled){ //show result
                $placeholder = $resultId . "/1/";
              }
              else{ //edit result
                $placeholder = $resultId;
              }
            }
            if($showPreview){
              $placeholder = "preview";
            }

            if ($pages > $page) {
                $content->setVariable("NEXT_LABEL", "Nächste Seite");
                $content->setVariable("NEXT_URL", $QuestionnaireExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/" . ($page + 1) . "/" . $placeholder);
            } else if ($pages == $page) {
                if ($showPreview == 1 || $disabled == 1) {
                    $content->setVariable("DISPLAY_NEXT", "none");
                    $content->setVariable("NEXT_URL", $QuestionnaireExtension->getExtensionUrl() . "Index/" . $questionnaire->get_id() . "/");
                } else {
                    $content->setVariable("NEXT_LABEL", "Fragebogen abschließen");
                    $content->setVariable("NEXT_URL", $QuestionnaireExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/" . ($page + 1) . "/" . $placeholder);
                }
            } else {
                $content->setVariable("DISPLAY_NEXT", "none");
            }
            if ($page != 1 && $pages > 1) {
                $content->setVariable("PREVIOUS_LABEL", "Vorherige Seite");
                $content->setVariable("PREVIOUS_URL", $QuestionnaireExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/" . ($page - 1) . "/" . $placeholder);
            } else {
                $content->setVariable("DISPLAY_PREVIOUS", "none");
            }
            $content->parse("BLOCK_VIEW_SURVEY");
          }
          else{
            $content->setVariable("HINT", $hint);
          }
          $html = $content->get();
      }
      $rawWidget = new \Widgets\RawHtml();
      $PopupMenuStyle = \Widgets::getInstance()->readCSS("PopupMenu.css");
      $rawWidget->setHtml($html . "<style>" . $PopupMenuStyle . "</style>");
      $frameResponseObject->addWidget($rawWidget);
      return $frameResponseObject;
  }

}

?>

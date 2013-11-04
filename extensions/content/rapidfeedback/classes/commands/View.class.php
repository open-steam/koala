<?php

namespace Rapidfeedback\Commands;

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
        $rapidfeedback = $survey->get_environment();
        $RapidfeedbackExtension = \Rapidfeedback::getInstance();
        $survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
        $xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
        $survey_object->parseXML($xml);
        $questions = $survey_object->getQuestions();
        $user = $GLOBALS["STEAM"]->get_current_steam_user();
        $RapidfeedbackExtension->addCSS();
        $RapidfeedbackExtension->addJS();
        $times = $rapidfeedback->get_attribute("RAPIDFEEDBACK_PARTICIPATION_TIMES");
        $resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");

        // check if displaying preview
        $preview = 0;
        $resultOrPreview = "";
        if (isset($this->params[2])) {
            if ($this->params[2] == "preview") {
                $preview = 1;
                $resultOrPreview = "preview";
            }
            $resultOrPreview = $this->params[2];
        }

        // check which page should be displayed
        $pages = $survey->get_attribute("RAPIDFEEDBACK_PAGES");
        $page = 1;
        if (isset($this->params[1])) {
            $page = $this->params[1];
        }

        // check if input is disabled
        $disabled = 0;
        if (isset($this->params[3])) {
            $disabled = 1;
        }

        // get result object if displayed result
        $resultObject = "";
        if ($resultOrPreview != "preview" && $resultOrPreview != "") {
            $resultObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $resultOrPreview);
        }

        $allowed = false;

        // check if current user is admin
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

        // check if user is allowed to view survey
        $participants = $survey->get_attribute("RAPIDFEEDBACK_PARTICIPANTS");
        $state = $survey->get_attribute("RAPIDFEEDBACK_STATE");
        // if user is admin and is preview or view of someones result
        if ($admin == 1 && ($preview == 1 || $disabled == 1)) {
            $allowed = true;
        }
        // if user is admin and is editing someones result
        if ($admin == 1 && $resultObject instanceof \steam_object) {
            // own result
            if ($resultObject->get_creator()->get_id() == $user->get_id() && $state == 1 && (($rapidfeedback->get_attribute("RAPIDFEEDBACK_OWN_EDIT") == 1) || $resultObject->get_attribute("RAPIDFEEDBACK_RELEASED") == 0)) {
                $allowed = true;
                // other peoples result
            } else if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_ADMIN_EDIT") == 1) {
                $allowed = true;
            }
        }
        // if user is editing or viewing his own result and is allowed to
        if ($admin == 0 && $resultObject instanceof \steam_object) {
            if ($resultObject->get_creator()->get_id() == $user->get_id() && $state == 1 && (($rapidfeedback->get_attribute("RAPIDFEEDBACK_OWN_EDIT") == 1) || $resultObject->get_attribute("RAPIDFEEDBACK_RELEASED") == 0)) {
                $allowed = true;
            }
            if ($resultObject->get_creator()->get_id() == $user->get_id() && $disabled == 1) {
                $allowed = true;
            }
        }
        // if user or admin is starting a new result
        if ($state == 1 && $disabled == 0 && $preview == 0 && $resultOrPreview == "" && !(isset($participants[$user->get_id()]) && $times == 1)) {
            $allowed = true;
        }

        // user is not allowed to view this survey / result
        if (!$allowed) {
            $rawWidget = new \Widgets\RawHtml();
            $rawWidget->setHtml("<center>Zugang verwehrt. Sie dürfen diesen Fragebogen im Moment nicht ansehen/ausfüllen.</center>");
            $frameResponseObject->addWidget($rawWidget);
            return $frameResponseObject;
        }

        // collect user input if view got submitted (and check for errors)
        $values = array();
        $errors = array();
        if ($_SERVER["REQUEST_METHOD"] == "POST" && $preview == 0 && $disabled == 0) {
            $questionCounter = 0;
            $pageCounter = 1;
            if ($_POST["action"] == "next") {
                $resultPage = $page - 1;
            } else {
                $resultPage = $page + 1;
            }
            foreach ($questions as $question) {
                if ($question instanceof \Rapidfeedback\Model\AbstractQuestion) {
                    if ($resultPage == $pageCounter) {
                        if ($question instanceof \Rapidfeedback\Model\TextQuestion | $question instanceof \Rapidfeedback\Model\TextareaQuestion) {
                            $value = $_POST["question" . $questionCounter];
                            if ($question->getRequired() == 1 && trim($value) == "") {
                                array_push($errors, $questionCounter);
                            } else if (trim($value) == "") {
                                $values[$questionCounter] = -1;
                            } else {
                                $values[$questionCounter] = trim($value);
                            }
                        } else if ($question instanceof \Rapidfeedback\Model\SingleChoiceQuestion) {
                            if (!isset($_POST["question" . $questionCounter])) {
                                if ($question->getRequired() == 1) {
                                    array_push($errors, $questionCounter);
                                } else {
                                    $values[$questionCounter] = -1;
                                }
                            } else {
                                $values[$questionCounter] = $_POST["question" . $questionCounter];
                            }
                        } else if ($question instanceof \Rapidfeedback\Model\MultipleChoiceQuestion) {
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
                        } else if ($question instanceof \Rapidfeedback\Model\MatrixQuestion) {
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
                        } else if ($question instanceof \Rapidfeedback\Model\TendencyQuestion) {
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
                } else if ($question instanceof \Rapidfeedback\Model\PageBreakLayoutElement) {
                    $pageCounter++;
                }
            }
            // if there are errors show error msg, else save answers
            if (!empty($errors)) {
                $problemdescription = "Pflichtfragen nicht beantwortet: ";
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
                    if (($admin == 1 && $rapidfeedback->get_attribute("RAPIDFEEDBACK_ADMIN_EDIT")) == 1 || $resultObject->get_creator()->get_id() == $user->get_id()) {
                        $save = true;
                    }
                } else {
                    // create new result object
                    $participants = $resultContainer->get_attribute("RAPIDFEEDBACK_PARTICIPANTS");
                    if (!isset($participants[$user->get_id()]) || $times == 0) {
                        $resultIDs = array();
                        if (isset($participants[$user->get_id()])) {
                            $resultIDs = $participants[$user->get_id()];
                        }
                        $resultObject = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "result" . time(), "", "text/plain", $resultContainer, "result" . time());
                        array_push($resultIDs, $resultObject->get_id());
                        $participants[$user->get_id()] = $resultIDs;
                        $resultContainer->set_attribute("RAPIDFEEDBACK_PARTICIPANTS", $participants);
                        $save = true;
                    }
                }
                if ($save) {
                    $questionCounter = 0;
                    $pageCounter = 1;
                    foreach ($questions as $question) {
                        if ($question instanceof \Rapidfeedback\Model\AbstractQuestion) {
                            if ($pageCounter == $resultPage) {
                                if (isset($values[$questionCounter])) {
                                    $resultObject->set_attribute("RAPIDFEEDBACK_ANSWER_" . $questionCounter, $values[$questionCounter]);
                                } else {
                                    $resultObject->set_attribute("RAPIDFEEDBACK_ANSWER_" . $questionCounter, -1);
                                }
                            }
                            $questionCounter++;
                        } else if ($question instanceof \Rapidfeedback\Model\PageBreakLayoutElement) {
                            $pageCounter++;
                        }
                    }
                    $resultOrPreview = $resultObject->get_id();
                }
            }
        }

        // display actionbar
        $actionbar = new \Widgets\Actionbar();
        if ($admin == 1 && $preview == 1 && ($state == 0 || $state == 1)) {
            $actions = array(
                array("name" => "Fragebogen bearbeiten", "link" => $RapidfeedbackExtension->getExtensionUrl() . "edit/" . $rapidfeedback->get_id() . "/" . $survey->get_id() . "/"),
                array("name" => "Übersicht", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id() . "/")
            );
        } else {
            $actions = array(
                array("name" => "Übersicht", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id() . "/")
            );
        }
        $actionbar->setActions($actions);
        $frameResponseObject->addWidget($actionbar);

        // display success msg if there was a submit, else just display survey
        if ($_SERVER["REQUEST_METHOD"] == "POST" && ($page > $pages) && empty($errors) && $preview == 0) {
            if ($resultObject->get_attribute("RAPIDFEEDBACK_RELEASED") == 0) {
                $resultObject->set_attribute("RAPIDFEEDBACK_RELEASED", time());
                $resultCount = $resultContainer->get_attribute("RAPIDFEEDBACK_RESULTS");
                $resultContainer->set_attribute("RAPIDFEEDBACK_RESULTS", ($resultCount + 1));
            }

            $html = '
			<center>
				<h1>Ihre Antworten wurden erfolgreich gespeichert.</h1>
				<div style="text-align:center" class="buttons">
					<a class="button" href="' . $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id() . '/">Zurück zur Übersicht</a>
				</div>
			</center>';
        } else {
            $welcomePictureId = $survey->get_attribute("bid:rfb:picture_id");
            $welcomePictureWidth = $survey->get_attribute("bid:rfb:picture_width");
            if ($welcomePictureWidth === 0) {
                $welcomePictureWidth = "";
            }


            // display survey
            $content = $RapidfeedbackExtension->loadTemplate("rapidfeedback_view.template.html");
            $content->setCurrentBlock("BLOCK_VIEW_SURVEY");
            if ($preview == 1) {
                $content->setVariable("SURVEY_NAME", $survey_object->getName() . " (Vorschau)");
            } else {
                $content->setVariable("SURVEY_NAME", $survey_object->getName());
            }
            if ($pages > 1) {
                $content->setVariable("SURVEY_PAGE", "<br>Seite " . $page . " von " . $pages);
            }
            if (trim($survey_object->getBeginText()) == "" || $page > 1) {
                $content->setVariable("DISPLAY_BEGIN", "none");
            } else {
                if ($welcomePictureId !== 0) {
                    $picUrl = getDownloadUrlForObjectId($welcomePictureId);
                    
                    $content->setVariable("SURVEY_BEGIN", nl2br($survey_object->getBeginText()) . '<br><br><img src="' . $picUrl . '" width="' . $welcomePictureWidth . '">');
                } else {
                    $content->setVariable("SURVEY_BEGIN", nl2br($survey_object->getBeginText()));
                }
            }
            if (trim($survey_object->getEndText()) == "" || $page != $pages) {
                $content->setVariable("DISPLAY_END", "none");
            } else {
                $content->setVariable("SURVEY_END", nl2br($survey_object->getEndText()));
            }
            $state = $survey->get_attribute("RAPIDFEEDBACK_STATE");
            if ($admin == 0 | $state != 0) {
                $content->setVariable("DISPLAY_EDIT", "none");
            }
            $content->setVariable("ASSET_URL", $RapidfeedbackExtension->getAssetUrl() . "icons");

            $html = "";
            $counter = 0;
           $layoutCounter = 0;
            $pageCounter = 1;
            foreach ($questions as $question) {
                if ($question instanceof \Rapidfeedback\Model\AbstractLayoutElement) {
                    if ($pageCounter == $page) {
                        $html = $html . $question->getViewHTML($counter +1);
                         $counter++;
                    }
                    $layoutCounter++;
                    if ($question instanceof \Rapidfeedback\Model\PageBreakLayoutElement) {
                        $pageCounter++;
                    }
                } else {
                    if ($pageCounter == $page) {
                        if ($resultObject instanceof \steam_object) {
                            $attributes = $resultObject->get_attribute_names();
                            if (!isset($values[$counter]) && !in_array($counter, $errors)) {
                                if (in_array("RAPIDFEEDBACK_ANSWER_" . $counter, $attributes)) {
                                    $values[$counter] = $resultObject->get_attribute("RAPIDFEEDBACK_ANSWER_" . $counter);
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
            if ($resultOrPreview != "" && $disabled == 1) {
                $resultOrPreview = $resultOrPreview . "1";
            }
            if ($pages > $page) {
                $content->setVariable("NEXT_LABEL", "Nächste Seite");
                $content->setVariable("NEXT_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/" . ($page + 1) . "/" . $resultOrPreview);
            } else if ($pages == $page) {
                if ($preview == 1 || $disabled == 1) {
                    $content->setVariable("NEXT_LABEL", "Zurück zur Übersicht");
                    $content->setVariable("NEXT_URL", $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id() . "/");
                } else {
                    $content->setVariable("NEXT_LABEL", "Fragebogen abschließen");
                    $content->setVariable("NEXT_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/" . ($page + 1) . "/" . $resultOrPreview);
                }
            } else {
                $content->setVariable("DISPLAY_NEXT", "none");
            }
            if ($page != 1 && $pages > 1) {
                $content->setVariable("PREVIOUS_LABEL", "Vorherige Seite");
                $content->setVariable("PREVIOUS_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/" . ($page - 1) . "/" . $resultOrPreview);
            } else {
                $content->setVariable("DISPLAY_PREVIOUS", "none");
            }
            $content->parse("BLOCK_VIEW_SURVEY");
            $html = $content->get();
        }

        $rawWidget = new \Widgets\RawHtml();
        $rawWidget->setHtml($html);
        $frameResponseObject->addWidget($rawWidget);
        return $frameResponseObject;
    }

}

?>
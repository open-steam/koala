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
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		}
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$rapidfeedback = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[1]);
		$survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
		$xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
		$survey_object->parseXML($xml);
		$questions = $survey_object->getQuestions();
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$RapidfeedbackExtension->addCSS();
		
		// check if current user is admin
		$staff = $rapidfeedback->get_attribute("RAPIDFEEDBACK_STAFF");
		$admin = 0;
		if (($staff instanceof \steam_group && $staff->is_member($user)) || $staff instanceof \steam_user && $staff->get_id() == $user->get_id()) {
			$admin = 1;
		}
		
		// collect user input if view got submitted (and check for errors)
		$values = array();
		$errors = array();
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_survey"])) {
			$questionCounter = 0;
			foreach ($questions as $question) {
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
				}
				$questionCounter++;
			}
			
			// if there are errors show error msg, else save answers
			if (!empty($errors)) {
				$problemdescription = "Erforderliche Fragen nicht beantwortet: ";
				foreach ($errors as $error) {
					$problemdescription = $problemdescription . ($error+1) . ", ";
				}
				$problemdescription = substr($problemdescription, 0, strlen($problemdescription)-2);
				$frameResponseObject->setProblemDescription($problemdescription);
			} else {
				$participants = $survey->get_attribute("RAPIDFEEDBACK_PARTICIPANTS");
				$adminsAllowed = $rapidfeedback->get_attribute("RAPIDFEEDBACK_ADMIN_SURVEY");
				if (!in_array($user->get_id(), $participants) && (($admin == 1 && $adminsAllowed == 1) | ($admin == 0))) {
					$resultCount = $survey->get_attribute("RAPIDFEEDBACK_RESULTS");
					array_push($participants, $user->get_id());
					$survey->set_attribute("RAPIDFEEDBACK_PARTICIPANTS", $participants);
					$survey->set_attribute("RAPIDFEEDBACK_RESULTS", ($resultCount+1));
					$resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
					$resultObject = \steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "results" . $user->get_id(), "", "text/plain", $resultContainer, "Results of user ". $user->get_id());
					$questionCounter = 0;
					foreach ($questions as $question) {
						if (isset($values[$questionCounter])) {
							$resultObject->set_attribute("RAPIDFEEDBACK_ANSWER_" . $questionCounter, $values[$questionCounter]);
						} else {
							$resultObject->set_attribute("RAPIDFEEDBACK_ANSWER_" . $questionCounter, -1);
						}
						$questionCounter++;
					}
				}
			}
		}
		
		// display success msg if there was a submit, else just display survey
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_survey"]) && empty($errors)) {
			$headline_label = "Antworten gespeichert";
			$html = '
			<center>
				<h1>Ihre Antworten wurden erfolgreich gespeichert.</h1>
				<div style="text-align:center" class="buttons">
					<a class="button" href="' . $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id() . '">Zurück zur Übersicht</a>
				</div>
			</center>
			';
		} else {
			$content = $RapidfeedbackExtension->loadTemplate("rapidfeedback_view.template.html");
			$content->setCurrentBlock("BLOCK_VIEW_SURVEY");
			$content->setVariable("SURVEY_NAME", $survey_object->getName());
			$content->setVariable("SURVEY_BEGIN", $survey_object->getBeginText());
			$content->setVariable("SURVEY_END", $survey_object->getEndText());
			$content->setVariable("QUESTION_NEEDED", "Erforderlich");
			$state = $survey->get_attribute("RAPIDFEEDBACK_STATE");
			if ($admin == 0 | $state != 0) {
				$content->setVariable("DISPLAY_EDIT", "none");
			}
			$content->setVariable("ASSET_URL", $RapidfeedbackExtension->getAssetUrl() . "icons");
			$content->setVariable("EDIT_TITLE", "Umfrage bearbeiten");
			$content->setVariable("EDIT_URL", $RapidfeedbackExtension->getExtensionUrl() . "edit/" . $this->id . "/" . $survey->get_id());
				
			$html = "";
			$counter = 0;
			foreach ($questions as $question) {
				if (in_array($counter, $errors)) {
					if (isset($values[$counter])) {
						$html = $html . $question->getViewHTML($counter, 1, $values[$counter]);
					} else {
						$html = $html . $question->getViewHTML($counter, 1);
					}
				} else {
					if (isset($values[$counter])) {
						$html = $html . $question->getViewHTML($counter, 0, $values[$counter]);
					} else {
						$html = $html . $question->getViewHTML($counter, 0);
					}
				}
				$counter++;
			}
			$content->setVariable("QUESTIONS_HTML", $html);
			$content->setVariable("SUBMIT_SURVEY", "Antworten abschicken");
			$preview = 0;
			if (isset($this->params[2])) {
				$preview = 1;
			}
			if ($preview == 1) {
				$content->setVariable("DISPLAY_SUBMIT", "none");
				$headline_label = "Umfrage: Vorschau";
			} else {
				$headline_label = "Umfrage ausfüllen";
			}
			$content->setVariable("BACK_LABEL", "Zurück");
			$content->setVariable("BACK_URL", $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id());
			$content->parse("BLOCK_VIEW_SURVEY");
			$html = $content->get();
		}
		
		$group = $rapidfeedback->get_attribute("RAPIDFEEDBACK_GROUP");
		if ($group->get_name() == "learners") {
			$parent = $group->get_parent_group();
			$courseOrGroup = "Kurs: " . $parent->get_attribute("OBJ_DESC") . " (" . $parent->get_name() . ")";
			$courseOrGroupUrl = PATH_URL . "semester/" . $parent->get_id();
		} else {
			$courseOrGroup = "Gruppe: " . $group->get_name();
			$courseOrGroupUrl = PATH_URL . "groups/" . $group->get_id();
		}
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($html);
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
			array("name" => "Rapid Feedback", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id()),
			array("name" => $headline_label)
		));
		return $frameResponseObject;
	}
}
?>
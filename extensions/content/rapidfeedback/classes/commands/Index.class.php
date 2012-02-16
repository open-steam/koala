<?php
namespace Rapidfeedback\Commands;
class Index extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$rapidfeedback = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$RapidfeedbackExtension->addJS();
		
		// admin action (start, stop, copy, delete) got submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["admin_action"])) {
			$element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_POST["element_id"]);
			if ($element instanceof \steam_object) {
				switch ($_POST["admin_action"]) {
					case 1:
						$element->set_attribute("RAPIDFEEDBACK_STATE", 1);
						break;
					case 2:
						$element->set_attribute("RAPIDFEEDBACK_STATE", 2);
						break;
					case 3:
						$copy = \steam_factory::create_copy($GLOBALS["STEAM"]->get_id(), $element);
						$copy->move($rapidfeedback);
						$copy->set_attribute("RAPIDFEEDBACK_PARTICIPANTS", array());
						$copy->set_attribute("RAPIDFEEDBACK_STATE", 0);
						$copy->set_attribute("RAPIDFEEDBACK_RESULTS", 0);
						$copy->set_attribute("RAPIDFEEDBACK_STARTTYPE", 0);
						$resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $copy->get_path() . "/results");
						$results = $resultContainer->get_inventory();
						foreach ($results as $result) {
							$result->delete();
						}
						break;
					case 4:
						$element->delete();
						break;
				}
			}
		}
		
		// edit configuration got submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit_rapidfeedback"])) {
			$rapidfeedback->set_name($_POST["title"]);
			$rapidfeedback->set_attribute("OBJ_DESC", $_POST["desc"]);
			if (isset($_POST["adminsurvey"]) && $_POST["adminsurvey"] == "on") {
				$rapidfeedback->set_attribute("RAPIDFEEDBACK_ADMIN_SURVEY", 1);
			} else {
				$rapidfeedback->set_attribute("RAPIDFEEDBACK_ADMIN_SURVEY", 0);
			}
		}
		
		// create/edit survey got submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_survey"])) {
			$survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
			$survey_object->setName($_POST["title"]);
			$survey_object->setBeginText($_POST["begintext"]);
			$survey_object->setEndText($_POST["endtext"]);
			
			$questioncounter = 0;
			$sortedQuestions = $_POST["sortable_array"];
			$sortedQuestions != '' ? ($sortedQuestions = explode(',',$sortedQuestions)) : '';
			foreach ($sortedQuestions as $question) {
				if ($question != "newquestion" && $question != "newlayout" && $question != "") {
					$questionValues = $_POST[$question];
					$questionValues != '' ? ($questionValues = explode(',',$questionValues)) : '';
					switch ($questionValues[0]) {
						case 0:
							$newquestion = new \Rapidfeedback\Model\TextQuestion();
							break;
						case 1:
							$newquestion = new \Rapidfeedback\Model\TextareaQuestion();
							break;
						case 2:
							$newquestion = new \Rapidfeedback\Model\SingleChoiceQuestion();
							$options = $_POST[$question . "_options"];
							$options != '' ? ($options = explode(',',$options)) : '';
							foreach ($options as $option) {
								$newquestion->addOption(rawurldecode($option));
							}
							$newquestion->setArrangement($questionValues[4]);
							break;
						case 3:
							$newquestion = new \Rapidfeedback\Model\MultipleChoiceQuestion();
							$options = $_POST[$question . "_options"];
							$options != '' ? ($options = explode(',',$options)) : '';
							foreach ($options as $option) {
								$newquestion->addOption(rawurldecode($option));
							}
							$newquestion->setArrangement($questionValues[4]);
							break;
						case 4:
							$newquestion = new \Rapidfeedback\Model\MatrixQuestion();
							$columns = $_POST[$question . "_columns"];
							$columns != '' ? ($columns = explode(',',$columns)) : '';
							foreach ($columns as $column) {
								$newquestion->addcolumn(rawurldecode($column));
							}
							$rows = $_POST[$question . "_rows"];
							$rows != '' ? ($rows = explode(',',$rows)) : '';
							foreach ($rows as $row) {
								$newquestion->addRow(rawurldecode($row));
							}
							break;
						case 5:
							$newquestion = new \Rapidfeedback\Model\GradingQuestion();
							$options = $_POST[$question . "_rows"];
							$options != '' ? ($options = explode(',',$options)) : '';
							foreach ($options as $option) {
								$newquestion->addRow(rawurldecode($option));
							}
							break;
						case 6:
							$newquestion = new \Rapidfeedback\Model\TendencyQuestion();
							$options = $_POST[$question . "_options"];
							$options != '' ? ($options = explode(',',$options)) : '';
							$newquestion->setSteps($questionValues[4]);
							for ($count = 0; $count < count($options); $count = $count+2) {
								$newquestion->addOption(array($options[$count], $options[$count+1]));
							}
							break;
					}
					$newquestion->setQuestionText(rawurldecode($questionValues[1]));
					$newquestion->setHelpText(rawurldecode($questionValues[2]));
					$newquestion->setRequired($questionValues[3]);
					$survey_object->addQuestion($newquestion);
				}
			}
			if ($_POST["starttype"] == 1) {
				$survey_object->setStartType(1, $_POST["begin"], $_POST["end"]);
			} else {
				$survey_object->setStartType(0);
			}
			if (isset($this->params[1])) {
				$survey_object->createSurvey($this->params[1]);
			} else {
				$survey_object->createSurvey();	
			}
		}
		
		// display actionbar if current user is admin
		$staff = $rapidfeedback->get_attribute("RAPIDFEEDBACK_STAFF");
		$admin = 0;
		if (($staff instanceof \steam_group && $staff->is_member($user)) || $staff instanceof \steam_user && $staff->get_id() == $user->get_id()) {
			$admin = 1;
			$actionbar = new \Widgets\Actionbar();
			$actions = array(
				array("name" => "Konfiguration" , "link" => $RapidfeedbackExtension->getExtensionUrl() . "configuration/" . $this->id),
				array("name" => "Import" , "link" => $RapidfeedbackExtension->getExtensionUrl() . "import/" . $this->id),
				array("name" => "Umfrage erstellen" , "link" => $RapidfeedbackExtension->getExtensionUrl() . "edit/" . $this->id),
			);
			$actionbar->setActions($actions);
			$frameResponseObject->addWidget($actionbar);
		}
		
		// get the surveys that are to be shown and sort them
		$surveys = $rapidfeedback->get_inventory();
		$surveys_inactive = array();
		$surveys_running = array();
		$surveys_ended = array();
		foreach ($surveys as $survey) {
			if ($survey instanceof \steam_container && !($survey instanceof \steam_user)) {
				$starttype = $survey->get_attribute("RAPIDFEEDBACK_STARTTYPE");
				$state = $survey->get_attribute("RAPIDFEEDBACK_STATE");
				// if survey is started/ended automatically check the times
				if (is_array($starttype)) {
					if (time() > $starttype[1] && $state == 0) {
						$survey->set_attribute("RAPIDFEEDBACK_STATE", 1);
					} 
					if (time() > $starttype[0] && $state == 1) {
						$survey->set_attribute("RAPIDFEEDBACK_STATE", 2);
					}
				}
				$state = $survey->get_attribute("RAPIDFEEDBACK_STATE");
				if ($state == 0) {
					array_push($surveys_inactive, $survey);
				} else if ($state == 1) {
					$participants = $survey->get_attribute("RAPIDFEEDBACK_PARTICIPANTS");
					if ($admin == 1 || !in_array($user->get_id(), $participants)) {
						array_push($surveys_running, $survey);
					}
				} else {
					array_push($surveys_ended, $survey);
				}
			}
		}
		usort($surveys_inactive, "sort_workplans");
		usort($surveys_running, "sort_workplans");
		usort($surveys_ended, "sort_workplans");
		if ($admin == 1) {
			$surveys = array_merge($surveys_inactive, $surveys_running, $surveys_ended);
		} else {
			$surveys = $surveys_running;
		}
		
		// display surveys
		$content = $RapidfeedbackExtension->loadTemplate("rapidfeedback_index.template.html");
		if (count($surveys) == 0) {
			$content->setCurrentBlock("BLOCK_NO_SURVEYS");
			$content->setVariable("NO_SURVEYS", "Keine Umfragen vorhanden.");
			$content->setVariable("RAPIDFEEDBACK_NAME", $rapidfeedback->get_name());
			if ($rapidfeedback->get_attribute("OBJ_DESC") != "0") {
				$content->setVariable("RAPIDFEEDBACK_DESC", $rapidfeedback->get_attribute("OBJ_DESC"));
			}
			$content->parse("BLOCK_NO_SURVEYS");
		} else {
			$content->setCurrentBlock("BLOCK_SURVEY_TABLE");
			$content->setVariable("RAPIDFEEDBACK_NAME", $rapidfeedback->get_name());
			if ($rapidfeedback->get_attribute("OBJ_DESC") != "0") {
				$content->setVariable("RAPIDFEEDBACK_DESC", $rapidfeedback->get_attribute("OBJ_DESC"));
			}
			$content->setVariable("NAME_LABEL", "Name der Umfrage");
			$content->setVariable("STATUS_LABEL", "Status");
			$content->setVariable("QUESTIONS_LABEL", "Anzahl der Fragen");
			$content->setVariable("RESULTS_LABEL", "Anzahl der Abgaben");
			$content->setVariable("ACTIONS_LABEL", "Aktionen");
		
			foreach ($surveys as $survey) {
				$content->setCurrentBlock("BLOCK_SURVEY_ELEMENT");
				$content->setVariable("NAME_VALUE", $survey->get_name());
				$participants = $survey->get_attribute("RAPIDFEEDBACK_PARTICIPANTS");
				$state = $survey->get_attribute("RAPIDFEEDBACK_STATE");
				$adminsAllowed = $rapidfeedback->get_attribute("RAPIDFEEDBACK_ADMIN_SURVEY");
				if ($admin == 1 && (in_array($user->get_id(), $participants) | $state != 1 | $adminsAllowed == 0)) {
					$content->setVariable("DISPLAY_LINK", "none");
					$content->setVariable("NAME_DONE", $survey->get_name());
				}
				$starttype = $survey->get_attribute("RAPIDFEEDBACK_STARTTYPE");
				if ($state == 0) {
					if (is_array($starttype)) {
						$content->setVariable("STATE_VALUE", "Inaktiv (Start: " . date('d.m.Y', $starttype[1]) . ")");
						$content->setVariable("DISPLAY_START", "none");
					} else {
						$content->setVariable("STATE_VALUE", "Inaktiv");
					}
					$content->setVariable("DISPLAY_RESULTS", "none");
					$content->setVariable("DISPLAY_STOP", "none");
					$content->setVariable("DISPLAY_REPEAT", "none");
				} else if ($state == 1) {
					if (is_array($starttype)) {
						$content->setVariable("STATE_VALUE", "Aktiv (Ende: " . date('d.m.Y', $starttype[0]) . ")");
						$content->setVariable("DISPLAY_STOP", "none");
					} else {
						$content->setVariable("STATE_VALUE", "Aktiv");
					}
					$content->setVariable("DISPLAY_EDIT", "none");
					$content->setVariable("DISPLAY_START", "none");
					$content->setVariable("DISPLAY_REPEAT", "none");
					if ($survey->get_attribute("RAPIDFEEDBACK_RESULTS") == 0) {
						$content->setVariable("DISPLAY_RESULTS", "none");
					} 
				} else {
					$content->setVariable("STATE_VALUE", "Beendet");
					$content->setVariable("DISPLAY_EDIT", "none");
					$content->setVariable("DISPLAY_START", "none");
					$content->setVariable("DISPLAY_STOP", "none");
					if ($survey->get_attribute("RAPIDFEEDBACK_RESULTS") == 0) {
						$content->setVariable("DISPLAY_RESULTS", "none");
					}
				}
				$content->setVariable("QUESTIONS_VALUE", $survey->get_attribute("RAPIDFEEDBACK_QUESTIONS"));
				$content->setVariable("RESULTS_VALUE", $survey->get_attribute("RAPIDFEEDBACK_RESULTS"));
				$content->setVariable("ASSET_URL", $RapidfeedbackExtension->getAssetUrl() . "icons");
				$content->setVariable("PREVIEW_TITLE", "Vorschau");
				$content->setVariable("VIEW_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $this->id . "/" . $survey->get_id());
				$content->setVariable("PREVIEW_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $this->id . "/" . $survey->get_id() . "/1");
				$content->setVariable("EDIT_TITLE", "Umfrage bearbeiten");
				$content->setVariable("EDIT_URL", $RapidfeedbackExtension->getExtensionUrl() . "edit/" . $this->id . "/" . $survey->get_id());
				$content->setVariable("RESULTS_TITLE", "Auswertung");
				$content->setVariable("RESULTS_URL", $RapidfeedbackExtension->getExtensionUrl() . "results/" . $this->id . "/" . $survey->get_id());
				$content->setVariable("DELETE_TITLE", "Umfrage löschen");
				$content->setVariable("START_TITLE", "Umfrage starten");
				$content->setVariable("STOP_TITLE", "Umfrage beenden");
				$content->setVariable("REPEAT_TITLE", "Umfrage wiederholen");
				$content->setVariable("ELEMENT_ID", $survey->get_id());
				if ($admin == 0) {
					$content->setVariable("DISPLAY_ADMIN_ELEMENT", "none");
				}
				$content->parse("BLOCK_SURVEY_ELEMENT");
			}
			if ($admin == 0) {
				$content->setVariable("DISPLAY_ADMIN", "none");
			}
			$content->parse("BLOCK_SURVEY_TABLE");
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
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
			array("name" => "Rapid Feedback")
		));
		return $frameResponseObject;
	}
}
?>
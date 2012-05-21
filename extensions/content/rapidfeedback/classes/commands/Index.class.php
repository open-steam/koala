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
    
		// check if current user is admin
		$staff = $rapidfeedback->get_attribute("RAPIDFEEDBACK_STAFF");
		$admin = 0;
		$allowed = false;
		foreach ($staff as $group) {
			if ($group->is_member($user)) {
				$admin = 1;
				break;
			}
		}
		if ($rapidfeedback->get_creator()->get_id() == $user->get_id()) {
			$admin = 1;
			$allowed = true;
		}
		
		// redirect if there is no survey
		$surveyCount = 0;
		$surveys = $rapidfeedback->get_inventory();
		foreach ($surveys as $survey) {
			if ($survey instanceof \steam_container && !($survey instanceof \steam_user)) {
				$surveyCount++;
			}
		}
		if ($admin == 1 && $surveyCount == 0) {
			header('Location: ' . $RapidfeedbackExtension->getExtensionUrl() . "edit/" . $this->id);
    		die('Redirect');
		}
    	
		// display actionbar for admin
		if ($admin == 1) {
			$actionbar = new \Widgets\Actionbar();
			$actions = array(
				array("name" => "Neuen Fragebogen erstellen" , "link" => $RapidfeedbackExtension->getExtensionUrl() . "edit/" . $this->id),
				array("name" => "Import" , "link" => $RapidfeedbackExtension->getExtensionUrl() . "import/" . $this->id),
				array("name" => "Konfiguration" , "link" => $RapidfeedbackExtension->getExtensionUrl() . "configuration/" . $this->id),
				array("name" => "Übersicht", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $this->id)
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
					array_push($surveys_running, $survey);
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
			if ($admin == 1) {
				$content->setVariable("NO_SURVEYS", "Keine Fragebögen vorhanden.");
			} else {
				$content->setVariable("NO_SURVEYS", "Keine aktiven Fragebögen vorhanden.");
			}
			$content->setVariable("RAPIDFEEDBACK_NAME", $rapidfeedback->get_name());
			if ($rapidfeedback->get_attribute("OBJ_DESC") != "0") {
				$content->setVariable("RAPIDFEEDBACK_DESC", nl2br($rapidfeedback->get_attribute("OBJ_DESC")));
			}
			$content->parse("BLOCK_NO_SURVEYS");
		} else {
			$content->setCurrentBlock("BLOCK_SURVEY_TABLE");
			$content->setVariable("RAPIDFEEDBACK_NAME", $rapidfeedback->get_name());
			if ($rapidfeedback->get_attribute("OBJ_DESC") != "0") {
				$content->setVariable("RAPIDFEEDBACK_DESC", nl2br($rapidfeedback->get_attribute("OBJ_DESC")));
			}
			$content->setVariable("NAME_LABEL", "Name des Fragebogens");
			$content->setVariable("STATUS_LABEL", "Status");
			$content->setVariable("QUESTIONS_LABEL", "Anzahl der Fragen");
			$content->setVariable("RESULTS_LABEL", "Anzahl der Abgaben");
			$content->setVariable("ACTIONS_LABEL", "Aktionen");
		
			foreach ($surveys as $survey) {
				$content->setCurrentBlock("BLOCK_SURVEY_ELEMENT");
				$content->setVariable("NAME_VALUE", $survey->get_attribute("OBJ_DESC"));
				$resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
				$participants = $resultContainer->get_attribute("RAPIDFEEDBACK_PARTICIPANTS");
				$state = $survey->get_attribute("RAPIDFEEDBACK_STATE");
				$groups = $rapidfeedback->get_attribute("RAPIDFEEDBACK_GROUP");
				foreach ($groups as $group) {
					if ($group->is_member($user)) {
						$allowed = true;
						break;
					}
				}
				$times = $rapidfeedback->get_attribute("RAPIDFEEDBACK_PARTICIPATION_TIMES");
				if ((isset($participants[$user->get_id()]) && $times == 1) || $state != 1) {
					$allowed  = false;
				}
				if ($allowed) {
					$content->setVariable("VIEW_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/1");
					$content->setVariable("VIEW_TITLE", "Fragebogen ausfüllen");
				} else {
					$content->setVariable("DISPLAY_VIEW", "none");
					$content->setVariable("NAME_VALUE_NOLINK", $survey->get_attribute("OBJ_DESC"));
				}
				$starttype = $survey->get_attribute("RAPIDFEEDBACK_STARTTYPE");
				if ($state == 0) {
					if (is_array($starttype)) {
						$content->setVariable("STATE_VALUE", "Inaktiv (Start: " . date('d.m.Y H:i', $starttype[1]) . ")");
						$content->setVariable("DISPLAY_START", "none");
					} else {
						$content->setVariable("STATE_VALUE", "Inaktiv");
					}
					$content->setVariable("DISPLAY_RESULTS", "none");
					$content->setVariable("DISPLAY_STOP", "none");
					$content->setVariable("DISPLAY_REPEAT", "none");
				} else if ($state == 1) {
					if (is_array($starttype)) {
						$content->setVariable("STATE_VALUE", "Aktiv (Ende: " . date('d.m.Y H:i', $starttype[0]) . ")");
						$content->setVariable("DISPLAY_STOP", "none");
					} else {
						$content->setVariable("STATE_VALUE", "Aktiv");
					}
					$content->setVariable("DISPLAY_START", "none");
					$content->setVariable("DISPLAY_REPEAT", "none");
					if ($resultContainer->get_attribute("RAPIDFEEDBACK_RESULTS") == 0) {
						$content->setVariable("DISPLAY_RESULTS", "none");
					} 
				} else {
					$content->setVariable("STATE_VALUE", "Beendet");
					$content->setVariable("DISPLAY_EDIT", "none");
					$content->setVariable("DISPLAY_START", "none");
					$content->setVariable("DISPLAY_STOP", "none");
					if ($resultContainer->get_attribute("RAPIDFEEDBACK_RESULTS") == 0) {
						$content->setVariable("DISPLAY_RESULTS", "none");
					}
				}
				$content->setVariable("QUESTIONS_VALUE", $survey->get_attribute("RAPIDFEEDBACK_QUESTIONS"));
				$content->setVariable("RESULTS_VALUE", $resultContainer->get_attribute("RAPIDFEEDBACK_RESULTS"));
				$content->setVariable("ASSET_URL", $RapidfeedbackExtension->getAssetUrl() . "icons");
				$content->setVariable("PREVIEW_TITLE", "Vorschau");
				$content->setVariable("PREVIEW_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/1/preview");
				$content->setVariable("EDIT_TITLE", "Fragebogen bearbeiten");
				$content->setVariable("EDIT_URL", $RapidfeedbackExtension->getExtensionUrl() . "edit/" . $this->id . "/" . $survey->get_id());
				$content->setVariable("RESULTS_TITLE", "Auswertung");
				$content->setVariable("RESULTS_URL", $RapidfeedbackExtension->getExtensionUrl() . "individualResults/" . $survey->get_id());
				$content->setVariable("DELETE_TITLE", "Fragebogen löschen");
				$content->setVariable("START_TITLE", "Fragebogen starten");
				$content->setVariable("STOP_TITLE", "Fragebogen beenden");
				$content->setVariable("REPEAT_TITLE", "Fragebogen wiederholen");
				$content->setVariable("RF_VALUE", $rapidfeedback->get_id());
				$content->setVariable("ELEMENT_ID", $survey->get_id());
				if ($admin == 0) {
					$content->setVariable("DISPLAY_ADMIN_ELEMENT", "none");
					$content->setVariable("DISPLAY_RESULTS", "none");
					$content->setVariable("DISPLAY_EDIT", "none");
					$content->setVariable("DISPLAY_START", "none");
					$content->setVariable("DISPLAY_STOP", "none");
					$content->setVariable("DISPLAY_REPEAT", "none");
				}
				
				// show users results in the table
				if (isset($participants[$user->get_id()])) {
					$content->setCurrentBlock("BLOCK_SURVEY_PARTICIPATION");
					$content->setVariable("PARTICIPATION_LABEL", "Teilnahme");
					$content->setVariable("SUBMITDATE_LABEL", "Abgabedatum");
					$content->setVariable("PARTICIPATION_ACTIONS_LABEL", "Aktionen");
					$results = $participants[$user->get_id()];
					$count = 1;
					foreach ($results as $result) {
						$resultObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $result);
						$content->setCurrentBlock("BLOCK_SURVEY_ONE_PARTICIPATION");
						$content->setVariable("PARTICIPATION_TIME", $count);
						if ($resultObject->get_attribute("RAPIDFEEDBACK_RELEASED") != 0) {
							$content->setVariable("SUBMITDATE", date("d.m.Y H:i:s", $resultObject->get_attribute("RAPIDFEEDBACK_RELEASED")));
						} else {
							$questionCount = $survey->get_attribute("RAPIDFEEDBACK_QUESTIONS");
							$questionsAnswered = 0;
							$attributeNames = $resultObject->get_attribute_names();
							for ($count2 = 0; $count2 < $questionCount; $count2++) {
								if (in_array("RAPIDFEEDBACK_ANSWER_" . $count2, $attributeNames)) {
									$questionsAnswered++;
								}
							}
							$content->setVariable("SUBMITDATE", "noch nicht abgegeben (" . $questionsAnswered . " von " . $questionCount . " Fragen beantwortet)");
						}
						$content->setVariable("P_ASSET_URL", $RapidfeedbackExtension->getAssetUrl() . "icons");
						$content->setVariable("P_VIEW_TITLE", "Details");
						$content->setVariable("P_VIEW_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/1/" . $result . "/1");
						if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_OWN_EDIT") == 1 || $resultObject->get_attribute("RAPIDFEEDBACK_RELEASED") == 0) {
							$content->setVariable("P_EDIT_TITLE", "Bearbeiten");
							$content->setVariable("P_EDIT_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/1/" . $result);
						} else {
							$content->setVariable("P_DISPLAY_EDIT", "none");
						}
						$content->setVariable("P_DELETE_TITLE", "Löschen");
						$content->setVariable("RESULT_ID", $result);
						$content->setVariable("RESULT_RF", $rapidfeedback->get_id());
						$content->setVariable("RESULT_SURVEY", $survey->get_id());
						$content->parse("BLOCK_SURVEY_ONE_PARTICIPATION");
						$count++;
					}
					$content->parse("BLOCK_SURVEY_PARTICIPATION");
				}
				$content->parse("BLOCK_SURVEY_ELEMENT");
			}
			if ($admin == 0) {
				$content->setVariable("DISPLAY_ADMIN", "none");
			}
			$content->parse("BLOCK_SURVEY_TABLE");
		}
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
}
?>
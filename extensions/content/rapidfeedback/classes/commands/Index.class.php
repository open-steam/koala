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
			$content->setVariable("NO_SURVEYS", "Keine offenen Umfragen vorhanden.");
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
			$content->setVariable("NAME_LABEL", "Name der Umfrage");
			$content->setVariable("STATUS_LABEL", "Status");
			$content->setVariable("QUESTIONS_LABEL", "Anzahl der Fragen");
			$content->setVariable("RESULTS_LABEL", "Anzahl der Abgaben");
			$content->setVariable("ACTIONS_LABEL", "Aktionen");
		
			foreach ($surveys as $survey) {
				$content->setCurrentBlock("BLOCK_SURVEY_ELEMENT");
				$content->setVariable("NAME_VALUE", $survey->get_attribute("OBJ_DESC"));
				$participants = $survey->get_attribute("RAPIDFEEDBACK_PARTICIPANTS");
				$state = $survey->get_attribute("RAPIDFEEDBACK_STATE");
				$adminsAllowed = $rapidfeedback->get_attribute("RAPIDFEEDBACK_ADMIN_SURVEY");
				if ($admin == 1 && (in_array($user->get_id(), $participants) | $state != 1 | $adminsAllowed == 0)) {
					$content->setVariable("DISPLAY_LINK", "none");
					$content->setVariable("NAME_DONE", $survey->get_attribute("OBJ_DESC"));
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
				$content->setVariable("RF_VALUE", $rapidfeedback->get_id());
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
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array( 
			array("name" => "Rapid Feedback")
		));
		return $frameResponseObject;
	}
}
?>
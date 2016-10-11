<?php
namespace Questionnaire\Commands;
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
		$questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$QuestionnaireExtension->addJS();
		$QuestionnaireExtension->addCSS();

		// check if current user is admin
		$staff = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
		$admin = 0;
		$allowed = false;
		if ($questionnaire->get_creator()->get_id() == $user->get_id() || \lms_steam::is_steam_admin($user)) {
			$admin = 1;
			$allowed = true;
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

		// chronic
		\ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentObject($questionnaire);

		// redirect if there is no survey
		$surveyCount = 1;

		if (!($questionnaire->check_access_read())) {
				$errorHtml = new \Widgets\RawHtml();
				$errorHtml->setHtml("Der Fragebogen kann nicht angezeigt werden, da Sie nicht über die erforderlichen Leserechte verfügen.");
				$frameResponseObject->addWidget($errorHtml);
				return $frameResponseObject;
		}

		// display actionbar for admin
		if ($admin == 1) {
			$actionbar = new \Widgets\Actionbar();
			$actions = array(
				//array("name" => "Neuen Fragebogen erstellen" , "link" => $QuestionnaireExtension->getExtensionUrl() . "edit/" . $this->id . "/"),
				//array("name" => "Import" , "link" => $QuestionnaireExtension->getExtensionUrl() . "import/" . $this->id . "/"),
				//array("name" => "Konfiguration" , "link" => $QuestionnaireExtension->getExtensionUrl() . "configuration/" . $this->id . "/"),
				//array("name" => "Übersicht", "link" => $QuestionnaireExtension->getExtensionUrl() . "Index/" . $this->id . "/")
			);
			$actionbar->setActions($actions);
			$frameResponseObject->addWidget($actionbar);
		}

		// get the surveys that are to be shown and sort them
		$surveys = $questionnaire->get_inventory();
		$survey = $surveys[0];

		/*
		$surveys_inactive = array();
		$surveys_running = array();
		$surveys_ended = array();
		if ($survey instanceof \steam_container && !($survey instanceof \steam_user)) {
			$starttype = $survey->get_attribute("QUESTIONNAIRE_STARTTYPE");
			$state = $survey->get_attribute("QUESTIONNAIRE_STATE");
			// if survey is started/ended automatically check the times
			if (is_array($starttype)) {
				if (time() > $starttype[1] && $state == 0) {
					$survey->set_attribute("QUESTIONNAIRE_STATE", 1);
				}
				if (time() > $starttype[0] && $state == 1) {
					$survey->set_attribute("QUESTIONNAIRE_STATE", 2);
				}
			}
			$state = $survey->get_attribute("QUESTIONNAIRE_STATE");
			if ($state == 0) {
				array_push($surveys_inactive, $survey);
			} else if ($state == 1) {
				array_push($surveys_running, $survey);
			} else {
				array_push($surveys_ended, $survey);
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
		*/

		// display surveys
		$content = $QuestionnaireExtension->loadTemplate("questionnaire_index.template.html");
		$content->setCurrentBlock("BLOCK_SURVEY_TABLE");
		$content->setVariable("QUESTIONNAIRE_NAME", '<svg style="width:16px; height:16px; float:left; color:#3a6e9f; right:5px; position:relative;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' . PATH_URL . 'explorer/asset/icons/mimetype/svg/questionnaire.svg#questionnaire"></use></svg><h1>' . $questionnaire->get_name() . '</h1>');
		$content->setVariable("QUESTIONNAIRE_DESC", '<p style="float:left; color:#AAAAAA; clear:both; margin-top:0px">' . $questionnaire->get_attribute("OBJ_DESC") . '</p>');
		$content->setVariable("NAME_LABEL", "Name des Fragebogens");
		$content->setVariable("STATUS_LABEL", "Status");
		$content->setVariable("QUESTIONS_LABEL", "Anzahl der Fragen");
		$content->setVariable("RESULTS_LABEL", "Anzahl der Abgaben");
		$content->setVariable("ACTIONS_LABEL", "Aktionen");
		$content->setCurrentBlock("BLOCK_SURVEY_ELEMENT");
		$content->setVariable("NAME_VALUE", $survey->get_attribute("OBJ_DESC"));
		$resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
		$participants = $resultContainer->get_attribute("QUESTIONNAIRE_PARTICIPANTS");
		$active = \Questionnaire::getInstance()->isActive($this->id);
		$groups = $questionnaire->get_attribute("QUESTIONNAIRE_GROUP");
		if(in_array($user, $groups)){
			$allowed = true;
		}
		else{
			foreach ($groups as $group) {
				if ($group instanceof steam_group && $group->is_member($user)) {
					$allowed = true;
					break;
				}
			}
		}
		$times = $questionnaire->get_attribute("QUESTIONNAIRE_PARTICIPATION_TIMES");
		if ((isset($participants[$user->get_id()]) && $times == 1) || !$active) {
			$allowed  = false;
		}
		if ($allowed) {
			$content->setVariable("VIEW_URL", $QuestionnaireExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/1" . "/");
			$content->setVariable("VIEW_TITLE", "Fragebogen ausfüllen");
		} else {
			//$content->setVariable("DISPLAY_VIEW", "none");
			$content->setVariable("NAME_VALUE_NOLINK", $survey->get_attribute("OBJ_DESC"));
		}
		if (!$active){
			$content->setVariable("STATE_VALUE", "Inaktiv");
			$content->setVariable("DISPLAY_RESULTS", "none");
			$content->setVariable("DISPLAY_STOP", "none");
			$content->setVariable("DISPLAY_REPEAT", "none");
		} else if ($active) {
			$content->setVariable("STATE_VALUE", "Aktiv");
			//$content->setVariable("DISPLAY_START", "none");
			$content->setVariable("DISPLAY_REPEAT", "none");
			if ($resultContainer->get_attribute("QUESTIONNAIRE_RESULTS") == 0) {
				$content->setVariable("DISPLAY_RESULTS", "none");
			}
		} else {
			$content->setVariable("STATE_VALUE", "Beendet");
			//$content->setVariable("DISPLAY_EDIT", "none");
			//$content->setVariable("DISPLAY_START", "none");
			$content->setVariable("DISPLAY_STOP", "none");
			if ($resultContainer->get_attribute("QUESTIONNAIRE_RESULTS") == 0) {
				$content->setVariable("DISPLAY_RESULTS", "none");
			}
		}
		$content->setVariable("QUESTIONS_VALUE", $survey->get_attribute("QUESTIONNAIRE_QUESTIONS"));
		$content->setVariable("RESULTS_VALUE", $resultContainer->get_attribute("QUESTIONNAIRE_RESULTS"));
		$content->setVariable("ASSET_URL", $QuestionnaireExtension->getAssetUrl() . "icons");
		$content->setVariable("PREVIEW_TITLE", "Vorschau");
		$content->setVariable("PREVIEW_URL", $QuestionnaireExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/1/preview" . "/");
		$content->setVariable("EDIT_TITLE", "Fragebogen bearbeiten");
		$content->setVariable("EDIT_URL", $QuestionnaireExtension->getExtensionUrl() . "edit/" . $this->id . "/" . $survey->get_id() . "/");
		$content->setVariable("RESULTS_TITLE", "Auswertung");
		$content->setVariable("RESULTS_URL", $QuestionnaireExtension->getExtensionUrl() . "individualResults/" . $survey->get_id() . "/");
		$content->setVariable("DELETE_TITLE", "Fragebogen löschen");
		$content->setVariable("START_TITLE", "Fragebogen starten");
		$content->setVariable("STOP_TITLE", "Fragebogen beenden");
		$content->setVariable("REPEAT_TITLE", "Fragebogen kopieren");
		$content->setVariable("RF_VALUE", $questionnaire->get_id());
		$content->setVariable("ELEMENT_ID", $survey->get_id());
		$content->setVariable("DISPLAY_REPEAT", "none");
		$content->setVariable("DISPLAY_RESULTS", "none");
		$content->setVariable("DISPLAY_DELETE", "none");
		if ($admin == 0) {
			$content->setVariable("DISPLAY_ADMIN_ELEMENT", "none");
			//$content->setVariable("DISPLAY_EDIT", "none");
			//$content->setVariable("DISPLAY_START", "none");
			$content->setVariable("DISPLAY_STOP", "none");

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
				if ($resultObject->get_attribute("QUESTIONNAIRE_RELEASED") != 0) {
					$content->setVariable("SUBMITDATE", date("d.m.Y H:i:s", $resultObject->get_attribute("QUESTIONNAIRE_RELEASED")));
				} else {
					$questionCount = $survey->get_attribute("QUESTIONNAIRE_QUESTIONS");
					$questionsAnswered = 0;
					$attributeNames = $resultObject->get_attribute_names();
					for ($count2 = 0; $count2 < $questionCount; $count2++) {
						if (in_array("QUESTIONNAIRE_ANSWER_" . $count2, $attributeNames)) {
							$questionsAnswered++;
						}
					}
					$content->setVariable("SUBMITDATE", "noch nicht abgegeben (" . $questionsAnswered . " von " . $questionCount . " Fragen beantwortet)");
				}
				$content->setVariable("P_ASSET_URL", $QuestionnaireExtension->getAssetUrl() . "icons");
				$content->setVariable("P_VIEW_TITLE", "Details");
				$content->setVariable("P_VIEW_URL", $QuestionnaireExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/1/" . $result . "/1" . "/");
				if ($questionnaire->get_attribute("QUESTIONNAIRE_OWN_EDIT") == 1 || $resultObject->get_attribute("QUESTIONNAIRE_RELEASED") == 0) {
					$content->setVariable("P_EDIT_TITLE", "Bearbeiten");
					$content->setVariable("P_EDIT_URL", $QuestionnaireExtension->getExtensionUrl() . "view/" . $survey->get_id() . "/1/" . $result . "/");
				} else {
					$content->setVariable("P_DISPLAY_EDIT", "none");
				}
				$content->setVariable("P_DELETE_TITLE", "Löschen");
				$content->setVariable("RESULT_ID", $result);
				$content->setVariable("RESULT_RF", $questionnaire->get_id());
				$content->setVariable("RESULT_SURVEY", $survey->get_id());
				$content->parse("BLOCK_SURVEY_ONE_PARTICIPATION");
				$count++;
			}
			$content->parse("BLOCK_SURVEY_PARTICIPATION");
		}
		$content->parse("BLOCK_SURVEY_ELEMENT");
		if ($admin == 0) {
			$content->setVariable("DISPLAY_ADMIN", "none");
		}
		$content->parse("BLOCK_SURVEY_TABLE");

		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
}
?>

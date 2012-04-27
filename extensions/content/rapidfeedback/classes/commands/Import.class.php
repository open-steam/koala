<?php
/**
 * 
 * Import function to import bidowl questionnaires
 * atm working: importing of questions
 * work on this class stopped
 *
 */
namespace Rapidfeedback\Commands;
class Import extends \AbstractCommand implements \IFrameCommand {

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
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$RapidfeedbackExtension->addCSS();
		
		// access not allowed for non-admins
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$staff = $rapidfeedback->get_attribute("RAPIDFEEDBACK_STAFF");
		if (($staff instanceof \steam_group && !($staff->is_member($user))) || $staff instanceof \steam_user && !($staff->get_id() == $user->get_id())) {
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("<center>Zugang verwehrt. Sie sind kein Administrator in dieser Rapid Feedback Instanz</center>");
			$frameResponseObject->addWidget($rawWidget);
			$frameResponseObject->setHeadline(array(
				array("name" => "Rapid Feedback", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Import")
			));
			return $frameResponseObject;
		}
		
		// TODO: import function
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["import_survey"])) {
			echo($_POST["id"]);
			$bidowl_container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_POST["id"]);
			if ((!($bidowl_container instanceof \steam_container)) || ($bidowl_container->get_attribute("bid:doctype") != "questionary")) {
				// error
			} else {
				$name = $bidowl_container->get_name();
				/*
				$description = $bidowl_container->get_attribute("bid:questionary:description");
				$fillout = $bidowl_container->get_attribute("bid:questionary:fillout"); // wie oft kann ein benutzer fragebogen ausfüllen; 1 oder n
				$editanswer = $bidowl_container->get_attribute("bid:questionary:editanswer"); // können autoren antworten später bearbeiten
				$editownanswer = $bidowl_container->get_attribute("bid:questionary:editownanswer"); // kann der benutzer selbst seine antworten bearbeiten
				$numbering = $bidowl_container->get_attribute("bid:questionary:number"); // fragen des fragebogens nummerieren
				$resultcreationtime = $bidowl_container->get_attribute("bid:questionary:resultcreationtime"); // creationtime in auswertung anzeigen
				$resultcreator = $bidowl_container->get_attribute("bid:questionary:resultcreator"); // creator in auswertung anzeigen
				$enabled = $bidowl_container->get_attribute("bid:questionary:enabled");
				$layout = $bidowl_container->get_attribute("bid:questionary:layout");
				$analyst_rights = $bidowl_container->get_attribute("bid:questionary:analyst_rights");;
				$editor_rights = $bidowl_container->get_attribute("bid:questionary:editor_rights");;
				$author_rights = $bidowl_container->get_attribute("bid:questionary:author_rights");;
				*/
				
				// TODO: einstellungen am container setzen
				$survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
				$survey_object->setName($name);
				$survey_object->setBeginText("");
				$survey_object->setEndText("");
				
				// TODO: fragen hinzufügen
				$question_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $bidowl_container->get_path() . "/questions");
				$questions = $question_container->get_inventory();
				// TODO: evtl. erst noch sortieren
				foreach ($questions as $question) {
					$isQuestion = false;
					$question_geometry = $question->get_attribute("bid:question:geometry");
					switch ($question_geometry["type"]) {
						case 0:
							// description
							break;
						case 1:
							// empty line
							break;
						case 2:
							// checkbox
							$newquestion = new \Rapidfeedback\Model\MultipleChoiceQuestion();
							foreach ($question_geometry["options"] as $option) {
								$newquestion->addOption($option);
							}
							$newquestion->setArrangement($question_geometry["columns"]);
							$isQuestion = true;
							break;
						case 3:
							// input file
							break;	
						case 4:
							// radio
							$newquestion = new \Rapidfeedback\Model\SingleChoiceQuestion();
							foreach ($question_geometry["options"] as $option) {
								$newquestion->addOption($option);
							}
							$newquestion->setArrangement($question_geometry["columns"]);
							$isQuestion = true;
							break;	
						case 5:
							// select
							$newquestion = new \Rapidfeedback\Model\SingleChoiceQuestion();
							foreach ($question_geometry["options"] as $option) {
								$newquestion->addOption($option);
							}
							$isQuestion = true;
							break;								
						case 6:
							// caption
							break;								
						case 7:		
							// text	
							$newquestion = new \Rapidfeedback\Model\TextQuestion();
							$isQuestion = true;
							break;								
						case 8:
							// textarea
							$newquestion = new \Rapidfeedback\Model\TextareaQuestion();
							$isQuestion = true;
							break;	
						case 9:
							// new page
							break;
						case 10:
							// full line
							break;
						case 11:
							// grading
							$newquestion = new \Rapidfeedback\Model\GradingQuestion();
							foreach ($question_geometry["grading_options"] as $option) {
								$newquestion->addRow($option);
							}
							$question_geometry["question"] = $question_geometry["description"];
							$isQuestion = true;
							break;
						case 12;
							// tendency
							$newquestion = new \Rapidfeedback\Model\TendencyQuestion();
							foreach ($question_geometry["tendency_elements"] as $option) {
								$newquestion->addOption($option);
							}
							$question_geometry["question"] = $question_geometry["description"];
							$newquestion->setSteps($question_geometry["tendency_steps"]);
							$isQuestion = true;
							break;
					}
					// TODO: evtl weitere attribute
					if ($isQuestion) {
						$newquestion->setQuestionText($question_geometry["question"]);
						$newquestion->setHelpText("");
						$newquestion->setRequired($question_geometry["must"]);
						$survey_object->addQuestion($newquestion);
					}
				}
				
				// TODO: einstellungen am container setzen
				$survey_container = $survey_object->createSurvey();
				
				$edittime = $bidowl_container->get_attribute("bid:questionary:edittime");
				if ($edittime[0]) {
					$times = array();
					array_push($times, $edittime[2]);
					array_push($times, $edittime[1]);
					$survey_container->set_attribute("RAPIDFEEDBACK_STARTTYPE", $times);
				}
			}
		}
		
		$content = $RapidfeedbackExtension->loadTemplate("rapidfeedback_import.template.html");
		$content->setCurrentBlock("BLOCK_IMPORT_DIALOG");
		$content->setVariable("RAPIDFEEDBACK_IMPORT", "Fragebogen importieren");
		$content->setVariable("ID_LABEL", "Objekt ID:*");
		$content->setVariable("IMPORT_SURVEY", "Fragebogen importieren");
		$content->setVariable("BACK_URL", $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id());
		$content->setVariable("BACK_LABEL", "Zurück");
		$content->parse("BLOCK_IMPORT_DIALOG");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => "Rapid Feedback", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id()),
			array("name" => "Import")
		));
		return $frameResponseObject;
	}
}
?>
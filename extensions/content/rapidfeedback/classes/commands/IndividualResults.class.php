<?php
namespace Rapidfeedback\Commands;
class IndividualResults extends \AbstractCommand implements \IFrameCommand {

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
		$survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[0]);
                if (!($survey instanceof \steam_object)) {
                    \ExtensionMaster::getInstance()->send404Error();
                }
		$rapidfeedback = $survey->get_environment();
		$result_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
		$survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
		$user = \lms_steam::get_current_user();
		$RapidfeedbackExtension->addCSS();
		$RapidfeedbackExtension->addJS();
		$RapidfeedbackExtension->addJS("jquery.tablesorter.js");
		$RapidfeedbackExtension->addCSS("jquery.tablesorter.css");

		// access not allowed for non-admins
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
		if ($admin == 0) {
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("<center>Die Bearbeitung dieses Fragebogens ist den Administratoren vorbehalten.</center>");
			$frameResponseObject->addWidget($rawWidget);
			return $frameResponseObject;
		}

		// display actionbar
		$actionBar = new \Widgets\ActionBar();
		$actions = array(
			array("name" => "Export als Excel-Datei" , "link" => $RapidfeedbackExtension->getExtensionUrl() . "export/" . $this->id . "/"),
			array("name" => "Übersicht" , "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id() . "/")
			);
		$actionBar->setActions($actions);
		$frameResponseObject->addWidget($actionBar);

		// display tabbar
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(
			array("name"=>"Individuelle Auswertung", "link"=>$this->getExtension()->getExtensionUrl() . "individualResults/" . $this->id . "/"),
			array("name"=>"Gesamtauswertung", "link"=>$this->getExtension()->getExtensionUrl() . "overallResults/" . $this->id . "/")
		));
		$tabBar->setActiveTab(0);
		$frameResponseObject->addWidget($tabBar);

		$xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
		$survey_object->parseXML($xml);

		$content = $RapidfeedbackExtension->loadTemplate("rapidfeedback_individualresults.template.html");
		$content->setCurrentBlock("BLOCK_RESULTS");
		$content->setVariable("RESULTS_LABEL", "Individuelle Auswertung");
		if ($result_container->get_attribute("RAPIDFEEDBACK_RESULTS") != 1) {
			$content->setVariable("RESULTS_AMOUNT", $result_container->get_attribute("RAPIDFEEDBACK_RESULTS") . " Abgaben");
		} else {
			$content->setVariable("RESULTS_AMOUNT", $result_container->get_attribute("RAPIDFEEDBACK_RESULTS") . " Abgabe");
		}

		// display questions in the first line
		$questionCount = 1;
		foreach ($survey_object->getQuestions() as $question) {
			if ($question instanceof \Rapidfeedback\Model\AbstractQuestion) {
				$content->setCurrentBlock("BLOCK_QUESTION");
				$text = "";
				$text_long = $questionCount . ". " . $question->getQuestionText();
				if ($question instanceof \Rapidfeedback\Model\MatrixQuestion) {
					foreach ($question->getRows() as $row) {
						$text = $text . " " . $row;
						$text_long = $text_long . "<br>" . $row;
					}
				} else if ($question instanceof \Rapidfeedback\Model\TendencyQuestion) {
					foreach ($question->getOptions() as $option) {
						$text = $text . " " . $option[0] . " - " . $option[1];
						$text_long = $text_long . "<br>" . $option[0] . " - " . $option[1];
					}
				} else {
					$text = " " . $question->getQuestionText();
					$text_long = $text;
				}
				if (strlen($text) > 25) {
					$text = substr($text, 0, 25) . "...";
				}
				$text = $text . "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
				$content->setVariable("QUESTION_TEXT", $questionCount . "." . $text);
				$tipsy = new \Widgets\Tipsy();
				$tipsy->setElementId("tipsy" . $questionCount);
				$tipsy->setHtml($text_long);
				$content->setVariable("TIPSY_ID", "tipsy" . $questionCount);
				$content->setVariable("TIPSY_HTML", "<script>" . $tipsy->getHtml() . "</script>");
				$content->parse("BLOCK_QUESTION");
				$questionCount++;
			}
		}

		if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_SHOW_PARTICIPANTS") == 0) {
			$content->setVariable("DISPLAY_PARTICIPANTS", "none");
		} else {
			$content->setVariable("PARTICIPANT_LABEL", "Teilnehmer&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
		}
		if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_SHOW_CREATIONTIME") == 0) {
			$content->setVariable("DISPLAY_TIME", "none");
		} else {
			$content->setVariable("TIME_LABEL", "Erstellungszeit");
		}

		// initialize table sorting
		$initJS = '$(document).ready(function() {
					        $("#resulttable").tablesorter({
					        	headers : {' .
					        	 	($questionCount+1) . ': { sorter : false }
								}, sortList: [[' . $questionCount . ',1]]
							});
				   });';
		$content->setVariable("INIT_JS_SORT", "<script>" . $initJS . "</script>");

		// display results
		$results = $result_container->get_inventory();
		$resultCount = 0;
		foreach ($results as $result) {
			if ($result instanceof \steam_object && $result->get_attribute("RAPIDFEEDBACK_RELEASED") != 0) {
				$content->setCurrentBlock("BLOCK_RESULT");
				$resultArray = $survey_object->getIndividualResult($result);
				foreach ($resultArray as $questionResult) {
					$resultHTML = "";
					for ($count = 0; $count < count($questionResult); $count++) {
						$resultHTML = $resultHTML . $questionResult[$count] . "<br>";
					}
					$resultHTML = substr($resultHTML, 0, strlen($resultHTML)-4);

					$content->setCurrentBlock("BLOCK_RESULT_COL");
					if ($resultCount % 2 == 0) {
						$content->setVariable("BG_COLOR_COL", "#FFFFFF");
					} else {
						$content->setVariable("BG_COLOR_COL", "#FFFCCC");
					}
					$content->setVariable("RESULT_HTML", $resultHTML);
					$content->parse("BLOCK_RESULT_COL");
				}
				if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_SHOW_PARTICIPANTS") == 0) {
					$content->setVariable("DISPLAY_PARTICIPANTS_RESULT", "none");
				} else {
					$content->setVariable("USER_NAME", $result->get_creator()->get_full_name());
					$content->setVariable("USER_URL", PATH_URL . "user/index/" . $result->get_creator()->get_name());
				}
				if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_SHOW_CREATIONTIME") == 0) {
					$content->setVariable("DISPLAY_TIME_RESULT", "none");
				} else {
					$content->setVariable("TIMESTAMP", date("d.m.Y H:i:s", $result->get_attribute("OBJ_CREATION_TIME")));
				}
				if ($resultCount % 2 == 0) {
					$content->setVariable("BG_COLOR", "#FFFFFF");
				} else {
					$content->setVariable("BG_COLOR", "#FFFCCC");
				}
				$content->setVariable("ASSET_URL", $RapidfeedbackExtension->getAssetUrl() . "icons");
				$content->setVariable("VIEW_TITLE", "Details");
				$content->setVariable("VIEW_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $this->id . "/1/" . $result->get_id() . "/1" . "/");
				if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_ADMIN_EDIT") == 1) {
					$content->setVariable("EDIT_TITLE", "Bearbeiten");
					$content->setVariable("EDIT_URL", $RapidfeedbackExtension->getExtensionUrl() . "view/" . $this->id . "/1/" . $result->get_id() . "/");
				} else {
					$content->setVariable("DISPLAY_EDIT", "none");
				}
				$content->setVariable("DELETE_TITLE", "Löschen");
				$content->setVariable("RESULT_ID", $result->get_id());
				$content->setVariable("RESULT_SURVEY", $survey->get_id());
				$content->setVariable("RESULT_RF", $rapidfeedback->get_id());
				$content->parse("BLOCK_RESULT");
				$resultCount++;
			}
		}
		$content->parse("BLOCK_RESULTS");

		$tipsy = new \Widgets\Tipsy();
		$frameResponseObject->addWIdget($tipsy);

		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
}
?>

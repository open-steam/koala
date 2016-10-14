<?php
namespace Questionnaire\Commands;
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
		$questionnaire = $survey->get_environment();
		$result_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
		$resultNumber = $result_container->get_attribute("QUESTIONNAIRE_RESULTS");
		$survey_object = new \Questionnaire\Model\Survey($questionnaire);
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$QuestionnaireExtension->addCSS();
		$QuestionnaireExtension->addJS();
		$QuestionnaireExtension->addJS("jquery.tablesorter.js");
		$QuestionnaireExtension->addCSS("jquery.tablesorter.css");
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
					if ($object instanceof steam_group && $object->is_member($user)) {
						$admin = 1;
						break;
					}
				}
			}
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
			//array("name" => "Export als Excel-Datei" , "link" => $QuestionnaireExtension->getExtensionUrl() . "export/" . $this->id . "/"),
			//array("name" => "Übersicht" , "link" => $QuestionnaireExtension->getExtensionUrl() . "Index/" . $questionnaire->get_id() . "/")
			);
		$actionBar->setActions($actions);
		$frameResponseObject->addWidget($actionBar);

		// display tabbar
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(
			array("name"=>"<svg style='height:16px; width:16px; position:relative; top:3px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/user.svg#user'></use></svg> Individuelle Auswertung", "link"=>$this->getExtension()->getExtensionUrl() . "individualResults/" . $this->id . "/"),
			array("name"=>"<svg style='height:16px; width:16px; position:relative; top:3px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/group.svg#group'></use></svg> Gesamtauswertung", "link"=>$this->getExtension()->getExtensionUrl() . "overallResults/" . $this->id . "/")
		));
		$tabBar->setActiveTab(0);
		$frameResponseObject->addWidget($tabBar);

		$xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
		$survey_object->parseXML($xml);
		$questions = $survey_object->getQuestions();

		$content = $QuestionnaireExtension->loadTemplate("questionnaire_individualresults.template.html");
		$content->setCurrentBlock("BLOCK_RESULTS");
		//$content->setVariable("RESULTS_LABEL", "Individuelle Auswertung");
		$content->setVariable("RESULTS_AMOUNT", "Anzahl Abgaben: " . $resultNumber);

		// display questions in the first line
		$questionCount = 1;
		foreach ($questions as $question) {
			if ($question instanceof \Questionnaire\Model\AbstractQuestion) {
				$content->setCurrentBlock("BLOCK_QUESTION");
				$text = "";
				$text_long = $questionCount . ". " . $question->getQuestionText();
				if ($question instanceof \Questionnaire\Model\MatrixQuestion) {
					foreach ($question->getRows() as $row) {
						$text = $text . " " . $row;
						$text_long = $text_long . "<br>" . $row;
					}
				} else if ($question instanceof \Questionnaire\Model\TendencyQuestion) {
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
				//$text = $text . "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
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

		if ($questionnaire->get_attribute("QUESTIONNAIRE_SHOW_PARTICIPANTS") == 0) {
			$content->setVariable("DISPLAY_PARTICIPANTS", "none");
		} else {
			$content->setVariable("PARTICIPANT_LABEL", "Teilnehmer&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
		}
		if ($questionnaire->get_attribute("QUESTIONNAIRE_SHOW_CREATIONTIME") == 0) {
			$content->setVariable("DISPLAY_TIME", "none");
		} else {
			$content->setVariable("TIME_LABEL", "Erstellungszeit");
		}

		$popupMenuHeadline = new \Widgets\PopupMenu();
		$popupMenuHeadline->setCommand("GetPopupMenuHeadline");
		$popupMenuHeadline->setNamespace("Questionnaire");
		$popupMenuHeadline->setData($questionnaire);
		$popupMenuHeadline->setElementId("result-overlay");
		$popupMenuHeadline->setParams(array(array("key" => "id", "value" => $this->id)));
		$content->setVariable("POPUPMENUANKER_HEADLINE", $popupMenuHeadline->getHtml());

		if($resultNumber != 0){
			// initialize table sorting
			$initJS = '$(document).ready(function() {
						        $("#resulttable").tablesorter({
						        	headers : {' .
						        	 	($questionCount+1) . ': { sorter : false }
									}, sortList: [[' . $questionCount . ',1]]
								});
					   });';
			$content->setVariable("INIT_JS_SORT", "<script>" . $initJS . "</script>");
		}

		// display results
		$results = $result_container->get_inventory();
		$resultCount = 0;
		foreach ($results as $result) {
			if ($result instanceof \steam_object && $result->get_attribute("QUESTIONNAIRE_RELEASED") != 0) {
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
						$content->setVariable("BG_COLOR_COL", "#EEE");
					}
					$content->setVariable("RESULT_HTML", $resultHTML);
					$content->parse("BLOCK_RESULT_COL");
				}
				if ($questionnaire->get_attribute("QUESTIONNAIRE_SHOW_PARTICIPANTS") == 0) {
					$content->setVariable("DISPLAY_PARTICIPANTS_RESULT", "none");
				} else {
					$content->setVariable("USER_NAME", $result->get_creator()->get_full_name());
					$content->setVariable("USER_URL", PATH_URL . "user/index/" . $result->get_creator()->get_name());
				}
				if ($questionnaire->get_attribute("QUESTIONNAIRE_SHOW_CREATIONTIME") == 0) {
					$content->setVariable("DISPLAY_TIME_RESULT", "none");
				} else {
					$content->setVariable("TIMESTAMP", date("d.m.Y H:i:s", $result->get_attribute("OBJ_CREATION_TIME")));
				}
				if ($resultCount % 2 == 0) {
					$content->setVariable("BG_COLOR", "#FFFFFF");
				} else {
					$content->setVariable("BG_COLOR", "#EEE");
				}

				$popupMenu = new \Widgets\PopupMenu();
				$popupMenu->setCommand("GetPopupMenu");
				$popupMenu->setNamespace("Questionnaire");
				$popupMenu->setData($questionnaire);
				$popupMenu->setElementId("result-overlay");
				$popupMenu->setParams(array(array("key" => "resultId", "value" => $result->get_id())));

				$content->setVariable("POPUPMENUANKER", $popupMenu->getHtml());
/*
				$content->setVariable("ASSET_URL", $QuestionnaireExtension->getAssetUrl() . "icons");
				$content->setVariable("VIEW_TITLE", "Details");
				$content->setVariable("VIEW_URL", $QuestionnaireExtension->getExtensionUrl() . "view/" . $this->id . "/1/" . $result->get_id() . "/1" . "/");
				if ($questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1) {
					$content->setVariable("EDIT_TITLE", "Bearbeiten");
					$content->setVariable("EDIT_URL", $QuestionnaireExtension->getExtensionUrl() . "view/" . $this->id . "/1/" . $result->get_id() . "/");
				} else {
					$content->setVariable("DISPLAY_EDIT", "none");
				}
				$content->setVariable("DELETE_TITLE", "Löschen");
*/

				//bearbeiten und löschen braucht admin edit == 1

				//<a href="{VIEW_URL}"><img style="cursor: hand;" src="{ASSET_URL}/preview.png" title="{VIEW_TITLE}" width="12px" height="12px"></a>
				//<a href="{EDIT_URL}" style="display:{DISPLAY_EDIT}"><img style="cursor: hand;" src="{ASSET_URL}/edit.png" title="{EDIT_TITLE}" width="12px" height="12px"></a>
				//<a href="#" onclick="deleteResult({RESULT_ID}, {RESULT_SURVEY}, {RESULT_RF})"><img style="cursor: hand; display:{DISPLAY_EDIT};" src="{ASSET_URL}/delete.png" title="{DELETE_TITLE}" width="12px" height="12px"></a>

				$content->setVariable("RESULT_ID", $result->get_id());
				$content->setVariable("RESULT_SURVEY", $survey->get_id());
				$content->setVariable("RESULT_RF", $questionnaire->get_id());
				$content->parse("BLOCK_RESULT");
				$resultCount++;
			}
		}
		$content->parse("BLOCK_RESULTS");

		$tipsy = new \Widgets\Tipsy();
		$frameResponseObject->addWIdget($tipsy);
		$PopupMenuStyle = \Widgets::getInstance()->readCSS("PopupMenu.css");
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get() . "<style>" . $PopupMenuStyle . "</style>");
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
}
?>
<?php
namespace Rapidfeedback\Commands;
class OverallResults extends \AbstractCommand implements \IFrameCommand {

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
		$rapidfeedback = $survey->get_environment();
		$result_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
		$survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
		$user = \lms_steam::get_current_user();
		$RapidfeedbackExtension->addCSS();
		$RapidfeedbackExtension->addJS();

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
		$actionbar = new \Widgets\Actionbar();
		$actions = array(
			array("name" => "Übersicht", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id() . "/")
		);
		$actionbar->setActions($actions);
		$frameResponseObject->addWidget($actionbar);

		// display tabbar
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(
			array("name"=>"Individuelle Auswertung", "link"=>$this->getExtension()->getExtensionUrl() . "individualResults/" . $this->id . "/"),
			array("name"=>"Gesamtauswertung", "link"=>$this->getExtension()->getExtensionUrl() . "overallResults/" . $this->id . "/")
		));
		$tabBar->setActiveTab(1);
		$frameResponseObject->addWidget($tabBar);

		// display results
		$content = $RapidfeedbackExtension->loadTemplate("rapidfeedback_overallresults.template.html");
		$content->setCurrentBlock("BLOCK_RESULTS");
		$content->setVariable("RESULTS_LABEL", "Gesamtauswertung");
		if ($survey->get_attribute("RAPIDFEEDBACK_RESULTS") != 1) {
			$content->setVariable("RESULTS_AMOUNT", $result_container->get_attribute("RAPIDFEEDBACK_RESULTS") . " Abgaben");
		} else {
			$content->setVariable("RESULTS_AMOUNT", $result_container->get_attribute("RAPIDFEEDBACK_RESULTS") . " Abgabe");
		}
		$content->setVariable("RESULTS_LEGEND", "Legende: n = Anzahl, mw = Mittelwert, md = Median, s = Standardabweichung");
		$survey_object->parseXML($xml);
		$survey_object->generateResults($survey);
		$questions = $survey_object->getQuestions();
		$question_html = '
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    	<script type="text/javascript">
      		google.load("visualization", "1", {packages:["corechart"]});
		</script>';
		$questionCount = 0;
		$allCount = 0;
		foreach ($questions as $question) {
			if ($question instanceof \Rapidfeedback\Model\AbstractQuestion) {
				$question_html = $question_html . $questions[$allCount]->getResultHTML($questionCount+1);
				$questionCount++;
			}
			$allCount++;
		}
		$content->setVariable("QUESTIONS_HTML", $question_html);
		$content->parse("BLOCK_RESULTS");

		$tipsy = new \Widgets\Tipsy();
		$frameResponseObject->addWidget($tipsy);

		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
}
?>

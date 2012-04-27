<?php
namespace Rapidfeedback\Commands;
class Results extends \AbstractCommand implements \IFrameCommand {

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
		$survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[1]);
		$survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
		$xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$RapidfeedbackExtension->addCSS();
		$RapidfeedbackExtension->addJS();
		
		// access not allowed for non-admins
		$staff = $rapidfeedback->get_attribute("RAPIDFEEDBACK_STAFF");
		if (($staff instanceof \steam_group && !($staff->is_member($user))) || $staff instanceof \steam_user && !($staff->get_id() == $user->get_id())) {
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("<center>Zugang verwehrt. Sie sind kein Administrator in dieser Rapid Feedback Instanz</center>");
			$frameResponseObject->addWidget($rawWidget);
			$frameResponseObject->setHeadline(array(
				array("name" => "Rapid Feedback", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Auswertung")
			));
			return $frameResponseObject;
		}
		
		// display results
		$content = $RapidfeedbackExtension->loadTemplate("rapidfeedback_results.template.html");
		$content->setCurrentBlock("BLOCK_RESULTS");
		$content->setVariable("RESULTS_LABEL", "Auswertung");
		if ($survey->get_attribute("RAPIDFEEDBACK_RESULTS") != 1) {
			$content->setVariable("RESULTS_AMOUNT", $survey->get_attribute("RAPIDFEEDBACK_RESULTS") . " Abgaben");
		} else {
			$content->setVariable("RESULTS_AMOUNT", $survey->get_attribute("RAPIDFEEDBACK_RESULTS") . " Abgabe");
		}
		$content->setVariable("RESULTS_LEGEND", "Legende: n = Anzahl, mw = Mittelwert, md = Median, s = Standardabweichung");
		$survey_object->parseXML($xml);
		$survey_object->generateResults($survey);
		$questions = $survey_object->getQuestions();
		$question_html = '
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    	<script type="text/javascript">
      		google.load("visualization", "1", {packages:["corechart"]});
		</script>
		<script type="text/javascript" src="' . $RapidfeedbackExtension->getAssetUrl() . 'wz_tooltip.js"></script>';
		for ($count = 0; $count < count($questions); $count++) {
			$question_html = $question_html . $questions[$count]->getResultHTML($count+1);
		}
		$content->setVariable("QUESTIONS_HTML", $question_html);
		$content->setVariable("BACK_LABEL", "Zurück");
		$content->setVariable("BACK_URL", $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $this->id);
		$content->parse("BLOCK_RESULTS");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => "Rapid Feedback", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $rapidfeedback->get_id()),
			array("name" => "Auswertung")
		));
		return $frameResponseObject;
	}
}
?>
<?php
namespace Questionnaire\Commands;
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
		$questionnaire = $survey->get_environment();
		$result_container = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
		$survey_object = new \Questionnaire\Model\Survey($questionnaire);
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$xml = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/survey.xml");
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$QuestionnaireExtension->addCSS();
		$QuestionnaireExtension->addJS();
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
		$actionbar = new \Widgets\Actionbar();
		$actions = array(
			//array("name" => "Übersicht", "link" => $QuestionnaireExtension->getExtensionUrl() . "Index/" . $questionnaire->get_id() . "/")
		);
		$actionbar->setActions($actions);
		$frameResponseObject->addWidget($actionbar);

		// display tabbar
		$tabBar = new \Widgets\TabBar();
		$tabBar->setTabs(array(
			array("name"=>"<svg style='height:16px; width:16px; position:relative; top:3px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/user.svg#user'></use></svg> Individuelle Auswertung", "link"=>$this->getExtension()->getExtensionUrl() . "individualResults/" . $this->id . "/"),
			array("name"=>"<svg style='height:16px; width:16px; position:relative; top:3px;'><use xlink:href='" . PATH_URL . "explorer/asset/icons/group.svg#group'></use></svg> Gesamtauswertung", "link"=>$this->getExtension()->getExtensionUrl() . "overallResults/" . $this->id . "/")
		));
		$tabBar->setActiveTab(1);
		$frameResponseObject->addWidget($tabBar);

		// display results
		$content = $QuestionnaireExtension->loadTemplate("questionnaire_overallresults.template.html");
		$content->setCurrentBlock("BLOCK_RESULTS");
		//$content->setVariable("RESULTS_LABEL", "Gesamtauswertung");
		$content->setVariable("RESULTS_AMOUNT", "Anzahl Abgaben: " . $result_container->get_attribute("QUESTIONNAIRE_RESULTS"));
		//$content->setVariable("RESULTS_LEGEND", "Legende: n = Anzahl, mw = Mittelwert, md = Median, s = Standardabweichung");
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
			if ($question instanceof \Questionnaire\Model\AbstractQuestion) {
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

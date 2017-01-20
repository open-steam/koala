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
		$QuestionnaireExtension = \Questionnaire::getInstance();

		if(!($questionnaire instanceof \steam_room)){
			$errorHtml = new \Widgets\RawHtml();
			$errorHtml->setHtml("<center>Der angeforderte Fragebogen existiert nicht.</center>");
			$frameResponseObject->addWidget($errorHtml);
			return $frameResponseObject;
		}

		if (!($questionnaire->check_access_read())) {
				$errorHtml = new \Widgets\RawHtml();
				$errorHtml->setHtml("<center>Der Fragebogen kann nicht angezeigt werden, da Sie nicht über die erforderlichen Leserechte verfügen.</center>");
				$frameResponseObject->addWidget($errorHtml);
				return $frameResponseObject;
		}

		// chronic
		\ExtensionMaster::getInstance()->getExtensionById("Chronic")->setCurrentObject($questionnaire);

		$surveys = $questionnaire->get_inventory();
		$survey = $surveys[0];
		$surveyId = $survey->get_id();

		header('Location: ' . $QuestionnaireExtension->getExtensionUrl() . "view/" . $surveyId . "/1/");

		die;

	}
}
?>

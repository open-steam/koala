<?php
namespace Questionnaire\Commands;
class Create extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {

		if (isset($this->params["title"]) && $this->params["title"] != "") {
			// create data structure
			$currentRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			$questionnaire = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $this->params["title"], $currentRoom);
			$questionnaire->set_attribute("OBJ_TYPE", "QUESTIONNAIRE_CONTAINER");
			$questionnaire->set_attribute("QUESTIONNAIRE_GROUP", array());
			$questionnaire->set_attribute("QUESTIONNAIRE_STAFF", array());
			$questionnaire->set_attribute("QUESTIONNAIRE_PARTICIPATION_TIMES", 1);
			$questionnaire->set_attribute("QUESTIONNAIRE_SHOW_PARTICIPANTS", 1);
			$questionnaire->set_attribute("QUESTIONNAIRE_SHOW_CREATIONTIME", 1);
			$questionnaire->set_attribute("QUESTIONNAIRE_ADMIN_EDIT", 1);
			$questionnaire->set_attribute("QUESTIONNAIRE_OWN_EDIT", 0);

			$survey_object = new \Questionnaire\Model\Survey($questionnaire);
			$survey_object->setName($this->params["title"]);
			$survey_object->createSurvey();

		}

		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs('closeDialog(); location.reload();');
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}

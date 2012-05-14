<?php
namespace Rapidfeedback\Commands;
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
		
		// create data structure 
		$currentRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$rapidfeedback = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $this->params["title"], $currentRoom);
		$rapidfeedback->set_attribute("OBJ_TYPE", "RAPIDFEEDBACK_CONTAINER");
		$rapidfeedback->set_attribute("RAPIDFEEDBACK_GROUP", array());
		$rapidfeedback->set_attribute("RAPIDFEEDBACK_STAFF", array());
		$rapidfeedback->set_attribute("RAPIDFEEDBACK_PARTICIPATION_TIMES", 1);
		$rapidfeedback->set_attribute("RAPIDFEEDBACK_SHOW_PARTICIPANTS", 1);
		$rapidfeedback->set_attribute("RAPIDFEEDBACK_SHOW_CREATIONTIME", 1);
		$rapidfeedback->set_attribute("RAPIDFEEDBACK_ADMIN_EDIT", 0);
		$rapidfeedback->set_attribute("RAPIDFEEDBACK_OWN_EDIT", 1);
		
		// create one survey
		/*
		$survey_object = new \Rapidfeedback\Model\Survey($rapidfeedback);
		$survey_object->setName($this->params["title"]);
		$survey_object->setBeginText("");
		$survey_object->setEndText("");
		$survey_object->setStartType(0);
		$survey_object->createSurvey();*/
		
		
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		closeDialog();
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
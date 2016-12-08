<?php
namespace Questionnaire\Commands;
class DeleteResult extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $reload;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		isset($this->params["reload"]) ? $this->reload = $this->params["reload"]: "";
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$result = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["survey"]);
		$questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["rf"]);
		$user = \lms_steam::get_current_user();
		$released = $result->get_attribute("QUESTIONNAIRE_RELEASED");
		$creator = $questionnaire->get_creator()->get_id();
		$active = \Questionnaire::getInstance()->isActive($this->id);

		// check if user is admin
		$staff = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
		$admin = 0;
		$allowed = false;
		$root = 0;
		$isCreator = 0;
		if($creator == $user->get_id()){
			$isCreator = 1;
			$admin = 1;
		}
		else if(\lms_steam::is_steam_admin($user)){
			$root = 1;
			$admin = 1;
		}
		else if(in_array($user, $staff)){
				$admin = 1;
		}
		else{
			foreach ($staff as $object) {
				if ($object instanceof \steam_group && $object->is_member($user)) {
					$admin = 1;
					break;
				}
			}
		}

		// check if user is allowed to participate
		$possibleParticipants = $questionnaire->get_attribute("QUESTIONNAIRE_GROUP");
		if(in_array($user, $possibleParticipants)){
			$allowed = true;
		}
		else{
			foreach ($possibleParticipants as $object) {
				if ($object instanceof \steam_group && $object->is_member($user)) {
					$allowed = true;
					break;
				}
			}
		}

		if($result->get_creator()->get_id() == $user->get_id()){
			$ownResult = true;
		}
		else{
			$ownResult = false;
		}

		if(!$admin && !$allowed) die;

		$check = false;
		if($admin){
			if($ownResult){
				if($result->get_attribute("QUESTIONNAIRE_RELEASED") == 0 || $questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1 || $questionnaire->get_attribute("QUESTIONNAIRE_OWN_EDIT") == 1){
					$check = true;
				}
			}
			else{
				if($questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1){
					$check = true;
				}
			}
		} elseif($allowed){ //allowed to participate, but no admin
			if($ownResult && ($questionnaire->get_attribute("QUESTIONNAIRE_OWN_EDIT") == 1 || $result->get_attribute("QUESTIONNAIRE_RELEASED") == 0)){
				$check = true;
			}
		}

		if($root || $isCreator){
			$check = true;
		}

		// if user is allowed to delete result, delete it and update participation array
		if ($result instanceof \steam_object && $check) {
			$resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
			$participants = $resultContainer->get_attribute("QUESTIONNAIRE_PARTICIPANTS");
			$results = $participants[$result->get_creator()->get_id()];
			$newResults = array();
			foreach ($results as $oneResult) {
				if ($result->get_id() != $oneResult) {
					array_push($newResults, $oneResult);
				}
			}
			$count = $resultContainer->get_attribute("QUESTIONNAIRE_RESULTS");
			if ($result->get_attribute("QUESTIONNAIRE_RELEASED") != 0) $count--;

			if (count($newResults) == 0) {
				$participantsHelp = array();
				foreach ($participants as $participant => $resultArray) {
					if ($participant != $result->get_creator()->get_id()) {
						$participantsHelp[$participant] = $resultArray;
					}
				}
				$participants = $participantsHelp;
			} else {
				$participants[$result->get_creator()->get_id()] = $newResults;
			}

			$result->delete();

			$resultContainer->set_attribute("QUESTIONNAIRE_PARTICIPANTS", $participants);
			$resultContainer->set_attribute("QUESTIONNAIRE_RESULTS", $count);
		}

		$raw = new \Widgets\RawHtml();
		if($this->reload){
			$raw->setHtml('<script>location.reload()</script>');
		}
		else{
			$raw->setHtml("");
		}
		$ajaxResponseObject->addWidget($raw);

		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	}
}
?>

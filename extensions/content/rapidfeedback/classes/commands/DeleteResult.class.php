<?php
namespace Rapidfeedback\Commands;
class DeleteResult extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$result = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$survey = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["survey"]);
		$rapidfeedback = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["rf"]);
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$state = $rapidfeedback->get_attribute("RAPIDFEEDBACK_STATE");
		$released = $result->get_attribute("RAPIDFEEDBACK_RELEASED");
		$creator = $rapidfeedback->get_creator()->get_id();
		 
		// check if user is admin
		$staff = $rapidfeedback->get_attribute("RAPIDFEEDBACK_STAFF");
		$admin = 0;
		foreach ($staff as $group) {
			if ($group->is_member($user)) {
				$admin = 1;
				break;
			}
		}
		if ($creator  == $user->get_id()) {
			$admin = 1;
		}
		
		$allowed = true;
		// if current user is no admin, questionnaire is active, result is his unreleased result or editing is allowed
		if ($state == 1 && $admin == 0 && $result->get_creator()->get_id()  == $user->get_id()) {
			if ($released == 0 || $rapidfeedback->get_attribute("RAPIDFEEDBACK_OWN_EDIT") == 1) {
				$allowed = true;
			}
		}
		// if current user is admin, it is his own unreleased result or own editing is allowed or admin editing is allowed
		if ($admin == 1) {
			if ($state == 1 && $result->get_creator()->get_id()  == $user->get_id() && ($released == 0 || $rapidfeedback->get_attribute("RAPIDFEEDBACK_OWN_EDIT") == 1)) {
				$allowed = true;
			}
			if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_ADMIN_EDIT") == 1) {
				$allowed = true;
			}
		}
		
		// if user is allowed to delete result, delete it and update participation array
		if ($result instanceof \steam_object && $allowed) {
			$resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
			$participants = $resultContainer->get_attribute("RAPIDFEEDBACK_PARTICIPANTS");
			$results = $participants[$result->get_creator()->get_id()];
			$newResults = array();
			foreach ($results as $oneResult) {
				if ($result->get_id() != $oneResult) {
					array_push($newResults, $oneResult);
				}
			}
			$count = $resultContainer->get_attribute("RAPIDFEEDBACK_RESULTS");
			if ($result->get_attribute("RAPIDFEEDBACK_RELEASED") != 0) $count--;
			
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
			
			$resultContainer->set_attribute("RAPIDFEEDBACK_PARTICIPANTS", $participants);
			$resultContainer->set_attribute("RAPIDFEEDBACK_RESULTS", $count);
		}
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	}
}
?>
<?php
namespace Rapidfeedback\Commands;
class AdminAction extends \AbstractCommand implements \IAjaxCommand {

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
		// admin action (start, stop, copy, delete) got submitted
		$element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$rapidfeedback = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["rf"]);
		if ($element instanceof \steam_object) {
			switch ($this->params["action"]) {
				case 1:
					// start
					$element->set_attribute("RAPIDFEEDBACK_STATE", 1);
					break;
				case 2:
					// stop
					$element->set_attribute("RAPIDFEEDBACK_STATE", 2);
					break;
				case 3:
					// copy
					$copy = \steam_factory::create_copy($GLOBALS["STEAM"]->get_id(), $element);
					$copy->move($rapidfeedback);
					$copy->set_attribute("RAPIDFEEDBACK_PARTICIPANTS", array());
					$copy->set_attribute("RAPIDFEEDBACK_STATE", 0);
					$copy->set_attribute("RAPIDFEEDBACK_RESULTS", 0);
					$copy->set_attribute("RAPIDFEEDBACK_STARTTYPE", 0);
					$resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $copy->get_path() . "/results");
					$results = $resultContainer->get_inventory();
					foreach ($results as $result) {
						$result->delete();
					}
					break;
				case 4:
					// delete
					$element->delete();
					break;
			}
		}
		
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	}
}
?>
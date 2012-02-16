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
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		
		if (!(isset($this->params["group_course"]))) {
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
			$ajaxResponseObject->addWidget($rawWidget);
			return $ajaxResponseObject;
		}
		
		if ($this->params["group_course"] == 1) {
			if (!(isset($this->params["course"]))) {
				$rawWidget = new \Widgets\RawHtml();
				$rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
				$ajaxResponseObject->addWidget($rawWidget);
				return $ajaxResponseObject;
			}
			$course = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["course"]);
			$subgroups = $course->get_subgroups();
			foreach ($subgroups as $subgroup) {
				if ($subgroup->get_name() == "learners") {
					$group = $subgroup;
				}
				if ($subgroup->get_name() == "staff") {
					$staffgroup = $subgroup;
				}
			}
		} else {
			if (!(isset($this->params["group"]))) {
				$rawWidget = new \Widgets\RawHtml();
				$rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
				$ajaxResponseObject->addWidget($rawWidget);
				return $ajaxResponseObject;
			}
			$group = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["group"]);
			if ($this->params["group_admin"] == 0) {
				$staffgroup = $user;
			} else {
				$staffgroup = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["group_admin"]);
			}
		}
		
		// create data structure and set access rights
		$rapidfeedback = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $this->params["title"], $group->get_workroom(), $this->params["desc"]);
		$rapidfeedback->set_attribute("OBJ_TYPE", "RAPIDFEEDBACK_CONTAINER");
		$rapidfeedback->set_attribute("RAPIDFEEDBACK_GROUP", $group);
		$rapidfeedback->set_attribute("RAPIDFEEDBACK_STAFF", $staffgroup);
		$rapidfeedback->set_attribute("RAPIDFEEDBACK_ADMIN_SURVEY", 1);
		$rapidfeedback->set_sanction_all($group);
		$rapidfeedback->set_sanction_all($staffgroup);
		
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
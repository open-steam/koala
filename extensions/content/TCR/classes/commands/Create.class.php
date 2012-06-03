<?php
namespace TCR\Commands;
class Create extends \AbstractCommand implements \IAjaxCommand {
	
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
		if (!isset($this->params["group_course"])) {
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
			$ajaxResponseObject->addWidget($rawWidget);
			return $ajaxResponseObject;
		}
		
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		if ($this->params["group_course"] == 1) {
			// course
			if (!(isset($this->params["course"]))) {
				$rawWidget = new \Widgets\RawHtml();
				$rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
				$ajaxResponseObject->addWidget($rawWidget);
				return $ajaxResponseObject;
			}
			$course = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["course"]);
			$subgroups = $course->get_subgroups();
			foreach ($subgroups as $subgroup) {
				if ($subgroup->get_name() == "staff") {
					$staff = $subgroup->get_members();
					$admins = array();
					foreach ($staff as $staffMember) {
						if ($staffMember instanceof \steam_user) {
							array_push($admins, $staffMember->get_id());
						}
					}
				} else if ($subgroup->get_name() == "learners") {
					$group = $subgroup;
				}
			}
		} else {
			// group
			if (!(isset($this->params["group"]))) {
				$rawWidget = new \Widgets\RawHtml();
				$rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
				$ajaxResponseObject->addWidget($rawWidget);
				return $ajaxResponseObject;
			}
			$group = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["group"]);
			$admins = array($user->get_id());
		}
		
		if (intval($this->params["rounds"]) == 0) {
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("Error: Rundenzahl bitte als Integer eingeben.");
			$ajaxResponseObject->addWidget($rawWidget);
			return $ajaxResponseObject;
		}
		// create data structure and set access rights
		$TCR = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $this->params["title"], $group->get_workroom(), $this->params["title"]);
        \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "theses", $TCR, "container for theses");
		\steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "reviews", $TCR, "container for reviews");
		\steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "responses", $TCR, "container for responses");
		$TCR->set_attribute("OBJ_TYPE", "TCR_CONTAINER");
		$TCR->set_attribute("TCR_ROUNDS", $this->params["rounds"]);
		$TCR->set_attribute("TCR_USERS", array());
		$TCR->set_attribute("TCR_ADMINS", $admins);
		$TCR->set_attribute("TCR_GROUP", $group);
		$TCR->set_sanction_all($group);
		
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		closeDialog();
		sendRequest("LoadContent", {"id":"{$this->id}"}, "explorerWrapper", "updater", null, null, "explorer");
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		
		return $ajaxResponseObject;
	}
}
?>
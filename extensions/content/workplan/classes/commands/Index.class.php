<?php
namespace Workplan\Commands;
class Index extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand  {
	
	private $params;
	private $deleteid = 0;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		if (isset($this->params["deleteid"])) {
			$this->deleteid = $this->params["deleteid"];
		}
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$rawWidget = $this->displayWorkplans();
		$frameResponseObject->setTitle("Projektplanverwaltung");
		$frameResponseObject->setHeadline("Projektplanverwaltung");
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		if ($this->deleteid > 0) {
			$deleteObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->deleteid);
			if ($user->get_id() == $deleteObject->get_creator()->get_id()) {
				$deleteObject->delete();
			}			
		}
		
		$rawWidget = $this->displayWorkplans();
		$rawWidget->setJs(<<<END
		document.getElementById("confirmation").innerHTML = "Projektplan {$this->params["name"]} erfolgreich gelöscht.";
		appearConfirmation();
END
		);
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($rawWidget);
		return $ajaxResponseObject;
	}
	
	private function displayWorkplans() {
		$workplanExtension = \Workplan::getInstance();
		$workplanExtension->addJS();
		$content = $workplanExtension->loadTemplate("workplan_index.template.html");
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$workplans_private = array();
		$workplans_group = array();
		
		// get all workplans from the private workroom of the current user
		$inventory = $user->get_workroom()->get_inventory();
		for ($count = 0; $count < count($inventory); $count++) {
			$object = $inventory[$count];
			if (in_array("OBJ_TYPE", $object->get_attribute_names()) && $object->get_attribute("OBJ_TYPE") == "WORKPLAN_CONTAINER") {
				array_push($workplans_private, $object);
			}
		}
		usort($workplans_private, 'sort_workplans');
		
		// search for workplans in workrooms of all groups the current user is member in
		$groups = $user->get_groups();
		for ($count = 0; $count < count($groups); $count++) {
			$groupWorkroom = $groups[$count]->get_workroom();
			$inventory = $groupWorkroom->get_inventory();
			for ($count2 = 0; $count2 < count($inventory); $count2++) {
				$object = $inventory[$count2];
				if (in_array("OBJ_TYPE", $object->get_attribute_names()) && $object->get_attribute("OBJ_TYPE") == "WORKPLAN_CONTAINER") {
					array_push($workplans_group, $object);
				}
			}
		}
		usort($workplans_group, 'sort_workplans');
		
		$content->setCurrentBlock("BLOCK_CONFIRMATION");
		$content->setVariable("CONFIRMATION_TEXT", "NONE");
		$content->parse("BLOCK_CONFIRMATION");
		
		$content->setCurrentBlock("BLOCK_WORKPLAN_ADMINISTRATION_ACTIONBAR");
		$content->setVariable("WORKPLAN_PATH_URL", $this->getExtension()->getExtensionUrl());
		$content->setVariable("LABEL_CREATE_PROJECT", "Neuer Projektplan");
		$content->parse("BLOCK_WORKPLAN_ADMINISTRATION_ACTIONBAR");

		// display private workplan table
		if (count($workplans_private) != 0) {
			$content->setCurrentBlock("BLOCK_WORKPLAN_ADMINISTRATION");
			$content->setVariable("WORKPLAN_ADMINISTRATION_ID", "Nummer");
			$content->setVariable("WORKPLAN_ADMINISTRATION_NAME", "Name");
			$content->setVariable("WORKPLAN_PROJECTS_KIND", "Private Projektpläne");
			for ($count = 0; $count < count($workplans_private); $count++) {
				$content->setCurrentBlock("BLOCK_WORKPLAN_ADMINISTRATION_ELEMENT");
				$content->setVariable("WORKPLAN_PATH_URL", $this->getExtension()->getExtensionUrl());
				$content->setVariable("WORKPLAN_ADMINISTRATION_ELEMENT_ID", $workplans_private[$count]->get_id());
				$content->setVariable("WORKPLAN_ADMINISTRATION_ELEMENT_NAME", $workplans_private[$count]->get_name());
				$content->setVariable("WORKPLAN_ADMINISTRATION_CHANGE_VALUE", "Bearbeiten");
				$content->setVariable("WORKPLAN_ADMINISTRATION_DELETE_VALUE", "Löschen");
				$content->parse("BLOCK_WORKPLAN_ADMINISTRATION_ELEMENT");
			}
			$content->parse("BLOCK_WORKPLAN_ADMINISTRATION");
		} else {
			$content->setCurrentBlock("BLOCK_WORKPLAN_ADMINISTRATION_NO_PROJECTS_PRIVATE");
			$content->setVariable("WORKPLAN_PROJECTS_KIND", "Private Projektpläne");
			$content->setVariable("WORKPLAN_ADMINISTRATION_NO_PROJECT", "Keine privaten Projektpläne vorhanden.");
			$content->parse("BLOCK_WORKPLAN_ADMINISTRATION_NO_PROJECTS_PRIVATE");
		}
			
		// display group workplan table
		if (count($workplans_group) != 0) {
			$content->setCurrentBlock("BLOCK_WORKPLAN_ADMINISTRATION");
			$content->setVariable("WORKPLAN_PATH_URL", $this->getExtension()->getExtensionUrl());
			$content->setVariable("WORKPLAN_ADMINISTRATION_ID", "Nummer");
			$content->setVariable("WORKPLAN_ADMINISTRATION_NAME", "Name");
			$content->setVariable("WORKPLAN_PROJECTS_KIND", "Gruppen Projektpläne");
			for ($count = 0; $count < count($workplans_group); $count++) {				
				$content->setCurrentBlock("BLOCK_WORKPLAN_ADMINISTRATION_ELEMENT");
				$content->setVariable("WORKPLAN_PATH_URL", $this->getExtension()->getExtensionUrl());
				$content->setVariable("WORKPLAN_ADMINISTRATION_ELEMENT_ID", $workplans_group[$count]->get_id());
				$content->setVariable("WORKPLAN_ADMINISTRATION_ELEMENT_NAME", $workplans_group[$count]->get_name());
				$content->setVariable("WORKPLAN_ADMINISTRATION_CHANGE_VALUE", "Bearbeiten");
				$content->setVariable("WORKPLAN_ADMINISTRATION_DELETE_VALUE", "Löschen");
				if ($workplans_group[$count]->get_creator()->get_id() == $user->get_id()) {	
					$content->setVariable("WORKPLAN_ADMINISTRATION_ELEMENT_RIGHTS", "");	
				} else {
					$content->setVariable("WORKPLAN_ADMINISTRATION_ELEMENT_RIGHTS", "none");
				}
				$content->parse("BLOCK_WORKPLAN_ADMINISTRATION_ELEMENT");
			}
			$content->parse("BLOCK_WORKPLAN_ADMINISTRATION");
		} else {
			$content->setCurrentBlock("BLOCK_WORKPLAN_ADMINISTRATION_NO_PROJECTS_GROUP");
			$content->setVariable("WORKPLAN_PROJECTS_KIND", "Gruppen Projektpläne");
			$content->setVariable("WORKPLAN_ADMINISTRATION_NO_PROJECT", "Keine Gruppen Projektpläne vorhanden.");
			$content->parse("BLOCK_WORKPLAN_ADMINISTRATION_NO_PROJECTS_GROUP");
		}
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		return $rawWidget;
	}
}
?>
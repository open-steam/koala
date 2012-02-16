<?php
namespace Workplan\Commands;
class UpdateUsers extends \AbstractCommand implements \IAjaxCommand  {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$workplanExtension = \Workplan::getInstance();
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$content = $workplanExtension->loadTemplate("workplan_users.template.html");
		$portal = \lms_portal::get_instance();
		$objectID = $this->params["id"];
		$workplanContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectID);
		
		if ($user->get_id() != $workplanContainer->get_creator()->get_id()) {
			$this->params["newChanges"] = 0;
			$this->params["change"] = 0;
		}
		
		// if user submitted new changes save them
		if ($this->params["newChanges"] == 1) {
			$oids = json_decode($this->params["oids"]);
			$ressources = json_decode($this->params["ressources"]);
			$roles = json_decode($this->params["roles"]);
	
			$workplanContainer->set_attribute("WORKPLAN_" . $workplanContainer->get_creator()->get_id() . "_RESSOURCE", $this->params["leaderRes"]);
			for ($count = 0; $count < count($oids); $count++) {
				$workplanContainer->set_attribute("WORKPLAN_" . $oids[$count] . "_RESSOURCE", $ressources[$count]);
				if ($roles[$count] == 1) {
					$workplanContainer->set_attribute("WORKPLAN_" . $oids[$count] . "_LEADER", "LEADER");
				} else {
					$workplanContainer->delete_attribute("WORKPLAN_" . $oids[$count] . "_LEADER");
				}
			}
		}
		
		// check if workplan is in group and save the group members in array (sorted according to their role)
		$group = 0;
		if (in_array("WORKPLAN_GROUP", $workplanContainer->get_attribute_names())) {
			$group = $workplanContainer->get_attribute("WORKPLAN_GROUP");
		}
		$users = array();
		array_push($users, $workplanContainer->get_creator());
		if ($group != 0) {
			$groupObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $group);
			$normalusers = array();
			$groupusers = $groupObject->get_members();
			for ($count = 0; $count < count($groupusers); $count++) {
				if ($groupusers[$count]->get_id() != $workplanContainer->get_creator()->get_id()) {
					if (in_array("WORKPLAN_" . $groupusers[$count]->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
						array_push($users, $groupusers[$count]);
					} else {
						array_push($normalusers, $groupusers[$count]);
					}
				}
			}
			$users = array_merge($users,$normalusers);
		}
		
		// display edit view
		if ($this->params["change"] == 1) {
			$content->setCurrentBlock("BLOCK_USERS_CHANGE_LIST");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_NAME", "Name");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_ROLE", "Rolle");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_RESSOURCE", "Ressourcenwert");
			
			$content->setCurrentBlock("BLOCK_USERS_CREATOR");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_ROLE_VALUE", "Projektersteller");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_NAME_VALUE", $workplanContainer->get_creator()->get_full_name());
			$content->setVariable("WORKPLAN_USERS_ELEMENT_RESSOURCE_VALUE", $workplanContainer->get_attribute("WORKPLAN_" . $workplanContainer->get_creator()->get_id() . "_RESSOURCE"));
			$content->parse("BLOCK_USERS_CREATOR");

			for ($count = 0; $count < count($users); $count++) {
				if ($users[$count]->get_id() != $workplanContainer->get_creator()->get_id()) {
					if (in_array("WORKPLAN_" . $users[$count]->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
						$content->setCurrentBlock("BLOCK_USERS_CHANGE_ELEMENT_LEADER");
						$content->setVariable("WORKPLAN_USERS_ELEMENT_NAME_VALUE", $users[$count]->get_full_name());
						$content->setVariable("WORKPLAN_USERS_ELEMENT_RESSOURCE_VALUE", $workplanContainer->get_attribute("WORKPLAN_" . $users[$count]->get_id() . "_RESSOURCE"));
						$content->setVariable("WORKPLAN_USERS_ELEMENT_OID", $users[$count]->get_id());
						$content->setVariable("WORKPLAN_WORKER", "Mitarbeiter");
						$content->setVariable("WORKPLAN_LEADER", "Projektleiter");
						$content->parse("BLOCK_USERS_CHANGE_ELEMENT_LEADER");
					} else {
						$content->setCurrentBlock("BLOCK_USERS_CHANGE_ELEMENT_WORKER");
						$content->setVariable("WORKPLAN_USERS_ELEMENT_NAME_VALUE", $users[$count]->get_full_name());
						$content->setVariable("WORKPLAN_USERS_ELEMENT_RESSOURCE_VALUE", $workplanContainer->get_attribute("WORKPLAN_" . $users[$count]->get_id() . "_RESSOURCE"));
						$content->setVariable("WORKPLAN_USERS_ELEMENT_OID", $users[$count]->get_id());
						$content->setVariable("WORKPLAN_WORKER", "Mitarbeiter");
						$content->setVariable("WORKPLAN_LEADER", "Projektleiter");
						$content->parse("BLOCK_USERS_CHANGE_ELEMENT_WORKER");
					}
				}
			}
			$content->setVariable("LABEL_SAVE", "Änderungen speichern");
			$content->setVariable("LABEL_BACK", "Abbrechen");
			$content->setVariable("WORKPLAN_ID", $objectID);
			$content->parse("BLOCK_USERS_CHANGE_LIST");
		// display normal view
		} else if ($this->params["change"] == 0) {
			$content->setCurrentBlock("BLOCK_USERS_LIST");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_NAME", "Name");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_ROLE", "Rolle");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_RESSOURCE", "Ressourcenwert");
			for ($count = 0; $count < count($users); $count++) {
				$content->setCurrentBlock("BLOCK_USERS_ELEMENT");
				$content->setVariable("WORKPLAN_USERS_ELEMENT_NAME_VALUE", $users[$count]->get_full_name());
				$content->setVariable("WORKPLAN_USERS_ELEMENT_RESSOURCE_VALUE", $workplanContainer->get_attribute("WORKPLAN_" . $users[$count]->get_id() . "_RESSOURCE"));
				if ($workplanContainer->get_creator()->get_id() == $users[$count]->get_id()) {
					$content->setVariable("WORKPLAN_USERS_ELEMENT_ROLE_VALUE", "Projektersteller");
				} else if (in_array("WORKPLAN_" . $users[$count]->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
					$content->setVariable("WORKPLAN_USERS_ELEMENT_ROLE_VALUE", "Projektleiter");
				} else {
					$content->setVariable("WORKPLAN_USERS_ELEMENT_ROLE_VALUE", "Mitarbeiter");
				}
				$content->parse("BLOCK_USERS_ELEMENT");
			}
			$content->parse("BLOCK_USERS_LIST");
		}
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($rawWidget);
		return $ajaxResponseObject;
	}
}
?>
<?php
namespace Workplan\Commands;
class Users extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$workplanExtension = \Workplan::getInstance();
		$workplanExtension->addJS();
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$portal = \lms_portal::get_instance();
		$objectID = $this->params[0];
		$workplanContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectID);
		
		// move workplan to group workroom if form was submitted
		if ($_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["move"])) {
			$values = $_POST["values"];
			$groupID = $values["groupid"];
			$newgroup = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $groupID);
			$newroom = $newgroup->get_workroom();
			$workplanContainer->set_attribute("WORKPLAN_GROUP", $groupID);
			$workplanContainer->move($newroom);
			$portal->set_confirmation("Projektplan " . $workplanContainer->get_name() . " erfolgreich in Gruppe " . $newgroup->get_name() . " verschoben.");
		}
		
		if (is_object($workplanContainer) && $workplanContainer instanceof \steam_room) {
			$content = $workplanExtension->loadTemplate("workplan_users.template.html");
			$content->setCurrentBlock("BLOCK_CONFIRMATION");
			$content->setVariable("CONFIRMATION_TEXT", "NONE");
			$content->parse("BLOCK_CONFIRMATION");
			$confirmationBar = new \Widgets\RawHtml();
			$confirmationBar->setHtml($content->get());
			$frameResponseObject->addWidget($confirmationBar);
			
			// if current user has required rights display actionbar
			if ($workplanContainer->get_creator()->get_id() == $user->get_id()) {
				$actionBar = new \Widgets\ActionBar();
				$actionBar->setActions(array(array("name"=>"Mitarbeiter bearbeiten", "link"=>"javascript:changeUsers(" . $objectID . ")")));
				$frameResponseObject->addWidget($actionBar);
			}
			
			$tabBar = new \Widgets\TabBar();
			$tabBar->setTabs(array(
				array("name"=>"Überblick", "link"=>$this->getExtension()->getExtensionUrl() . "overview/" . $objectID), 
				array("name"=>"Tabelle", "link"=>$this->getExtension()->getExtensionUrl() . "listView/" . $objectID), 
				array("name"=>"Gantt-Diagramm", "link"=>$this->getExtension()->getExtensionUrl() . "ganttView/" . $objectID), 
				array("name"=>"Mitarbeiter", "link"=>$this->getExtension()->getExtensionUrl() . "users/" . $objectID), 
				array("name"=>"Snapshots", "link"=>$this->getExtension()->getExtensionUrl() . "snapshots/" . $objectID)));
			$tabBar->setActiveTab(3);
			$frameResponseObject->addWidget($tabBar);
			
			$content = $workplanExtension->loadTemplate("workplan_users.template.html");
			$content->setCurrentBlock("BLOCK_USERS_LIST");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_NAME", "Name");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_ROLE", "Rolle");
			$content->setVariable("WORKPLAN_USERS_ELEMENT_RESSOURCE", "Ressourcenwert");
			
			// check if workplan is in group and save the right users in array (sorted according to their role)
			$group = 0;
			if (in_array("WORKPLAN_GROUP", $workplanContainer->get_attribute_names())) {
				$group = $workplanContainer->get_attribute("WORKPLAN_GROUP");
			}
			if ($group == 0) {
				$users = array();
				array_push($users, $workplanContainer->get_creator());
			} else {
				$groupObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $group);
				$users = array();
				$normalusers = array();
				$groupusers = $groupObject->get_members();
				array_push($users, $workplanContainer->get_creator());
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
			
			// display found users sorted according to their role
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
			
			// if workplan is private display dialog to move it to a group the current user is member in
			if ($group == 0) {
				$groups = $user->get_groups();
				$koalagroups = array();
				for ($count = 0; $count < count($groups); $count++) {
					$currentGroup = $groups[$count];
					if (substr($currentGroup->get_groupname(),0,10) == 'PrivGroups' | substr($currentGroup->get_groupname(),0,12) == 'PublicGroups') {
						array_push($koalagroups, $currentGroup);
					}
				}
				if (count($koalagroups) > 0) {
					$content->setCurrentBlock("BLOCK_USERS_TOGROUP");
					$content->setVariable("WORKPLAN_TOGROUP", "Verschieben in Gruppe: ");
					$content->setVariable("GROUPCOUNT", count($koalagroups));
					$content->setVariable("LABEL_MOVE", "Verschieben");
					
					for ($count = 0; $count < count($koalagroups); $count++) {
						$currentGroup = $koalagroups[$count];
						$content->setCurrentBlock("BLOCK_USERS_TOGROUP_ELEMENT");
						$content->setVariable("GROUPID", $currentGroup->get_id());
						$content->setVariable("GROUPNAME", $currentGroup->get_name());
						$content->parse("BLOCK_USERS_TOGROUP_ELEMENT");
					}
					$content->parse("BLOCK_USERS_TOGROUP");
				}
			}
			
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml($content->get());
			$frameResponseObject->setTitle("Projektplan: " . $workplanContainer->get_name());
			$frameResponseObject->setHeadline(array(
				array("link"=>$this->getExtension()->getExtensionUrl(), "name"=>"Projektplanverwaltung"),
				array("", "name"=>$workplanContainer->get_name())));
			$frameResponseObject->addWidget($rawWidget);
			return $frameResponseObject;
		}
	}
}
?>
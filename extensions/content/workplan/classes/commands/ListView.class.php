<?php
namespace Workplan\Commands;
class ListView extends \AbstractCommand implements \IFrameCommand {
	
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
			
		if (is_object($workplanContainer) && $workplanContainer instanceof \steam_container) {
			// if current user has required rights display actionbar
			if ($user->get_id() == $workplanContainer->get_creator()->get_id() || in_array("WORKPLAN_" . $user->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
				$content = $workplanExtension->loadTemplate("workplan_listview.template.html");
				$content->setCurrentBlock("BLOCK_CONFIRMATION");
				$content->setVariable("CONFIRMATION_TEXT", "NONE");
				$content->parse("BLOCK_CONFIRMATION");
				
				$content->setCurrentBlock("BLOCK_WORKPLAN_LIST_ACTIONBAR");
				$content->setVariable("WORKPLAN_ID", $objectID);
				$content->setVariable("LABEL_NEW_SNAPSHOT", "Snapshot erstellen");
				$content->setVariable("LABEL_NEW_MILESTONE", "Neuer Meilenstein");
				$content->setVariable("LABEL_NEW_TASK", "Neuer Vorgang");
				$content->parse("BLOCK_WORKPLAN_LIST_ACTIONBAR");
				
				$actionBar = new \Widgets\RawHtml();
				$actionBar->setHtml($content->get());
				$frameResponseObject->addWidget($actionBar);
			}
			
			$tabBar = new \Widgets\TabBar();
			$tabBar->setTabs(array(
				array("name"=>"Überblick", "link"=>$this->getExtension()->getExtensionUrl() . "overview/" . $objectID), 
				array("name"=>"Tabelle", "link"=>$this->getExtension()->getExtensionUrl() . "listView/" . $objectID), 
				array("name"=>"Gantt-Diagramm", "link"=>$this->getExtension()->getExtensionUrl() . "ganttView/" . $objectID), 
				array("name"=>"Mitarbeiter", "link"=>$this->getExtension()->getExtensionUrl() . "users/" . $objectID), 
				array("name"=>"Snapshots", "link"=>$this->getExtension()->getExtensionUrl() . "snapshots/" . $objectID)));
			$tabBar->setActiveTab(1);
			$frameResponseObject->addWidget($tabBar);
			
			// load workplan from xml file in workplan room and sort elements
			$xmlfile = $workplanContainer->get_inventory_filtered(array(array("+", "class", CLASS_DOCUMENT)));
			$xml = simplexml_load_string($xmlfile[0]->get_content());
			$helpToArray = $xml->children();
			$list = array();
			for ($counter = 0; $counter < count($helpToArray); $counter++) {
				array_push($list, $helpToArray[$counter]);
			}
			usort($list, 'sort_xmllist');
			
			$content = $workplanExtension->loadTemplate("workplan_listview.template.html");
			if (count($list) == 0) {
				$content->setCurrentBlock("BLOCK_WORKPLAN_LIST_EMPTY");
				$content->setVariable("WORKPLAN_LIST_EMPTY", "Keine Meilensteine oder Vorgänge zu diesem Projektplan vorhanden.");
				$content->parse("BLOCK_WORKPLAN_LIST_EMPTY");
			} else {
				$content->setCurrentBlock("BLOCK_WORKPLAN_LIST");
				$content->setVariable("WORKPLAN_LIST_ELEMENT_NAME","Name");
				$content->setVariable("WORKPLAN_LIST_ELEMENT_START","Beginn");
				$content->setVariable("WORKPLAN_LIST_ELEMENT_END","Ende");
				$content->setVariable("WORKPLAN_LIST_ELEMENT_DURATION","Arbeitsstunden");
				$content->setVariable("WORKPLAN_LIST_ELEMENT_DEPENDS","Abhängigkeit");
				$content->setVariable("WORKPLAN_LIST_ELEMENT_USERS","Mitarbeiter");
				// create and fill array for displaying dependencies
				$dependencies = array();
				for ($counter = 0; $counter < count($list); $counter++) {
					$dependencies[strval($list[$counter]->oid)] = $counter+1;
				}
				// create list view of current elements
				for ($counter = 0; $counter < count($list); $counter++) {
					$element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $list[$counter]->oid);
					$elinventory = $element->get_inventory();
					$content->setCurrentBlock("BLOCK_WORKPLAN_LIST_ELEMENT");
					$content->setVariable("WORKPLAN_LIST_ELEMENT_NUMBER", $counter+1);
					$content->setVariable("WORKPLAN_LIST_ELEMENT_NAME_VALUE", $list[$counter]->name . " (" . count($elinventory) . ")");
					$content->setVariable("WORKPLAN_LIST_ELEMENT_START_VALUE", date("d.m.Y", (int) $list[$counter]->start));
					$content->setVariable("WORKPLAN_LIST_ELEMENT_END_VALUE", date("d.m.Y", (int) $list[$counter]->end));
					if ($list[$counter]->duration == -1) {
						$content->setVariable("WORKPLAN_LIST_ELEMENT_DURATION_VALUE", "-");
					} else {
						$content->setVariable("WORKPLAN_LIST_ELEMENT_DURATION_VALUE", $list[$counter]->duration);
					}
					$content->setVariable("WORKPLAN_LIST_ELEMENT_DURATION_UPDATE", $list[$counter]->duration);
					if ($list[$counter]->depends == -1) {
						$content->setVariable("WORKPLAN_LIST_ELEMENT_DEPENDS_VALUE", "-");
					} else if (array_key_exists(strval($list[$counter]->depends), $dependencies)) {
						$content->setVariable("WORKPLAN_LIST_ELEMENT_DEPENDS_VALUE", $dependencies[strval($list[$counter]->depends)]);
					}
					$content->setVariable("WORKPLAN_LIST_ELEMENT_DEPENDS_UPDATE", $list[$counter]->depends);
					if ($list[$counter]->getName() == 'task') {
						$content->setVariable("WORKPLAN_LIST_ELEMENT_MOT", "0");
					} else {
						$content->setVariable("WORKPLAN_LIST_ELEMENT_MOT", "1");
					}
					$content->setVariable("WORKPLAN_LIST_ELEMENT_OID", $list[$counter]->oid);
					$content->setVariable("WORKPLAN_LIST_ELEMENT_NAME_DEL", $list[$counter]->name);
					$content->setVariable("WORKPLAN_ID", $objectID);
					// convert saved users to output format
					$userList = $list[$counter]->users;
					$userList = "[" . str_replace(";",",",$userList) . "]";
					$content->setVariable("WORKPLAN_LIST_ELEMENT_USERS_JSON", $userList);
					$userList = json_decode($userList);
					$outputUsers = "";
					for ($count = 0; $count < count($userList); $count++) {
						$currentUser = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $userList[$count]);
						$outputUsers = $outputUsers . $currentUser->get_full_name() . "<br>";
					}
					if (count($userList) == 0) {
						$outputUsers = "-";
					}
					$content->setVariable("WORKPLAN_LIST_ELEMENT_USERS_VALUE", $outputUsers);
					$content->setVariable("WORKPLAN_LIST_UPDATE_OID", $list[$counter]->oid);
					$content->setVariable("WORKPLAN_LIST_ELEMENT_CHANGE_VALUE", "Bearbeiten");
					$content->setVariable("WORKPLAN_LIST_ELEMENT_DELETE_VALUE", "Löschen");
					if ($user->get_id() == $workplanContainer->get_creator()->get_id() || in_array("WORKPLAN_" . $user->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
						$content->setVariable("WORKPLAN_RIGHTS", "");
					} else {
						$content->setVariable("WORKPLAN_RIGHTS", "none");
					}
					$content->parse("BLOCK_WORKPLAN_LIST_ELEMENT");
				}
				$content->parse("BLOCK_WORKPLAN_LIST");
			}	
			
			// create update dialogs
			$content->setCurrentBlock("BLOCK_WORKPLAN_UPDATE_DIALOG");
			$content->setVariable("WORKPLAN_UPDATE_ID", $objectID);
			$content->setVariable("UPDATE_LABEL", "Meilenstein bearbeiten");
			$content->setVariable("UPDATE_TASK_LABEL", "Vorgang bearbeiten");
			$content->setVariable("UPDATE_NAME_LABEL","Name:*");
			$content->setVariable("UPDATE_DATE_LABEL", "Datum:*");
			$content->setVariable("UPDATE_START_LABEL", "Beginn:*");
			$content->setVariable("UPDATE_END_LABEL", "Ende:*");
			$content->setVariable("UPDATE_DURATION_LABEL","Dauer:");
			$content->setVariable("UPDATE_DEPENDS_LABEL","Abhängigkeit:");
			
			// create all dependency options in update dialogs
			$content->setCurrentBlock("UPDATE_BLOCK_WORKPLAN_UPDATE_DEPENDS");
			$content->setVariable("DEPENDS_OID", "-1");
			$content->setVariable("DEPENDS_NAME", "Keine Abhängigkeit");
			$content->parse("BLOCK_WORKPLAN_UPDATE_DEPENDS");
			$content->setCurrentBlock("UPDATE_BLOCK_WORKPLAN_UPDATE_TASK_DEPENDS");
			$content->setVariable("DEPENDS_OID", "-1");
			$content->setVariable("DEPENDS_NAME", "Keine Abhängigkeit");
			$content->parse("BLOCK_WORKPLAN_UPDATE_TASK_DEPENDS");

			for ($count = 0; $count < count($list); $count++) {
				if ($list[$count]->getName() == 'task') {
					$content->setCurrentBlock("BLOCK_WORKPLAN_UPDATE_DEPENDS");
					$content->setVariable("DEPENDS_OID", $list[$count]->oid);
					$content->setVariable("DEPENDS_NAME", $list[$count]->name);
					$content->parse("BLOCK_WORKPLAN_UPDATE_DEPENDS");
					
					$content->setCurrentBlock("BLOCK_WORKPLAN_UPDATE_TASK_DEPENDS");
					$content->setVariable("DEPENDS_OID", $list[$count]->oid);
					$content->setVariable("DEPENDS_NAME", $list[$count]->name);
					$content->parse("BLOCK_WORKPLAN_UPDATE_TASK_DEPENDS");
				}
			}
			// create user options in update dialogs
			$content->setVariable("UPDATE_USERS_LABEL", "Mitarbeiter:");
			$groupID = 0;
			if (in_array("WORKPLAN_GROUP", $workplanContainer->get_attribute_names())) {
				$groupID = $workplanContainer->get_attribute("WORKPLAN_GROUP");
			}
			if ($groupID == 0) {
				$content->setCurrentBlock("BLOCK_WORKPLAN_UPDATE_USER");
				$content->setVariable("USER_ID", $user->get_id());
				$content->setVariable("USER_NAME", $user->get_full_name());
				$content->parse("BLOCK_WORKPLAN_UPDATE_USER");
				
				$content->setCurrentBlock("BLOCK_WORKPLAN_UPDATE_TASK_USER");
				$content->setVariable("USER_ID", $user->get_id());
				$content->setVariable("USER_NAME", $user->get_full_name());
				$content->parse("BLOCK_WORKPLAN_UPDATE_TASK_USER");
			} else {
				$groupObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $groupID);
				$members = $groupObject->get_members();
				for ($count = 0; $count < count($members); $count++) {
					$currentMember = $members[$count];
					$content->setCurrentBlock("BLOCK_WORKPLAN_UPDATE_USER");
					$content->setVariable("USER_ID", $currentMember->get_id());
					$content->setVariable("USER_NAME", $currentMember->get_full_name());
					$content->parse("BLOCK_WORKPLAN_UPDATE_USER");
					
					$content->setCurrentBlock("BLOCK_WORKPLAN_UPDATE_TASK_USER");
					$content->setVariable("USER_ID", $currentMember->get_id());
					$content->setVariable("USER_NAME", $currentMember->get_full_name());
					$content->parse("BLOCK_WORKPLAN_UPDATE_TASK_USER");
				}
			}
			
			$content->setVariable("UPDATE_SUBMIT_LABEL","Abschicken");
			$content->setVariable("UPDATE_LABEL_BACK","Abbrechen");
			$content->parse("BLOCK_WORKPLAN_UPDATE_DIALOG");
			
			// create task and milestone create forms
			$content->setCurrentBlock("BLOCK_WORKPLAN_LIST_FORMULAR");
			$content->setVariable("LABEL_NEW_MILESTONE", "Meilenstein hinzufügen");
			$content->setVariable("LABEL_NEW_TASK", "Vorgang hinzufügen");
			$content->setVariable("NAME_LABEL","Name:*");
			$content->setVariable("START_LABEL","Beginn:*");
			$content->setVariable("END_LABEL","Ende:*");
			$content->setVariable("DATE_LABEL","Datum:*");
			$content->setVariable("DURATION_LABEL","Dauer:");
			$content->setVariable("DEPENDS_LABEL","Abhängigkeit:");
			$content->setVariable("USERS_LABEL","Mitarbeiter:");
			
			// create all dependency options in create forms
			$content->setCurrentBlock("BLOCK_LIST_MILESTONE_DEPENDS");
			$content->setVariable("DEPENDS_OID", "-1");
			$content->setVariable("DEPENDS_NAME", "Keine Abhängigkeit");
			$content->parse("BLOCK_LIST_MILESTONE_DEPENDS");
			
			$content->setCurrentBlock("BLOCK_LIST_TASK_DEPENDS");
			$content->setVariable("DEPENDS_OID", "-1");
			$content->setVariable("DEPENDS_NAME", "Keine Abhängigkeit");
			$content->parse("BLOCK_LIST_TASK_DEPENDS");
				
			for ($count = 0; $count < count($list); $count++) {
				if ($list[$count]->getName() == 'task') {
					$content->setCurrentBlock("BLOCK_LIST_MILESTONE_DEPENDS");
					$content->setVariable("DEPENDS_OID", $list[$count]->oid);
					$content->setVariable("DEPENDS_NAME", $list[$count]->name);
					$content->parse("BLOCK_LIST_MILESTONE_DEPENDS");
					
					$content->setCurrentBlock("BLOCK_LIST_TASK_DEPENDS");
					$content->setVariable("DEPENDS_OID", $list[$count]->oid);
					$content->setVariable("DEPENDS_NAME", $list[$count]->name);
					$content->parse("BLOCK_LIST_TASK_DEPENDS");
				}
			}
			
			// create user options in create forms
			if ($groupID == 0) {
				$content->setCurrentBlock("BLOCK_USER_OPTION_MILESTONE");
				$content->setVariable("USER_ID", $user->get_id());
				$content->setVariable("USER_NAME", $user->get_full_name());
				$content->parse("BLOCK_USER_OPTION_MILESTONE");
				
				$content->setCurrentBlock("BLOCK_USER_OPTION_TASK");
				$content->setVariable("USER_ID", $user->get_id());
				$content->setVariable("USER_NAME", $user->get_full_name());
				if (in_array("WORKPLAN_" . $user->get_id() . "_RESSOURCE", $workplanContainer->get_attribute_names())) {
					$content->setVariable("USER_RESSOURCE", $workplanContainer->get_attribute("WORKPLAN_" . $user->get_id() . "_RESSOURCE"));
				} else {
					$content->setVariable("USER_RESSOURCE", 0);
				}
				$content->parse("BLOCK_USER_OPTION_TASK");
			} else {
				$groupObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $groupID);
				$members = $groupObject->get_members();
				for ($count = 0; $count < count($members); $count++) {
					$currentMember = $members[$count];
					
					$content->setCurrentBlock("BLOCK_USER_OPTION_MILESTONE");
					$content->setVariable("USER_ID", $currentMember->get_id());
					$content->setVariable("USER_NAME", $currentMember->get_full_name());
					$content->parse("BLOCK_USER_OPTION_MILESTONE");
					
					$content->setCurrentBlock("BLOCK_USER_OPTION_TASK");
					$content->setVariable("USER_ID", $currentMember->get_id());
					$content->setVariable("USER_NAME", $currentMember->get_full_name());
					if (in_array("WORKPLAN_" . $currentMember->get_id() . "_RESSOURCE", $workplanContainer->get_attribute_names())) {
						$content->setVariable("USER_RESSOURCE", $workplanContainer->get_attribute("WORKPLAN_" . $currentMember->get_id() . "_RESSOURCE"));
					} else {
						$content->setVariable("USER_RESSOURCE", 0);
					}
					$content->parse("BLOCK_USER_OPTION_TASK");
				}
			}
			
			$content->setVariable("LABEL_BACK","Ausblenden");
			$content->setVariable("LABEL_ADD","Hinzufügen");
			$content->setVariable("WORKPLAN_ID", $objectID);
			$content->parse("BLOCK_WORKPLAN_LIST_FORMULAR");
			
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
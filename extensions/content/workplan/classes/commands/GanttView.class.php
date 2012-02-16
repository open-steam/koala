<?php
namespace Workplan\Commands;
class GanttView extends \AbstractCommand implements \IFrameCommand {
	
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
		$workplanExtension->addJS($fileName = 'jsgantt.js');
		$workplanExtension->addCSS($fileName = 'jsgantt.css');
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$portal = \lms_portal::get_instance();
		$objectID = $this->params[0];
		$workplanContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectID);
		$xmlfile = $workplanContainer->get_inventory_filtered(array(array("+", "class", CLASS_DOCUMENT)));
		$createContainer = 0;
		
		// check if user submitted create milestone or create task form
		if ($_SERVER[ "REQUEST_METHOD" ] == "POST" ) {
			if (isset($_POST["new_milestone"])) {
				$createContainer = 1;
			} else if (isset($_POST["new_task"])) {
				$createContainer = 2;
			}
		}
		// create new milestone or task
		if ($createContainer != 0) {
			$xmltree = new \SimpleXMLElement($xmlfile[0]->get_content());
			if ($createContainer == 1) {
				$xml = $xmltree->addChild("milestone");
				$newName = $_POST["milestonename"];
				$newStart = $_POST["milestonedate"];
				$newStart = mktime(0,0,0,substr($newStart,3,2),substr($newStart,0,2),substr($newStart,6,4));
				$newEnd = $_POST["milestonedate"];
				$newEnd = mktime(0,0,0,substr($newEnd,3,2),substr($newEnd,0,2),substr($newEnd,6,4));
				if (strlen($_POST["milestoneduration"]) > 0) {
					$newDuration = $_POST["milestoneduration"];
				} else $newDuration = -1;
				if (strlen($_POST["milestonedepends"]) > 0) {
					$newDepends = $_POST["milestonedepends"];
				} else $newDepends = -1;
				$newUsers = "";
				if (isset($_POST["milestoneusers"])) {
					for ($count = 0; $count < count($_POST["milestoneusers"]); $count++) {
						$newUsers = $newUsers . $_POST["milestoneusers"][$count] . ",";
					}
					$newUsers = substr($newUsers, 0, strlen($newUsers)-1);
				}
				$portal->set_confirmation("Meilenstein " . $newName . " wurde erfolgreich erstellt.");
			} else {
				$xml = $xmltree->addChild("task");
				$newName = $_POST["taskname"];
				$newStart = $_POST["taskstart"];
				$newStart = mktime(0,0,0,substr($newStart,3,2),substr($newStart,0,2),substr($newStart,6,4));
				$newEnd = $_POST["taskend"];
				$newEnd = mktime(0,0,0,substr($newEnd,3,2),substr($newEnd,0,2),substr($newEnd,6,4));
				if (strlen($_POST["taskduration"]) > 0) {
					$newDuration = $_POST["taskduration"];
				} else $newDuration = -1;
				if (strlen($_POST["taskdepends"]) > 0) {
					$newDepends = $_POST["taskdepends"];
				} else $newDepends = -1;
				$newUsers = "";
				if (isset($_POST["taskusers"])) {
					for ($count = 0; $count < count($_POST["taskusers"]); $count++) {
						$newUsers = $newUsers . $_POST["taskusers"][$count] . ",";
					}
					$newUsers = substr($newUsers, 0, strlen($newUsers)-1);
				}
				$portal->set_confirmation("Vorgang " . $newName . " wurde erfolgreich erstellt.");
			}
			$newContainer = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $newName, $workplanContainer);
			$xml->addChild("name", $newName);
			$xml->addChild("oid", $newContainer->get_id());
			$newContainer->set_attribute("WORKPLAN_START", $newStart);
			$xml->addChild("start", $newStart);
			$newContainer->set_attribute("WORKPLAN_END", $newEnd);
			$xml->addChild("end", $newEnd);
			$xml->addChild("duration", $newDuration);
			$newContainer->set_attribute("WORKPLAN_DURATION", $newDuration);
			$xml->addChild("depends", $newDepends);
			$newContainer->set_attribute("WORKPLAN_DEPENDS", $newDepends);
			$newContainer->set_attribute("WORKPLAN_USERS", $newUsers);
			$xml->addChild("users", $newUsers);	
			$xmlfile[0]->set_content($xmltree->saveXML());
		}	

		if (is_object($workplanContainer) && $workplanContainer instanceof \steam_room) {
			// if user has the required rights display actionbar
			if ($user->get_id() == $workplanContainer->get_creator()->get_id() || in_array("WORKPLAN_" . $user->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
				$content = $workplanExtension->loadTemplate("workplan_ganttview.template.html");
				$content->setCurrentBlock("BLOCK_CONFIRMATION");
				$content->setVariable("CONFIRMATION_TEXT", "NONE");
				$content->parse("BLOCK_CONFIRMATION");
				
				$content->setCurrentBlock("BLOCK_WORKPLAN_GANTT_ACTIONBAR");
				$content->setVariable("LABEL_NEW_SNAPSHOT", "Snapshot erstellen");
				$content->setVariable("WORKPLAN_ID", $objectID);
				$content->setVariable("LABEL_NEW_MILESTONE", "Neuer Meilenstein");
				$content->setVariable("LABEL_NEW_TASK", "Neuer Vorgang");
				$content->parse("BLOCK_WORKPLAN_GANTT_ACTIONBAR");
				
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
			$tabBar->setActiveTab(2);
			$frameResponseObject->addWidget($tabBar);
			
			$xml = simplexml_load_string($xmlfile[0]->get_content());
			$helpToArray = $xml->children();
			$list = array();
			for ($counter = 0; $counter < count($helpToArray); $counter++) {
				array_push($list, $helpToArray[$counter]);
			}
			usort($list, 'sort_xmllist');
			
			$content = $workplanExtension->loadTemplate("workplan_ganttview.template.html");
			if (count($list) == 0) {
				$content->setCurrentBlock("BLOCK_WORKPLAN_GANTT_EMPTY");
				$content->setVariable("WORKPLAN_GANTT_EMPTY", "Keine Meilensteine oder Vorgänge zu diesem Projektplan vorhanden.");
				$content->parse("BLOCK_WORKPLAN_GANTT_EMPTY");
			}
			
			// change the format of the information so it can be displayed via javascript/jsgantt
			$oids = "[";
			$tasks = "[";
			$starts = "[";
			$ends = "[";
			$dependslist = "[";
			$milestones = "[";
			for ($counter = 0; $counter < count($list); $counter++) {
				$name = $list[$counter]->name;
				$tasks = $tasks . $name . ",";
				$starts = $starts . (int) $list[$counter]->start . ",";
				$ends = $ends . (int) $list[$counter]->end . ",";
				$oids = $oids . $list[$counter]->oid . ",";
				$depends = $list[$counter]->depends;
				if ($depends == -1) {
					$dependslist = $dependslist . "-1,";
				} else $dependslist = $dependslist . $depends . ",";
				if ($list[$counter]->getName() == "milestone") {
					$milestones = $milestones . "1,";
				} else {
					$milestones = $milestones . "0,";
				}
			}
			if (count($list) > 0) {
				$oids = substr($oids,0,strlen($oids)-1) . "]";
				$tasks = substr($tasks,0,strlen($tasks)-1) . "]";
				$starts = substr($starts,0,strlen($starts)-1) . "]";
				$ends = substr($ends,0,strlen($ends)-1) . "]";
				$dependslist = substr($dependslist,0,strlen($dependslist)-1) . "]";
				$milestones = substr($milestones,0,strlen($milestones)-1) . "]";
			} else {
				$oids = $oids . "]";
				$tasks = $tasks . "]";
				$starts = $starts . "]";
				$ends = $ends . "]";
				$dependslist = $dependslist . "]";
				$milestones = $milestones . "]";
			}
			
			$content->setCurrentBlock("BLOCK_GANTT_CHART");
			$content->setVariable("GANTT_DIV", "ganttchartdiv");
			$content->setVariable("WORKPLAN_GANTT_TASKS",$tasks);
			$content->setVariable("WORKPLAN_GANTT_OID",$oids);
			$content->setVariable("WORKPLAN_GANTT_MILESTONE",$milestones);
			$content->setVariable("WORKPLAN_GANTT_DEPENDS",$dependslist);
			$content->setVariable("WORKPLAN_GANTT_START",$starts);
			$content->setVariable("WORKPLAN_GANTT_END",$ends);
			$content->parse("BLOCK_GANTT_CHART");
			
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
			
			$groupID = 0;
			if (in_array("WORKPLAN_GROUP", $workplanContainer->get_attribute_names())) {
				$groupID = $workplanContainer->get_attribute("WORKPLAN_GROUP");
			}
			if ($groupID == 0) {
				$content->setCurrentBlock("BEGIN BLOCK_USER_OPTION_MILESTONE");
				$content->setVariable("USER_ID", $user->get_id());
				$content->setVariable("USER_NAME", $user->get_full_name());
				$content->parse("BLOCK_USER_OPTION_MILESTONE");
				
				$content->setCurrentBlock("BEGIN BLOCK_USER_OPTION_TASK");
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
					
					$content->setCurrentBlock("BEGIN BLOCK_USER_OPTION_MILESTONE");
					$content->setVariable("USER_ID", $currentMember->get_id());
					$content->setVariable("USER_NAME", $currentMember->get_full_name());
					$content->parse("BLOCK_USER_OPTION_MILESTONE");
					
					$content->setCurrentBlock("BEGIN BLOCK_USER_OPTION_TASK");
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
			
			$content->setVariable("LABEL_BACK","Ausblenden");
			$content->setVariable("LABEL_ADD","Hinzufügen");
			$content->setVariable("WORKPLAN_ID", $this->params[0]);
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
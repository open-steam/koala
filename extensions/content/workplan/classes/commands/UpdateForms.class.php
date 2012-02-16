<?php
namespace Workplan\Commands;
class UpdateForms extends \AbstractCommand implements \IAjaxCommand  {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$workplanExtension = \Workplan::getInstance();
		$content = $workplanExtension->loadTemplate("workplan_listview.template.html");
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$objectID = $this->params["id"];
		$workplanContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectID);
		$xmlfile = $workplanContainer->get_inventory_filtered(array(array("+", "class", CLASS_DOCUMENT)));
		
		// load elements of the workplan for displaying options in update and create dialogs
		$xml = simplexml_load_string($xmlfile[0]->get_content());
		$helpToArray = $xml->children();
		$list = array();
		for ($counter = 0; $counter < count($helpToArray); $counter++) {
			array_push($list, $helpToArray[$counter]);
		}
		usort($list, 'sort_xmllist');
		
		// create update dialogs
		$content->setCurrentBlock("BLOCK_WORKPLAN_UPDATE_DIALOG");
		$content->setVariable("WORKPLAN_LIST_UPDATE_OID", $objectID);
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
		$content->setVariable("UPDATE_USERS_LABEL", "Mitarbeiter:");
		$groupID = 0;
		if (in_array("WORKPLAN_GROUP", $workplanContainer->get_attribute_names())) {
			$groupID = $workplanContainer->get_attribute("WORKPLAN_GROUP");
		}
		// create user options in update dialogs
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
		$content->setVariable("WORKPLAN_UPDATE_ID", $objectID);
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
			$content->setCurrentBlock("BEGIN BLOCK_USER_OPTION_MILESTONE");
			$content->setVariable("USER_ID", $user->get_id());
			$content->setVariable("USER_NAME", $user->get_full_name());
			$content->parse("BLOCK_USER_OPTION_MILESTONE");
			
			$content->setCurrentBlock("BEGIN BLOCK_USER_OPTION_TASK");
			if (in_array("WORKPLAN_" . $user->get_id() . "_RESSOURCE", $workplanContainer->get_attribute_names())) {
				$content->setVariable("USER_RESSOURCE", $workplanContainer->get_attribute("WORKPLAN_" . $user->get_id() . "_RESSOURCE"));
			} else {
				$content->setVariable("USER_RESSOURCE", 0);
			}
			$content->setVariable("USER_ID", $user->get_id());
			$content->setVariable("USER_NAME", $user->get_full_name());
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
				if (in_array("WORKPLAN_" . $currentMember->get_id() . "_RESSOURCE", $workplanContainer->get_attribute_names())) {
					$content->setVariable("USER_RESSOURCE", $currentMember->get_attribute("WORKPLAN_" . $objectID . "_RESSOURCE"));
				} else {
					$content->setVariable("USER_RESSOURCE", 0);
				}
				$content->setVariable("USER_ID", $currentMember->get_id());
				$content->setVariable("USER_NAME", $currentMember->get_full_name());
				$content->parse("BLOCK_USER_OPTION_TASK");
			}
		}
			
		$content->setVariable("LABEL_BACK","Ausblenden");
		$content->setVariable("LABEL_ADD","Hinzufügen");
		$content->setVariable("WORKPLAN_ID", $objectID);
		$content->parse("BLOCK_WORKPLAN_LIST_FORMULAR");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($rawWidget);
		return $ajaxResponseObject;
	}
}
?>
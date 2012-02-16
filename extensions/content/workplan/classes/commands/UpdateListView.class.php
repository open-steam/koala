<?php
namespace Workplan\Commands;
class UpdateListView extends \AbstractCommand implements \IAjaxCommand  {
	
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
		
		$snapshot = $this->params["snapshot"];
		$update = $this->params["update"];
		if ($user->get_id() != $workplanContainer->get_creator()->get_id() && !in_array("WORKPLAN_" . $user->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
			$update = -1;
		}
		// load old version if snapshot is requested
		if ($snapshot != 0) {
			if ($snapshot == $xmlfile[0]->get_version() || $snapshot == -1) {
				$snapshot = -1;
			} else {
				$previousversions = $xmlfile[0]->get_previous_versions();
				$xmlfile[0] = $previousversions[count($previousversions) - $snapshot];
				$snapshot = -1;
			}
		// if user submitted the update dialog
		} else if ($update == 1) {
			$xmltree = new \SimpleXMLElement($xmlfile[0]->get_content());
			$children = $xmltree->children();
			$changeID = $this->params["changeID"];
			$changeObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $changeID);
			// search for changeObject in xml tree
			for ($count = 0; $count < count($children); $count++) {
				$currentElementID = $children[$count]->oid;
				if ((int) $currentElementID == (int) $changeID) {
					$changeElement = $children[$count];
					break;
				}
			}
			// save changes in xml file and attributes of the container
			$changeElement->name = $this->params["name"];
			$changeObject->set_name($this->params["name"]);
			$start = $this->params["start"];
			$start = mktime(0,0,0,substr($start,3,2),substr($start,0,2),substr($start,6,4));
			$changeElement->start = $start;
			$changeObject->set_attribute("WORKPLAN_START", $start);
			$end = $this->params["end"];
			$end = mktime(0,0,0,substr($end,3,2),substr($end,0,2),substr($end,6,4));
			$changeElement->end = $end;
			$changeObject->set_attribute("WORKPLAN_END", $end);
			if (strlen($this->params["duration"]) > 0) {
				$changeElement->duration = $this->params["duration"];
				$changeObject->set_attribute("WORKPLAN_DURATION", $this->params["duration"]);
			} else {
				$changeElement->duration = -1;
				$changeObject->set_attribute("WORKPLAN_DURATION", -1);
			}
			if (strlen($this->params["depends"]) > 0) {
				$changeElement->depends = $this->params["depends"];
				$changeObject->set_attribute("WORKPLAN_DEPENDS", $this->params["depends"]);
			} else {
				$changeElement->depends = -1;
				$changeObject->set_attribute("WORKPLAN_DEPENDS", -1);
			}
			$changeElement->users = $this->params["users"];
			$changeObject->set_attribute("WORKPLAN_USERS", $this->params["users"]);
			$xmlfile[0]->set_content($xmltree->saveXML());
		// if user deleted something delete it in xml file and on the steam server
		} else if ($update == 2) {
			$mot = $this->params["mot"];
			$deleteID = $this->params["elementid"];
			$xmltree = new \SimpleXMLElement($xmlfile[0]->get_content());
			$children = $xmltree->children();
			$tasks = 0;
			$milestones = 0;
			for ($count = 0; $count < count($children); $count++) {
				$currentElementID = $children[$count]->oid;
				if ((int) $currentElementID == (int) $deleteID) {
					break;
				}
				if ($children[$count]->getName() == 'task') {
					$tasks++;
				} else {
					$milestones++;
				}
			}
			if ($mot == 1) {
				unset($xmltree->milestone[$milestones]);
			} else {
				unset($xmltree->task[$tasks]);
			}
			for ($count = 0; $count < count($children); $count++) {
				if ($children[$count]->depends == (int) $deleteID) {
					$children[$count]->depends = -1;
					$dependelement = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $children[$count]->oid);
					$dependelement->set_attribute("WORKPLAN_DEPENDS", -1);
				}
			}
			$xmlfile[0]->set_content($xmltree->saveXML());
			$deleteElement = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $deleteID);
			$deleteElement->delete();
		// if user created a new milestone or task
		} else if ($update == 0) {
			$xmltree = new \SimpleXMLElement($xmlfile[0]->get_content());
			if ($this->params["mot"] == 0) {
				$xml = $xmltree->addChild("milestone");
			} else {
				$xml = $xmltree->addChild("task");
			}
			$newContainer = \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), $this->params["name"], $workplanContainer);
			$xml->addChild("name", $this->params["name"]);
			$xml->addChild("oid", $newContainer->get_id());
			$start = $this->params["start"];
			$start = mktime(0,0,0,substr($start,3,2),substr($start,0,2),substr($start,6,4));
			$newContainer->set_attribute("WORKPLAN_START", $start);
			$xml->addChild("start", $start);
			$end = $this->params["end"];
			$end = mktime(0,0,0,substr($end,3,2),substr($end,0,2),substr($end,6,4));
			$newContainer->set_attribute("WORKPLAN_END", $end);
			$xml->addChild("end", $end);
			if (strlen($this->params["duration"]) > 0) {
				$xml->addChild("duration", $this->params["duration"]);
				$newContainer->set_attribute("WORKPLAN_DURATION", $this->params["duration"]);
			} else {
				$xml->addChild("duration", -1);
				$newContainer->set_attribute("WORKPLAN_DURATION", -1);
			}
			if (strlen($this->params["depends"]) > 0) {
				$xml->addChild("depends", $this->params["depends"]);
				$newContainer->set_attribute("WORKPLAN_DEPENDS", $this->params["depends"]);
			} else {
				$xml->addChild("depends", -1);
				$newContainer->set_attribute("WORKPLAN_DEPENDS", -1);
			}
			$newContainer->set_attribute("WORKPLAN_USERS", $this->params["users"]);
			$xml->addChild("users", $this->params["users"]);	
			$xmlfile[0]->set_content($xmltree->saveXML());
		}
		
		// display updated list view of current workplan
		$xml = simplexml_load_string($xmlfile[0]->get_content());
		$helpToArray = $xml->children();
		$list = array();
		for ($counter = 0; $counter < count($helpToArray); $counter++) {
			array_push($list, $helpToArray[$counter]);
		}
		usort($list, 'sort_xmllist');
			
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
				$content->setCurrentBlock("BLOCK_WORKPLAN_LIST_ELEMENT");
				$content->setVariable("WORKPLAN_LIST_ELEMENT_NUMBER", $counter+1);
				if ($snapshot == -1) {
					$content->setVariable("WORKPLAN_LIST_ELEMENT_NAME_VALUE", $list[$counter]->name);
				} else {
					$element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $list[$counter]->oid);
					$elinventory = $element->get_inventory();
					$content->setVariable("WORKPLAN_LIST_ELEMENT_NAME_VALUE", $list[$counter]->name . " (" . count($elinventory) . ")");
				}
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
				$content->setVariable("WORKPLAN_LIST_ELEMENT_CHANGE_VALUE", "Bearbeiten");
				$content->setVariable("WORKPLAN_LIST_ELEMENT_DELETE_VALUE", "Löschen");
				if ($snapshot != -1 && ($user->get_id() == $workplanContainer->get_creator()->get_id() || in_array("WORKPLAN_" . $user->get_id() . "_LEADER", $workplanContainer->get_attribute_names()))) {
					$content->setVariable("WORKPLAN_RIGHTS", "");
				} else {
					$content->setVariable("WORKPLAN_RIGHTS", "none");
				}
				$content->parse("BLOCK_WORKPLAN_LIST_ELEMENT");
			}
			$content->parse("BLOCK_WORKPLAN_LIST");
		}	
			
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($rawWidget);
		return $ajaxResponseObject;
	}
}
?>
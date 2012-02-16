<?php
namespace Workplan\Commands;
class Overview extends \AbstractCommand implements \IFrameCommand {
	
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
		$newWorkplan = FALSE;
		
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_workplan"])) {
			$newWorkplan = TRUE;
			$values = $_POST["values"];
			$workplanContainer = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $values["name"], $user->get_workroom());
			$workplanContainer->set_attribute("OBJ_TYPE", "WORKPLAN_CONTAINER");
			$xml = new \SimpleXMLElement("<workplan></workplan>");
			$xml->addAttribute("name", $values["name"]);
			$start = $values["start"];
			$start = mktime(0,0,0,substr($start,3,2),substr($start,0,2),substr($start,6,4));
			$workplanContainer->set_attribute("WORKPLAN_START", $start);
			$xml->addAttribute("start", $start);
			if (!empty($values["end"])) {
				$end = $values["end"];
				$end = mktime(0,0,0,substr($end,3,2),substr($end,0,2),substr($end,6,4));
				$workplanContainer->set_attribute("WORKPLAN_END", $end);
				$xml->addAttribute("end", $end);
			} else {
				$workplanContainer->set_attribute("WORKPLAN_END", -1);
				$xml->addAttribute("end", -1);
			}
			if (!empty($values["description"])) {
				$workplanContainer->set_attribute("WORKPLAN_DESCRIPTION", $values["description"]);
				$xml->addAttribute("description", $values["description"]);
			} else {
				$workplanContainer->set_attribute("WORKPLAN_DESCRIPTION", "");
				$xml->addAttribute("description", "");
			}
			\steam_factory::create_document($GLOBALS["STEAM"]->get_id(), "version.xml", $xml->saveXML(), "text/xml", $workplanContainer);
			$portal->set_confirmation("Projektplan " . $values["name"] . " erfolgreich erstellt.");
		} 
		
		if (!$newWorkplan) {
			$workplanContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params[0]);
		}
		
		if (is_object($workplanContainer) && $workplanContainer instanceof \steam_room) {
			$content = $workplanExtension->loadTemplate("workplan_overview.template.html");
			if (!$newWorkplan) {
				$content->setCurrentBlock("BLOCK_CONFIRMATION");
				$content->setVariable("CONFIRMATION_TEXT", "NONE");
				$content->parse("BLOCK_CONFIRMATION");
			}
			
			// if current user has required rights display actionbar
			if($workplanContainer->get_creator()->get_id() == $user->get_id() || in_array("WORKPLAN_" . $user->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
				$content->setCurrentBlock("BLOCK_WORKPLAN_OVERVIEW_ACTIONBAR");
				$content->setVariable("LABEL_CHANGE", "Eigenschaften bearbeiten");
				$content->setVariable("LABEL_SNAPSHOT", "Snapshot erstellen");
				$content->setVariable("WORKPLAN_ID", $workplanContainer->get_id());
				if ($workplanContainer->get_creator()->get_id() == $user->get_id()) {
					$content->setVariable("WORKPLAN_RIGHTS_CHANGE", "");
				} else {
					$content->setVariable("WORKPLAN_RIGHTS_CHANGE", "none");
				}
				$content->parse("BLOCK_WORKPLAN_OVERVIEW_ACTIONBAR");
			}
			$actionBar = new \Widgets\RawHtml();
			$actionBar->setHtml($content->get());
			$frameResponseObject->addWidget($actionBar);
			
			$tabBar = new \Widgets\TabBar();
			$tabBar->setTabs(array(
				array("name"=>"Überblick", "link"=>$this->getExtension()->getExtensionUrl() . "overview/" . $workplanContainer->get_id()), 
				array("name"=>"Tabelle", "link"=>$this->getExtension()->getExtensionUrl() . "listView/" . $workplanContainer->get_id()), 
				array("name"=>"Gantt-Diagramm", "link"=>$this->getExtension()->getExtensionUrl() . "ganttView/" . $workplanContainer->get_id()), 
				array("name"=>"Mitarbeiter", "link"=>$this->getExtension()->getExtensionUrl() . "users/" . $workplanContainer->get_id()), 
				array("name"=>"Snapshots", "link"=>$this->getExtension()->getExtensionUrl() . "snapshots/" . $workplanContainer->get_id())));
			$tabBar->setActiveTab(0);
			$frameResponseObject->addWidget($tabBar);
			
			$content = $workplanExtension->loadTemplate("workplan_overview.template.html");
			if (isset($_POST["edit"])) {
				$edit = $_POST["edit"];
			} else $edit = 0;
			// if the user clicked on the edit symbol in Index-Command-View display edit view
			if ($edit == 1) {
				$content->setCurrentBlock("BLOCK_WORKPLAN_OVERVIEW_TABLE_EDIT");
				$content->setVariable("WORKPLAN_OVERVIEW_EDIT", "Eigenschaften bearbeiten");
				$content->setVariable("NAME_LABEL", "Projektname:*");
				$content->setVariable("START_LABEL", "Beginn:*");
				$content->setVariable("END_LABEL", "Ende:");
				$content->setVariable("CREATOR_LABEL", "Projektersteller:");
				$content->setVariable("DESCRIPTION_LABEL", "Beschreibung:");
				$content->setVariable("NAME_VALUE", $workplanContainer->get_name());
				$content->setVariable("START_VALUE", date("d.m.Y", (int) $workplanContainer->get_attribute("WORKPLAN_START")));
				$content->setVariable("CREATOR_VALUE", $workplanContainer->get_creator()->get_full_name());
				if ($workplanContainer->get_attribute("WORKPLAN_END") != -1) {
					$content->setVariable("END_VALUE", date("d.m.Y", (int) $workplanContainer->get_attribute("WORKPLAN_END")));
				}
				if (in_array("WORKPLAN_DESCRIPTION", $workplanContainer->get_attribute_names())) {
					$content->setVariable("DESCRIPTION_VALUE", $workplanContainer->get_attribute("WORKPLAN_DESCRIPTION"));
				}
				$content->setVariable("LABEL_SAVE", "Speichern");
				$content->setVariable("LABEL_BACK", "Abbrechen");
				$content->setVariable("WORKPLAN_ID", $workplanContainer->get_id());
				$content->parse("BLOCK_WORKPLAN_OVERVIEW_TABLE_EDIT");
			// else display normal view
			} else {
				$content->setCurrentBlock("BLOCK_WORKPLAN_OVERVIEW_TABLE");
				$content->setVariable("WORKPLAN_OVERVIEW_ATTRIBUTE", "Eigenschaft");
				$content->setVariable("WORKPLAN_OVERVIEW_VALUE", "Wert");
				$content->setVariable("NAME_LABEL", "Projektname");
				$content->setVariable("START_LABEL", "Beginn");
				$content->setVariable("END_LABEL", "Ende");
				$content->setVariable("CREATOR_LABEL", "Projektersteller");
				$content->setVariable("DESCRIPTION_LABEL", "Beschreibung");
				$content->setVariable("NAME_VALUE", $workplanContainer->get_name());
				$content->setVariable("START_VALUE", date("d.m.Y", (int) $workplanContainer->get_attribute("WORKPLAN_START")));
				$content->setVariable("CREATOR_VALUE", $workplanContainer->get_creator()->get_full_name());
				if ($workplanContainer->get_attribute("WORKPLAN_END") != -1) {
					$content->setVariable("END_VALUE", date("d.m.Y", (int) $workplanContainer->get_attribute("WORKPLAN_END")));
				} else {
					$content->setVariable("END_VALUE", "-");
				}
				if (in_array("WORKPLAN_DESCRIPTION", $workplanContainer->get_attribute_names())) {
					if (strlen(trim($workplanContainer->get_attribute("WORKPLAN_DESCRIPTION"))) > 0) {
						$content->setVariable("DESCRIPTION_VALUE", nl2br($workplanContainer->get_attribute("WORKPLAN_DESCRIPTION")));
					} else {
						$content->setVariable("DESCRIPTION_VALUE", "-");
					}
				} else {
					$content->setVariable("DESCRIPTION_VALUE", "-");
				}
				$content->parse("BLOCK_WORKPLAN_OVERVIEW_TABLE");
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
<?php
namespace Workplan\Commands;
class Snapshots extends \AbstractCommand implements \IFrameCommand {
	
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
			
		if (is_object($workplanContainer) && $workplanContainer instanceof \steam_room) {
			$tabBar = new \Widgets\TabBar();
			$tabBar->setTabs(array(
				array("name"=>"Überblick", "link"=>$this->getExtension()->getExtensionUrl() . "overview/" . $objectID), 
				array("name"=>"Tabelle", "link"=>$this->getExtension()->getExtensionUrl() . "listView/" . $objectID), 
				array("name"=>"Gantt-Diagramm", "link"=>$this->getExtension()->getExtensionUrl() . "ganttView/" . $objectID), 
				array("name"=>"Mitarbeiter", "link"=>$this->getExtension()->getExtensionUrl() . "users/" . $objectID), 
				array("name"=>"Snapshots", "link"=>$this->getExtension()->getExtensionUrl() . "snapshots/" . $objectID)));
			$tabBar->setActiveTab(4);
			$frameResponseObject->addWidget($tabBar);
			
			$xmlfile = $workplanContainer->get_inventory_filtered(array(array("+", "class", CLASS_DOCUMENT)));
			// if user submitted new snapshot dialog add a now annotation/snapshot
			if ($_SERVER[ "REQUEST_METHOD" ] == "POST" && isset($_POST["new_snapshot"])) {
				$values = $_POST["values"];
				$newannotation = \steam_factory::create_textdoc($GLOBALS["STEAM"]->get_id(), $values["snapshotname"], strval($xmlfile[0]->get_version()), $workplanContainer);
				$xmlfile[0]->add_annotation($newannotation);
				$portal->set_confirmation("Snapshot " . $values["snapshotname"] . " erfolgreich erstellt.");
			}
			
			// display snapshot list
			$content = $workplanExtension->loadTemplate("workplan_snapshots.template.html");
			$snapshots = $xmlfile[0]->get_annotations();
			$howmany = count($snapshots);
			if ($howmany > 0) {
				$content->setCurrentBlock("BLOCK_SNAPSHOTS_LIST");
				$content->setVariable("NAME_LABEL", "Name");
				$content->setVariable("DATE_LABEL", "Datum");
				$content->setVariable("VIEW_LABEL", "Ansicht");
				for ($count = count($snapshots)-1; $count >= 0; $count--) {
					$content->setCurrentBlock("BLOCK_SNAPSHOTS_LIST_ELEMENT");
					$content->setVariable("ELEMENT_NAME", $snapshots[$count]->get_name());
					$timestamp = $snapshots[$count]->get_attribute("OBJ_CREATION_TIME");
					$content->setVariable("ELEMENT_DATE", strftime( "%x %X", $timestamp ));
					$content->setVariable("ELEMENT_LIST", "Tabelle");
					$content->setVariable("ELEMENT_GANTT", "Gantt-Diagramm");
					$content->setVariable("WORKPLAN_VERSION", $snapshots[$count]->get_content());
					$content->setVariable("WORKPLAN_ID", $objectID);
					$content->setVariable("ANNOTATION_ID", $count);
					$version = $snapshots[$count]->get_content();
					if ($version == $xmlfile[0]->get_version()) {
						$viewversion = $xmlfile[0];
					} else {
						$previousversions = $xmlfile[0]->get_previous_versions();
						$viewversion = $previousversions[count($previousversions) - $version];
					}
					// change information of every snapshot to a form which is used to create the gantt view
					$xml = simplexml_load_string($viewversion->get_content());
					$helpToArray = $xml->children();
					$list = array();
					for ($counter = 0; $counter < count($helpToArray); $counter++) {
						array_push($list, $helpToArray[$counter]);
					}
					usort($list, 'sort_xmllist');
					$oids = "";
					$names = "";
					$start = "";
					$end = "";
					$depends = "";
					$milestones = "";
					for ($count2 = 0; $count2 < count($list); $count2++) {
						$oids = $oids . $list[$count2]->oid . ",";
						$names = $names . $list[$count2]->name . ",";
						$start = $start . $list[$count2]->start . ",";
						$end = $end . $list[$count2]->end . ",";
						$depends = $depends . $list[$count2]->depends . ",";
						if ($list[$count2]->getName() == 'milestone') {
							$milestones = $milestones .  "1,"; 
						} else {
							$milestones = $milestones .  "0,"; 
						}
					}
					$oids = "[" . substr($oids,0,strlen($oids)-1) . "]";
					$names = "[" . substr($names,0,strlen($names)-1) . "]";
					$start = "[" . substr($start,0,strlen($start)-1) . "]";
					$end = "[" . substr($end,0,strlen($end)-1) . "]";
					$depends = "[" . substr($depends,0,strlen($depends)-1) . "]";
					$milestones = "[" . substr($milestones,0,strlen($milestones)-1) . "]";
					
					$content->setVariable("WORKPLAN_OIDS", $oids);
					$content->setVariable("WORKPLAN_NAMES", $names);
					$content->setVariable("WORKPLAN_STARTS", $start);
					$content->setVariable("WORKPLAN_ENDS", $end);
					$content->setVariable("WORKPLAN_DEPENDS", $depends);
					$content->setVariable("WORKPLAN_MILESTONES", $milestones);
					
					$content->parse("BLOCK_SNAPSHOTS_LIST_ELEMENT");
				}
				$content->parse("BLOCK_SNAPSHOTS_LIST");
			} else {
				$content->setCurrentBlock("BLOCK_SNAPSHOTS_NO_LIST");
				$content->setVariable("NO_SNAPSHOTS", "Keine Snapshots zu diesem Projektplan vorhanden.");
				$content->parse("BLOCK_SNAPSHOTS_NO_LIST");
			}
			
			// if current user has the required rights display create snapshot dialog
			$content->setCurrentBlock("BLOCK_NEW_SNAPSHOT");
			$content->setVariable("WORKPLAN_ID", $objectID);
			$content->setVariable("LABEL_NEW_SNAPSHOT", "Neuen Snapshot erstellen");
			$content->setVariable("LABEL_NAME", "Name:*");
			$content->setVariable("LABEL_CREATE", "Abschicken");
			$content->setVariable("WORKPLAN_SHOW_CURRENTVERSION", "Aktuelle Version des Projektplans einblenden");
			$content->setVariable("WORKPLAN_HIDE_SNAPSHOT", "Snapshot ausblenden");
			$content->setVariable("WORKPLAN_HIDECURRENT_SNAPSHOT", "Aktuelle Version des Projektplans ausblenden");
			if ($user->get_id() == $workplanContainer->get_creator()->get_id() || in_array("WORKPLAN_" . $user->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
				$content->setVariable("SNAPSHOT_RIGHTS", "");
			} else {
				$content->setVariable("SNAPSHOT_RIGHTS", "none");
			}
			$content->parse("BLOCK_NEW_SNAPSHOT");
			
			
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
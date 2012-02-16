<?php
namespace Workplan\Commands;
class UpdateOverview extends \AbstractCommand implements \IAjaxCommand  {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$workplanExtension = \Workplan::getInstance();
		$content = $workplanExtension->loadTemplate("workplan_overview.template.html");
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$objectID = $this->params["id"];
		$workplanContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectID);
		$change = $this->params["change"];
		
		if ($user->get_id() != $workplanContainer->get_creator()->get_id() && !in_array("WORKPLAN_" . $user->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
			$change = 0;
		}
		
		// if edit form got submitted save new data
		if ($change == 2) {
			$workplanContainer->set_name($this->params["name"]);
			$start = $this->params["start"];
			$start = mktime(0,0,0,substr($start,3,2),substr($start,0,2),substr($start,6,4));
			$workplanContainer->set_attribute("WORKPLAN_START", $start);
			$end = $this->params["end"];
			if (strlen($end) > 0) {
				$end = mktime(0,0,0,substr($end,3,2),substr($end,0,2),substr($end,6,4));
			} else $end = -1;
			$workplanContainer->set_attribute("WORKPLAN_END", $end);
			$workplanContainer->set_attribute("WORKPLAN_DESCRIPTION", $this->params["description"]);
			
			$xmlfile = $workplanContainer->get_inventory_filtered(array(array("+", "class", CLASS_DOCUMENT)));
			$xmltree = new \SimpleXMLElement($xmlfile[0]->get_content());
			$xmltree["name"] = $this->params["name"];
		   	$xmltree["start"] = $start;
		   	$xmltree["end"] = $end;
		   	$xmltree["description"] = $this->params["description"];
			$xmlfile[0]->set_content($xmltree->saveXML());
		// if edit button was clicked display edit view
		} else if ($change == 1) {
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
			$content->setVariable("WORKPLAN_ID", $objectID);
			$content->parse("BLOCK_WORKPLAN_OVERVIEW_TABLE_EDIT");
		}
		// if edit got cancelled or changes were saved display normal view
		if ($change != 1) {
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
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($rawWidget);
		return $ajaxResponseObject;
	}
}
?>
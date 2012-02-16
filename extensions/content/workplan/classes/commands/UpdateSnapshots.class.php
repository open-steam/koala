<?php
namespace Workplan\Commands;
class UpdateSnapshots extends \AbstractCommand implements \IAjaxCommand  {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$workplanExtension = \Workplan::getInstance();
		$objectID = $this->params["id"];
		$workplanContainer = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectID);
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$xmlfile = $workplanContainer->get_inventory_filtered(array(array("+", "class", CLASS_DOCUMENT)));
		$version = $this->params["version"];
		
		// if user submitted create snapshot dialog create the new annotation/snapshot
		$newsnapshot = 0;
		if (isset($this->params["newsnapshot"])) {
			$newsnapshot = $this->params["newsnapshot"];
		}
		if ($user->get_id() != $workplanContainer->get_creator()->get_id() && !in_array("WORKPLAN_" . $user->get_id() . "_LEADER", $workplanContainer->get_attribute_names())) {
			$newsnapshot = 0;
		}
		if ($newsnapshot == 1) {
			$newannotation = \steam_factory::create_textdoc($GLOBALS["STEAM"]->get_id(), $this->params["name"], strval($xmlfile[0]->get_version()), $workplanContainer);
			$xmlfile[0]->add_annotation($newannotation);
			
			$rawWidget = new \Widgets\RawHtml();
			$ajaxResponseObject->setStatus("ok");
			$ajaxResponseObject->addWidget($rawWidget);
			return $ajaxResponseObject;
		}
		
		// load the right version
		$previousversions = $xmlfile[0]->get_previous_versions();
		if (($version == 0) || ($version == count($previousversions)+1) || ($version == 1)) {
			$xml = simplexml_load_string($xmlfile[0]->get_content());
		} else {;
			$thisversion = $previousversions[count($previousversions) - $version];
			$xml = simplexml_load_string($thisversion->get_content());
		}
		$annotations = $xmlfile[0]->get_annotations();
		
		// display general informations of a workplan (if version == 0 its the current one)
		$content = $workplanExtension->loadTemplate("workplan_snapshots.template.html");
		$content->setCurrentBlock("BEGIN BLOCK_SNAPSHOT_VIEW");
		if ($version != 0) {
			$annotation = $annotations[$this->params["annotationid"]];
			$timestamp = $annotation->get_attribute("OBJ_CREATION_TIME");
			$content->setVariable("SNAPSHOT_VERSION", "Snapshot " . $annotation->get_name() . " vom " . strftime( "%x", (int) $timestamp ));
		} else {
			$content->setVariable("SNAPSHOT_VERSION", "Aktueller Projektplan");
		}
		$content->setVariable("SNAPSHOT_VERSION_NAME", "Name");
		$content->setVariable("SNAPSHOT_VERSION_NAME_VALUE", $xml["name"]);
		$content->setVariable("SNAPSHOT_VERSION_START", "Beginn");
		$content->setVariable("SNAPSHOT_VERSION_START_VALUE", strftime( "%x", (int) $xml["start"]));
		$content->setVariable("SNAPSHOT_VERSION_END", "Ende");
		if ($xml["end"] != -1) {
			$content->setVariable("SNAPSHOT_VERSION_END_VALUE", strftime( "%x", (int) $xml["end"]));
		} else {
			$content->setVariable("SNAPSHOT_VERSION_END_VALUE", "-");
		}
		$content->setVariable("SNAPSHOT_VERSION_DESCRIPTION", "Beschreibung");
		if (strlen($xml["description"]) > 0) {
			$content->setVariable("SNAPSHOT_VERSION_DESCRIPTION_VALUE", nl2br($xml["description"]));
		} else {
			$content->setVariable("SNAPSHOT_VERSION_DESCRIPTION_VALUE", "-");
		}
		$content->parse("BEGIN BLOCK_SNAPSHOT_VIEW");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($rawWidget);
		return $ajaxResponseObject;
	}
}
?>
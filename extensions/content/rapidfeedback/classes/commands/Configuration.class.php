<?php
namespace Rapidfeedback\Commands;
class Configuration extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$rapidfeedback = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$RapidfeedbackExtension = \Rapidfeedback::getInstance();
		$RapidfeedbackExtension->addCSS();
		
		$content = $RapidfeedbackExtension->loadTemplate("rapidfeedback_configuration.template.html");
		$content->setCurrentBlock("BLOCK_CONFIGURATION_TABLE");
		$content->setVariable("RAPIDFEEDBACK_OPTIONS", "Konfiguration");
		$content->setVariable("TITLE_LABEL", "Titel:*");
		$content->setVariable("TITLE_VALUE", $rapidfeedback->get_name());
		$content->setVariable("DESC_LABEL", "Beschreibung:");
		if ($rapidfeedback->get_attribute("OBJ_DESC") != "0") {
			$content->setVariable("DESC_VALUE", $rapidfeedback->get_attribute("OBJ_DESC"));
		}
		$content->setVariable("ADMINSURVEY_LABEL", "Administratoren können auch an den Umfragen teilnehmen");
		if ($rapidfeedback->get_attribute("RAPIDFEEDBACK_ADMIN_SURVEY") == 1) {
			$content->setVariable("ADMINSURVEY_CHECKED", "checked");
		}
		$content->setVariable("BACK_LABEL", "Zurück");
		$content->setVariable("BACK_URL", $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $this->id);
		$content->setVariable("EDIT_RAPIDFEEDBACK", "Änderungen speichern");
		$content->parse("BLOCK_CONFIGURATION_TABLE");
		
		$group = $rapidfeedback->get_attribute("RAPIDFEEDBACK_GROUP");
		if ($group->get_name() == "learners") {
			$parent = $group->get_parent_group();
			$courseOrGroup = "Kurs: " . $parent->get_attribute("OBJ_DESC") . " (" . $parent->get_name() . ")";
			$courseOrGroupUrl = PATH_URL . "semester/" . $parent->get_id();
		} else {
			$courseOrGroup = "Gruppe: " . $group->get_name();
			$courseOrGroupUrl = PATH_URL . "groups/" . $group->get_id();
		}
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
			array("name" => "Rapid Feedback", "link" => $RapidfeedbackExtension->getExtensionUrl() . "Index/" . $this->id),
			array("name" => "Konfiguration")
		));
		return $frameResponseObject;
	}
}
?>
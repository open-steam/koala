<?php
namespace Workplan\Commands;
class Debug extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$testObject = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), "Test Workflow2", $user->get_workroom());
		$testObject->set_attribute("OBJ_TYPE", "WORKPLAN_CONTAINER");
		
		$workplanExtension = \Workplan::getInstance();
		$workplanExtension->addJS();
		$content = $workplanExtension->loadTemplate("workplan_create.template.html");
			
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml("Test container wurde erstellt!");
		$frameResponseObject->setTitle("Projektplan erstellen");
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
}
?>
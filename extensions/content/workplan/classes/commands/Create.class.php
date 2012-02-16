<?php
namespace Workplan\Commands;
class Create extends \AbstractCommand implements \IFrameCommand {
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$workplanExtension = \Workplan::getInstance();
		$workplanExtension->addJS();
		$content = $workplanExtension->loadTemplate("workplan_create.template.html");
		
		$content->setCurrentBlock("BLOCK_INFO");
		$content->setVariable("INFO_TEXT","Sie sind dabei einen neuen Projektplan zu erstellen.");
		$content->parse("BLOCK_INFO");

		$content->setCurrentBlock("BLOCK_CREATE_FORMULAR");
		$content->setVariable("NAME_LABEL","Projektname:*");
		$content->setVariable("START_DATE_LABEL","Beginn:*");
		$content->setVariable("END_DATE_LABEL","Ende:");
		$content->setVariable("DESCRIPTION_LABEL","Beschreibung:");
		$content->setVariable("CREATE_WORKPLAN_LABEL","Projektplan erstellen");
		$content->setVariable("LABEL_BACK","Zurück");
		$content->setVariable("WORKPLAN_LINK_BACK", $this->getExtension()->getExtensionUrl());
		$content->parse("BLOCK_CREATE_FORMULAR");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->setTitle("Projektplan erstellen");
		$frameResponseObject->setHeadline(array(
			array("link"=>$this->getExtension()->getExtensionUrl(), "name"=>"Projektplanverwaltung"),
			array("", "name"=>"Neuer Projektplan")));
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
}
?>
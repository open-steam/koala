<?php
namespace PortletAppointment\Commands;
class Edit extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["portletId"];
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Bearbeiten von " . $object->get_attribute("OBJ_DESC"));
		
		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Überschrift");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget(new \Widgets\Clearer());
		
		$align =  new \Widgets\ComboBox();
		$align->setLabel("Sortierung");
		
		$align->setOptions(array(
							array("name"=>"Frühe Termine zuerst anzeigen", "value"=>"earliest_first"),
							array("name"=>"Späte Termine zuerst anzeigen", "value"=>"latest_first")
								));
		$align->setData($object);
		$align->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:app:app_order"));
		$dialog->addWidget($align);
		//$dialog->setForceReload(true);
		
		$this->dialog = $dialog;
	}
	
	public function idResponse(\IdResponseObject $idResponseObject) {
		$idResponseObject->setContent($this->content);
		return $idResponseObject;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$frameResponseObject->setTitle("Portal");
		$frameResponseObject->setContent($this->content);
		return $frameResponseObject;
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>
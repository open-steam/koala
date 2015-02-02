<?php
namespace PortletChronic\Commands;

class Edit extends \AbstractCommand implements \IAjaxCommand {
	
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
		$titelInput->setLabel("Anzahl an Objekten");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_CHRONIC_COUNT"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget(new \Widgets\Clearer());
			
		
		$this->dialog = $dialog;
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>
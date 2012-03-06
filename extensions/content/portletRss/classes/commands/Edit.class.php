<?php
namespace PortletRss\Commands;
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

		$clearer = new \Widgets\Clearer();
		
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Bearbeiten von " . $object->get_attribute("OBJ_DESC"));
		
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		
		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Überschrift");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		
		$addressInput = new \Widgets\TextInput();
		$addressInput->setLabel("RSS-Adresse");
		$addressInput->setData($object);
		$addressInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([address])"));
		
		$countInput = new \Widgets\TextInput();
		$countInput->setLabel("Anzahl Beiträge");
		$countInput->setData($object);
		$countInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([num_items])"));
		
		$lengthInput = new \Widgets\TextInput();
		$lengthInput->setLabel("Länge des Inhalts");
		$lengthInput->setData($object);
		$lengthInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([desc_length])"));
		
		$checkbox = new \Widgets\Checkbox();
		$checkbox->setLabel("HTML zulassen");
		$checkbox->setData($object);
		$checkbox->setCheckedValue("checked");
		$checkbox->setUncheckedValue("");
		$checkbox->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([allow_html])"));
		
		$dialog->addWidget($titelInput);
		$dialog->addWidget($clearer);
		$dialog->addWidget($addressInput);
		$dialog->addWidget($clearer);
		$dialog->addWidget($countInput);
		$dialog->addWidget($clearer);
		$dialog->addWidget($lengthInput);
		$dialog->addWidget($clearer);
		$dialog->addWidget($checkbox);
                $dialog->setForceReload(true);
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
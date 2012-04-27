<?php
namespace PortletMedia\Commands;
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
		
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		
		$clearer = new \Widgets\Clearer();
		
		/* not used
		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Titel");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget($clearer);
		*/
		
		$headlineInput = new \Widgets\TextInput();
		$headlineInput->setLabel("Überschrift");
		$headlineInput->setData($object);
		$headlineInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([headline])"));
		$dialog->addWidget($headlineInput);
		$dialog->addWidget($clearer);
		
		$urlInput = new \Widgets\TextInput();
		$urlInput->setLabel("Adresse");
		$urlInput->setData($object);
		$urlInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([url])"));
		$dialog->addWidget($urlInput);
		$dialog->addWidget($clearer);
		
		$descriptionInput = new \Widgets\TextInput();
		$descriptionInput->setLabel("Beschreibung");
		$descriptionInput->setData($object);
		$descriptionInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([description])"));
		$dialog->addWidget($descriptionInput);
		$dialog->addWidget($clearer);
		
		/*
		$mediaTypeInput = new \Widgets\TextInput();
		$mediaTypeInput->setLabel("Typ"); //Film, Bild, Ton
		$mediaTypeInput->setData($object);
		$mediaTypeInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([media_type])"));
		$dialog->addWidget($mediaTypeInput);
		$dialog->addWidget($clearer);
		*/
		
		$radioButton = new \Widgets\RadioButton();
		$radioButton->setLabel("Typ");
		$radioButton->setOptions(array(array("name"=>"Film", "value"=>"movie"), array("name"=>"Bild", "value"=>"image"), array("name"=>"Ton", "value"=>"audio")));
		$radioButton->setData($object);
		$radioButton->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:content([media_type])"));
		$dialog->addWidget($radioButton);
		$dialog->addWidget($clearer);
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
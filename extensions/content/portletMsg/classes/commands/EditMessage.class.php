<?php
namespace PortletMsg\Commands;
class EditMessage extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["messageObjectId"];
		
		
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Meldung bearbeiten");
		
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		//$dialog->setWidth(450);
		$clearer = new \Widgets\Clearer();
		
		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Überschrift");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_NAME"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget($clearer);
		
		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Untertitel");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget($clearer);
		
		$contentText = new \Widgets\Textarea();
		$contentText->setLabel("Inhalt");
		$contentText->setTextareaClass("mce-small");
		$contentText->setWidth(480);
		$contentText->setData($object);
		$contentText->setContentProvider(\Widgets\DataProvider::contentProvider());
		$dialog->addWidget($contentText);
		$dialog->addWidget($clearer);
		$dialog->addWidget($clearer);
		
		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Link-Text");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:msg:link_url_label"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget($clearer);
		
		$titelInput = new \Widgets\TextInput();
		$titelInput->setLabel("Link-Adresse");
		$titelInput->setData($object);
		$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:msg:link_url"));
		$dialog->addWidget($titelInput);
		$dialog->addWidget($clearer);
		
		$widget = new \Widgets\Checkbox();
		$widget->setLabel("Link in einem neuen Fenster öffnen");
		$widget->setData($object);
		$widget->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portlet:msg:link_open"));
		$widget->setCheckedValue("checked");
		$widget->setUncheckedValue("");
		$dialog->addWidget($widget);
		
		//create widgets
		//$button1 = new \Widgets\Button();
		
		//TODO: add a edit box for the message here
		
		
		//$button1->setLabel("abschicken");
		
		//add widgets to dialog
		//$dialog->addWidget($button1);
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
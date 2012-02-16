<?php
/*
 * edit dialog for the whole messages portlet
 * for example to sort the messages
 */

namespace PortletMsg\Commands;
class EditMsgPortlet extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
	private $params;
	private $id;
	private $content;
	private $dialog;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject){
		$params = $requestObject->getParams();
		$objectId = $params["portletObjectId"];

		
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		//$dialog->setTitle("Eigenschaften von " . $object->get_name());
		$dialog->setTitle("Bearbeiten von Nachrichten " . $object->get_attribute("OBJ_DESC"));
		
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		
		//create widgets
		$list1 = new \Widgets\SelectList();
		$button1 = new \Widgets\Button();
		$button2 = new \Widgets\Button();
		
		
		//get messages
		$inventory = $object->get_inventory();
		foreach ($inventory as $message) {
			if($message->get_attribute("DOC_TYPE")!="") continue;
			$list1->addOption($message->get_id(), $message->get_name());
		}
		
		$button1->setLabel("neue Nachricht oben anfügen");
		$button2->setLabel("neue Nachricht unten anfügen");
		
		//add widgets to dialog
		$dialog->addWidget($list1);
		$dialog->addWidget($button1);
		$dialog->addWidget($button2);
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
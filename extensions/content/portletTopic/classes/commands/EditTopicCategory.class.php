<?php
namespace PortletTopic\Commands;

class EditTopicCategory extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
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
		$categoryIndex = $params["categoryIndex"];
		
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		//$dialog->setTitle("Eigenschaften von Kategorie: $categoryIndex in " . $object->get_name());
		$dialog->setTitle("Bearbeiten von Kategorie");
		
		
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);
		
		$clearer = new \Widgets\Clearer();
		$titel = new \Widgets\TextInput();
		
		$titel->setLabel("Titel");
		
		$titel->setData($object);
		//$titel->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));
		$titel->setContentProvider(new AttributeDataProviderPortletTopicCategory($categoryIndex));
		
		
		$dialog->addWidget($titel);
		$dialog->addWidget($clearer);
		
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
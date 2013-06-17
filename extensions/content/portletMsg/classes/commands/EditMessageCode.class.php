<?php
namespace PortletMsg\Commands;
class EditMessageCode extends \AbstractCommand implements \IFrameCommand, \IIdCommand, \IAjaxCommand {
	
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
		
		$contentText = new \Widgets\TextareaCode();
		$contentText->setWidth(480);
                $contentText->setRows(10);
		$contentText->setData($object);
		$contentText->setContentProvider(\Widgets\DataProvider::contentProvider());
                $dialog->addWidget($contentText);
		$dialog->addWidget($clearer);
                
             //   $dialog->setForceReload(true);
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
<?php
namespace PortalColumn\Commands;
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
		$dialog->setTitle("Breite von Spalte " . $object->get_attribute("OBJ_DESC"));

		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);

		$clearer = new \Widgets\Clearer();
		$sizeInput = new \Widgets\TextInput();
		$sizeInput->setLabel("Breite");
		$sizeInput->setData($object);
		$sizeInput->setType("number");
		$sizeInput->setMin(100);
		$sizeInput->setMax(900);
		$sizeInput->setContentProvider(\Widgets\DataProvider::attributeProvider("bid:portal:column:width"));

		$dialog->addWidget($sizeInput);
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

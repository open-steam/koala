<?php
namespace PortletBookmarks\Commands;
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

    //$titelInput = new \Widgets\TextInput();
		//$titelInput->setLabel("Überschrift");
		//$titelInput->setData($object);
		//$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));

		$numberInput = new \Widgets\TextInput();
		$numberInput->setLabel("Anzahl an Lesezeichen");
		$numberInput->setData($object);
		$numberInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_BOOKMARK_COUNT"));

		//$dialog->addWidget($titelInput);
		//$dialog->addWidget($clearer);
		$dialog->addWidget($numberInput);
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

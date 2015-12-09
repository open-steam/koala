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

                $clearer = new \Widgets\Clearer();

		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $objectId);
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Bearbeiten von " . $object->get_attribute("OBJ_DESC"));

    //$titelInput = new \Widgets\TextInput();
		//$titelInput->setLabel("Überschrift");
		//$titelInput->setData($object);
		//$titelInput->setContentProvider(\Widgets\DataProvider::attributeProvider("OBJ_DESC"));

		$numberInput = new \Widgets\TextInput();
		$numberInput->setLabel("Anzahl an Objekten");
		$numberInput->setData($object);
		$numberInput->setContentProvider(\Widgets\DataProvider::attributeProvider("PORTLET_CHRONIC_COUNT"));

    //$dialog->addWidget($titelInput);
    //$dialog->addWidget($clearer);
    $dialog->addWidget($numberInput);
    $dialog->addWidget($clearer);

		$this->dialog = $dialog;
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($this->dialog);
		return $ajaxResponseObject;
	}
}
?>

<?php
namespace Explorer\Commands;
class Reference extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $user;
	private $name;
	private $duplicateNameObject;
	private $rename;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->user = \lms_steam::get_current_user_no_guest();
		$this->rename = $this->params["rename"];
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$this->name = $object->get_name();
		$this->duplicateNameObject = $this->user->get_object_by_name($this->name);
		if($this->duplicateNameObject == 0 || $this->rename){
			$link = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object);
			$link->set_attributes(array(OBJ_DESC => $object->get_attribute(OBJ_DESC)));
			$link->move($this->user);
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");

		if($this->duplicateNameObject != 0  && !$this->rename){   //there exists an object with this name in the clipboard, ask the user what to do
			$dialog = new \Widgets\Dialog();
			$dialog->setTitle("Information");
			$dialog->setSaveAndCloseButtonLabel(null);
			$dialog->setCancelButtonLabel("Abbrechen");

			$rawHtml = new \Widgets\RawHtml();
			$rawHtml->setHtml('<div>In der Zwischenablage existiert bereits eine Referenz mit dem Namen "' . $this->name . '". Bitte wählen Sie eine der angegebenen Handlungsalternativen.</div><br style="clear:both"><div><div style="font-weight: bold; float: left;">Beide behalten:</div><div style="margin-left: 100px;"> Der Name der neuen Referenz wird zur eindeutigen Zuordnung durch eine Ziffer ergänzt.</div></div><div><div style="font-weight: bold; float: left;">Ersetzen:</div><div style="margin-left: 100px;">Die bestehende Referenz wird durch die neue Referenz ersetzt.</div></div><div><div style="font-weight: bold; float: left;">Abbrechen:</div><div style="margin-left: 100px;">Die Erstellung der Referenz wird abgebrochen.</div></div>');
			$dialog->addWidget($rawHtml);

			$jswrapper = new \Widgets\JSWrapper();
			$jswrapper->setJs('createOverlay("white", null, "show")');
			$ajaxResponseObject->addWidget($jswrapper);

			$keepBothButton = array();
			$keepBothButton["label"] = "Beide behalten";
			$keepBothButton["js"] = "sendRequest('Reference', {'id':{$this->id}, 'rename':true}, '', 'data', null, null, 'explorer');closeDialog();";
			$buttons[0] = $keepBothButton;

			$replaceButton = array();
			$replaceButton["label"] = "Ersetzen";
			$replaceButton["js"] = "sendRequest('Delete', {'id':{$this->duplicateNameObject->get_id()}}, '', 'data', function(){sendRequest('Reference', {'id':{$this->id}}, '', 'data', null, null, 'explorer');}, null, 'explorer');closeDialog();";
			$buttons[1] = $replaceButton;

			$dialog->setButtons($buttons);
			$dialog->setWidth(500);
			$ajaxResponseObject->addWidget($dialog);
		}
		else{
			$jswrapper = new \Widgets\JSWrapper();
			$clipboardModel = new \Explorer\Model\Clipboard($this->user);
			$js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';" ;
			$jswrapper->setJs($js);
			$ajaxResponseObject->addWidget($jswrapper);
		}
		return $ajaxResponseObject;
	}

}
?>

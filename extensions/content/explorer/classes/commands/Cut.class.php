<?php

namespace Explorer\Commands;

class Cut extends \AbstractCommand implements \IAjaxCommand {

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
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user();
		$this->rename = $this->params["rename"];
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$this->name = $object->get_name();
		$this->duplicateNameObject = $this->user->get_object_by_name($this->name);
		if($this->duplicateNameObject == 0 || $this->rename){
			$object->move($this->user);
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
			$rawHtml->setHtml($this->id . " " . (($this->rename) ? 'true' : 'false') . '<div>In der Zwischenablage existiert bereits ein Objekt mit dem Namen "' . $this->name . '". Bitte wählen Sie eine der angegebenen Handlungsalternativen.</div><br style="clear:both"><div><div style="font-weight: bold; float: left;">Beide behalten:</div><div style="margin-left: 100px;"> Der Name des neuen Objekts wird zur eindeutigen Zuordnung durch eine Ziffer ergänzt.</div></div><div><div style="font-weight: bold; float: left;">Ersetzen:</div><div style="margin-left: 100px;">Das bestehende Objekt wird durch das neue Objekt ersetzt.</div></div><div><div style="font-weight: bold; float: left;">Abbrechen:</div><div style="margin-left: 100px;">Das Ausschneiden wird abgebrochen.</div></div>');
			$dialog->addWidget($rawHtml);

			$jswrapper = new \Widgets\JSWrapper();
			$jswrapper->setJs('createOverlay("white", null, "show")');
			$ajaxResponseObject->addWidget($jswrapper);

			$keepBothButton = array();
			$keepBothButton["label"] = "Beide behalten";
			$keepBothButton["js"] = "sendRequest('Cut', {'id':{$this->id}, 'rename':true}, '', 'nonModalUpdater', null, null, 'explorer');closeDialog();";
			$buttons[0] = $keepBothButton;

			$replaceButton = array();
			$replaceButton["label"] = "Ersetzen";
			$replaceButton["js"] = "sendRequest('Delete', {'id':{$this->duplicateNameObject->get_id()}}, '', 'data', function(){sendRequest('Cut', {'id':{$this->id}}, '', 'nonModalUpdater', null, null, 'explorer');}, null, 'explorer');closeDialog();";
			$buttons[1] = $replaceButton;

			$dialog->setButtons($buttons);
			$dialog->setWidth(500);
			$ajaxResponseObject->addWidget($dialog);
		}
		else{
			$rawHtml = new \Widgets\RawHtml();
			$rawHtml->setHtml("");
			$ajaxResponseObject->addWidget($rawHtml);
			$jswrapper = new \Widgets\JSWrapper();
			$clipboardModel = new \Explorer\Model\Clipboard($this->user);
			$js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';";
			$js .= "jQuery('#{$this->id}').remove();document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';";
			$jswrapper->setJs($js);
			$ajaxResponseObject->addWidget($jswrapper);
		}
		return $ajaxResponseObject;
	}
}
?>

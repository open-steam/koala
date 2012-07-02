<?php
namespace Explorer\Commands;

class ExplorerProperties extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		//$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
                $userObject = $GLOBALS["STEAM"]->get_current_steam_user();
                
                $checkboxObjectsHidden = new \Widgets\Checkbox();
		$checkboxObjectsHidden->setLabel("Verstecke Objekte <br> anzeigen");
		$checkboxObjectsHidden->setCheckedValue("TRUE");
		$checkboxObjectsHidden->setUncheckedValue("FALSE");
		$checkboxObjectsHidden->setData($userObject);
		$checkboxObjectsHidden->setContentProvider(\Widgets\DataProvider::attributeProvider("EXPLORER_SHOW_HIDDEN_DOCUMENTS"));

                //$seperator= new \Widgets\RawHtml();
		//$seperator->setHtml("<br style=\"clear:both\"/>");
                //$clearer = new \Widgets\Clearer();
                
		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Allgemeine Explorer-Einstellungen");
		$dialog->setPositionX($this->params["mouseX"]);
		$dialog->setPositionY($this->params["mouseY"]);

		$dialog->addWidget($checkboxObjectsHidden);
		$dialog->setForceReload(true);
                
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($dialog);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {

		$currentUser = $GLOBALS["STEAM"]->get_current_steam_user();
		$object = $currentUser->get_workroom();

		$dialog = new \Widgets\Dialog();
		$dialog->setTitle("Eigenschaften von " . $object->get_name());

		$dialog->setButtons(array(array("name"=>"speichern", "href"=>"save")));
		return $dialog->getHtml();
	}
}

class NameAttributeDataProvider extends \Widgets\AttributeDataProvider {

	public function getUpdateCode($object, $elementId, $successMethode = "") {
		if (is_int($object)) {
			$objectId = $object;
		} else {
			$objectId = $object->get_id();
		}
		$function = ($successMethode != "") ? ", function(response){{$successMethode}({$elementId}, response);}" : ",''";
		return <<< END
	sendRequest('databinding', {'id': {$objectId}, 'attribute': 'OBJ_DESC', 'value': ''}, '', 'data');
	sendRequest('databinding', {'id': {$objectId}, 'attribute': '{$this->getAttribute()}', 'value': value}, '', 'data'{$function});
END;
	}

}

?>
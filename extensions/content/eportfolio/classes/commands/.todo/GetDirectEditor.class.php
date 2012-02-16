<?php
namespace Portfolio\Commands;
class GetDirectEditor extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $object;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$objDesc = trim($this->object->get_attribute(OBJ_DESC));
		if (($objDesc === 0) || ($objDesc === "")) {
			$this->object->set_attribute(OBJ_DESC, $this->object->get_name());
		}
		$titelInput = new \Widgets\TextInput();
		$titelInput->setData($this->object);
		$titelInput->setFocus(true);
		$titelInput->setContentProvider(new NameAttributeDataProvider("OBJ_NAME", getCleanName($this->object, -1)));
		
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setJs("jQuery(document).click(function() {removeAllDirectEditors();})");
		
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($titelInput);
		$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;
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
	sendRequest('databinding', {'id': {$objectId}, 'attribute': 'OBJ_DESC', 'value': value}, '', 'data'{$function});
END;
	}

}
?>
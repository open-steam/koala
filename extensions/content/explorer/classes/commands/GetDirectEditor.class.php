<?php
namespace Explorer\Commands;
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
		$titelInput = new \Widgets\TextInput();
		$titelInput->setData($this->object);
		$titelInput->setFocus(true);
		if(strpos(strtolower($_SERVER["REQUEST_URI"]), "galleryview") == false) $titelInput->setInputWidth(245);
		$titelInput->setContentProvider(new \Widgets\NameAttributeDataProvider("OBJ_NAME", getCleanName($this->object, -1)));

		$rawHtml = new \Widgets\RawHtml();
                //if the user clicks, the directEditor is saved and closed
		$rawHtml->setJs("firstTime = true;"
                        . "jQuery(document).keyup(function(e) {"
                                  ."if (e.keyCode == 13) {removeAllDirectEditors(firstTime); firstTime=false;}" //enter
                                  ."if (e.keyCode == 27) {removeAllDirectEditors(false);}" //escape
                                ."});");

                //und methode einbauen, die nur den directEditor schließt, das objekt löscht und nichts speichert

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($titelInput);
		$ajaxResponseObject->addWidget($rawHtml);
		return $ajaxResponseObject;
	}
}
?>

<?php
namespace Explorer\Commands;
class EmptyTrashbin extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $elements;
	private $trashbin;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->trashbin = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("USER_TRASHBIN");
		if (isset($this->params["id"])) {
			$this->id = $this->params["id"];
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			$object->delete();
		} else {
			$this->elements = $this->trashbin->get_inventory();
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		if(isset($this->params["fromNameSpace"]) && ($this->params["fromNameSpace"] === "Trashbin")){
			$reloadId= \lms_steam::get_current_user()->get_trashbin()->get_id();
			$reload = new \Widgets\JSWrapper();
			$reload->setPostJsCode(<<<END
		closeDialog();
		sendRequest("LoadContent", {"id":"{$reloadId}"}, "trashbinWrapper", "updater", null, null, "trashbin");
END
			);

			$ajaxResponseObject->addWidget($reload);
				
		}
		if (!isset($this->id)) {
				
			$jswrapper = new \Widgets\JSWrapper();
			$ids = "";
			$elements = "";
			foreach ($this->elements as $key => $element) {
				if (count($this->elements) > $key + 1) {
					$ids .= "{\"id\":\"" . $element->get_id() . "\"}, ";
					$elements .= "\"\", ";
				} else {
					$ids .= "{\"id\":\"" . $element->get_id() . "\"}";
					$elements .= "\"\"";
				}
			}
			$js = "sendMultiRequest('EmptyTrashbin', jQuery.parseJSON('[$ids]'), jQuery.parseJSON('[$elements]'), 'updater', null, null, 'explorer', 'Leere Papierkorb ...', 0, " . count($this->elements) . ");";
			$jswrapper->setJs($js);
			$ajaxResponseObject->addWidget($jswrapper);
				
			return $ajaxResponseObject;
		} else {
				
			$trashbinModel = new \Explorer\Model\Trashbin($this->trashbin);
			$jswrapper = new \Widgets\JSWrapper();
			$js = "document.getElementById('trashbinIconbarWrapper').innerHTML = '" . $trashbinModel->getIconbarHtml() . "'; jQuery('.justTrashed').hide();";
			$jswrapper->setJs($js);
			$ajaxResponseObject->addWidget($jswrapper);
			return $ajaxResponseObject;
		}
	}
}
?>
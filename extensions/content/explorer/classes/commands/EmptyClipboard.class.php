<?php
namespace Explorer\Commands;
class EmptyClipboard extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $elements;
	private $clipboard;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->clipboard = \lms_steam::get_current_user();
		if (isset($this->params["id"])) {
			$this->id = $this->params["id"];
			$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
			if (getObjectType($object) === "pyramiddiscussion") {
                            \ExtensionMaster::getInstance()->getExtensionById("Pyramiddiscussion")->deletePyramiddiscussion($object);
                        } else {
                            $object->delete();
                        }
		} else {
			$this->elements = $this->clipboard->get_inventory();
		}
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		if (!isset($this->id)) {
			$ajaxResponseObject->setStatus("ok");
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
			$js = "sendMultiRequest('EmptyClipboard', jQuery.parseJSON('[$ids]'), jQuery.parseJSON('[$elements]'), 'updater', null, null, 'explorer', 'Leere Zwischenablage ...', 0, " . count($this->elements) . ");";
			$jswrapper->setJs($js);
			$ajaxResponseObject->addWidget($jswrapper);
			return $ajaxResponseObject;
		} else {
			$ajaxResponseObject->setStatus("ok");

			$clipboardModel = new \Explorer\Model\Clipboard($this->clipboard);
			$jswrapper = new \Widgets\JSWrapper();
			$path = strtolower($_SERVER["REQUEST_URI"]);
			if($path != "/clipboard/"){
				$js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';";
			}
			else{
				$js = "$('#" . $this->id . "').hide();";
				$js .="$('img[title=\"Zwischenablage leeren\"]').parent().parent().parent().hide();";
				$js .= "$('#ClipboardIconbarWrapper').html('" . $clipboardModel->getIconbarHtml() . "');";
				$js .= "if ($('div.listviewer-items div:visible').length == 0) $('div.listviewer-items').append('<div class=\"listviewer-noitem\">Die Zwischenablage ist leer.</div>');";
			}
			$jswrapper->setJs($js);
			$ajaxResponseObject->addWidget($jswrapper);
			return $ajaxResponseObject;
		}
	}
}
?>

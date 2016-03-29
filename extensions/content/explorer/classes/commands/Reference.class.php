<?php
namespace Explorer\Commands;
class Reference extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $user;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->user = $GLOBALS["STEAM"]->get_current_steam_user();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$link = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object);
		$link->set_attributes(array(OBJ_DESC => $object->get_attribute(OBJ_DESC)));
		$link->move($this->user);
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$clipboardModel = new \Explorer\Model\Clipboard($this->user);
		$js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';" ;
		$jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		$informSlider = new \Widgets\InformSlider();
		$informSlider->setTitle("Information");
		$informSlider->setPostJsCode("createInformSlider()");
		$informSlider->setContent("Die Referenz wurde in der Zwischenablage erstellt.");
		$ajaxResponseObject->addWidget($informSlider);
		return $ajaxResponseObject;
	}

}
?>

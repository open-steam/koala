<?php
namespace Portal\Commands;
class PortletReference extends \AbstractCommand implements \IAjaxCommand {

	private $params;
	private $id;
	private $user;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->user = \lms_steam::get_current_user();
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$object = $this->getSource($object);
		$link = \steam_factory::create_link($GLOBALS["STEAM"]->get_id(), $object);

		//$link->set_attributes(array(OBJ_DESC => $object->get_attribute(OBJ_DESC)));

	    $link->set_attributes(array(
            OBJ_DESC => $object->get_attribute(OBJ_DESC),
            "bid:portlet" => $object->get_attribute("bid:portlet"),
            ));

	    $link->move($this->user);
	}

	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$clipboardModel = new \Explorer\Model\Clipboard($this->user);
		$js = "document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';" ;
		$jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		//$informSlider = new \Widgets\InformSlider();
		//$informSlider->setTitle("Information");
		//$informSlider->setPostJsCode("createInformSlider()");
		//$informSlider->setContent("Die Referenz wurde in der Zwischenablage erstellt.");
		//$ajaxResponseObject->addWidget($informSlider);
		return $ajaxResponseObject;
	}


	function getSource($object){
		if($object instanceof \steam_link){
			return $this->getSource($object->get_source_object());
		}
		return $object;
	}
}
?>

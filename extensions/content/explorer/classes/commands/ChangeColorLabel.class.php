<?php
namespace Explorer\Commands;
class ChangeColorLabel extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $object;
	private $color;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->color = $this->params["color"];
		$this->object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$this->object->set_attribute("OBJ_COLOR_LABEL", $this->color);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$js = "jQuery('#{$this->id}').removeClass('red orange yellow green blue purple grey transparent').addClass('{$this->color}');";
		$jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>
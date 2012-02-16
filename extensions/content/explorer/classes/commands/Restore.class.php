<?php
namespace Explorer\Commands;
class Restore extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $env;
	private $trashbin;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->env =  \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["env"]);
		$this->trashbin = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("USER_TRASHBIN");

		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$object->move($this->env);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$trashbinModel = new \Explorer\Model\Trashbin($this->trashbin);
		$js = "jQuery('#{$this->id}').removeClass('justTrashed').removeClass('listviewer-item-selected');
			       document.getElementById('{$this->id}_checkbox').disabled = false;
			       document.getElementById('{$this->id}').onclick = document.getElementById('{$this->id}').onclick_restore;
				   document.getElementById('{$this->id}').onclick_restore = \"\";
				   document.getElementById('trashbinIconbarWrapper').innerHTML = '" . $trashbinModel->getIconbarHtml() . "';" ;
		$jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>
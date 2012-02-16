<?php
namespace Explorer\Commands;
class Delete extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	private $trashbin;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		$this->trashbin = $GLOBALS["STEAM"]->get_current_steam_user()->get_attribute("USER_TRASHBIN");
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$object->move($this->trashbin);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$trashbinModel = new \Explorer\Model\Trashbin($this->trashbin);
		$js = "jQuery('#{$this->id}').addClass('justTrashed').removeClass('listviewer-item-selected').find('input:checkbox').attr('disabled', 'disabled');
			   var checkbox = document.getElementById('{$this->id}_checkbox');
			   if (!checkbox) {
			   		location.reload();
			   } else {
			   	checkbox.checked = false;
			   	document.getElementById('{$this->id}').onclick_restore = document.getElementById('{$this->id}').onclick;
			   	document.getElementById('{$this->id}').onclick = \"\";
		       	document.getElementById('trashbinIconbarWrapper').innerHTML = '" . $trashbinModel->getIconbarHtml() . "';
			   }" ;
		$jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>
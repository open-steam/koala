<?php
namespace Explorer\Commands;
class Cut extends \AbstractCommand implements \IAjaxCommand {
	
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
		$object->move($this->user);
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$clipboardModel = new \Explorer\Model\Clipboard($this->user);
		$js = "
		 if (jQuery('#explorerWrapper').length == 0) {
			   	location.reload();
			   }
		 else{
		       jQuery('#{$this->id}').remove();document.getElementById('clipboardIconbarWrapper').innerHTML = '" . $clipboardModel->getIconbarHtml() . "';
			}" ;	
		$jswrapper->setJs($js);
		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>
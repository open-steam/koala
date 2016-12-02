<?php
namespace Bookmarks\Commands;
class RemoveBookmark extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {		
		$this->params = $requestObject->getParams();
		$this->id = $this->params["id"];
		
		$object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		
		$object->move(\lms_steam::get_current_user()->get_attribute(USER_TRASHBIN));
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$ajaxResponseObject->setStatus("ok");
// 		$jswrapper = new \Widgets\JSWrapper();
// 		$js = "";
// 		$js .= "jQuery('#{$id}').addClass('justTrashed').removeClass('listviewer-item-selected').find('input:checkbox').attr('disabled', 'disabled');
// 		       document.getElementById('{$id}_checkbox').checked = false;
// 		       document.getElementById('{$id}').onclick_restore = document.getElementById('{$id}').onclick;
// 			   document.getElementById('{$id}').onclick = \"\";";
// 		$jswrapper->setJs($js);
//		$ajaxResponseObject->addWidget($jswrapper);
		return $ajaxResponseObject;
	}
}
?>
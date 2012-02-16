<?php
namespace PortletMsg\Commands;
class DeleteImage extends \AbstractCommand implements \IAjaxCommand {
	
	private $params;
	private $id;
	
	public function validateData(\IRequestObject $requestObject) {
		return true;
	}
	
	public function processData(\IRequestObject $requestObject) {
		if ($requestObject instanceof \UrlRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params[0]) ? $this->id = $this->params[0]: "";
		} else if ($requestObject instanceof \AjaxRequestObject) {
			$this->params = $requestObject->getParams();
			isset($this->params["id"]) ? $this->id = $this->params["id"]: "";
		}
	}
	
	public function ajaxResponse(\AjaxResponseObject $ajaxResponseObject) {
		$destObject = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		//remove old image
		$oldImageId = $destObject->get_attribute("bid:portlet:msg:picture_id");
		if ($oldImageId !== 0) {
			$oldImage = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $oldImageId);
			if ($oldImage instanceof \steam_document) {
				$destObject->delete_attribute("bid:portlet:msg:picture_id");
				$oldImage ->delete();
			}
		}
		$ajaxResponseObject->setStatus("ok");
		return $ajaxResponseObject;
	}
}

?>
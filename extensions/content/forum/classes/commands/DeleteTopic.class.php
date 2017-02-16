<?php
namespace Forum\Commands;

class DeleteTopic extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

	private $id;
	private $params;

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
		//TODO: CORRECT REDIRECTION
		$forumId=$this->params["forum"];
		$object_id=$this->id;
		/** log-in user */
		$steamUser =  \lms_steam::get_current_user_no_guest();
		/** id of the log-in user */
		$steamUserId = $steamUser->get_id();
		
		$steam=$GLOBALS["STEAM"];
		$steamId=$steam->get_id();

		/** the current object */
		$object = \steam_factory::get_object($steamId, $object_id);
		$annotations = $object->get_annotations();
		$trash = $steamUser->get_attribute(USER_TRASHBIN, 1);
		$annotating = $object->get_annotating(1);
		$result = $steam->buffer_flush();
		$trash = $result[$trash];
		$annotating = $result[$annotating];
		//move objects to trashbin
		if (is_object($trash)) {
			if ($annotating) {
				$annotating->remove_annotation($object, 1);
				$annotating->set_acquire(false, 1);
			}
			$object->move($trash,1);
			$steam->buffer_flush();
		}
		$widget = new \Widgets\JSWrapper();
		$backlink= PATH_URL."forum/index/". $forumId . "/";
		$widget->setJs("self.location.href='{$backlink}'");
		
		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($widget);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
	}
}
?>
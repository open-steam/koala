<?php
namespace Forum\Commands;

class DeleteReply extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$object_id=$this->id;
		/** log-in user */
		$steamUser =  \lms_steam::get_current_user();
		/** id of the log-in user */
		$steamUserId = $steamUser->get_id();

		$steam=$GLOBALS["STEAM"];
		$steamId=$steam->get_id();

		/** the current object */
		$object = \steam_factory::get_object($steamId, $object_id);
		$objectCreatorId = $object->get_creator()->get_id();

		$forumId=$this->params["forum"];
		$forum=\steam_factory::get_object($steamId, $forumId);
		$forumCreatorId= $forum->get_creator()->get_id();

		if($forumCreatorId==$steamUserId){
			$annotations = $object->get_annotations();
			$annotating = $object->get_annotating(1);
			$result = $steam->buffer_flush();
			
			$annotating = $result[$annotating];


			if(!empty($annotations)){
				foreach($annotations as $annotation){
					$annotation->delete(1);
				}
			}
			$object->delete(1);
			$steam->buffer_flush();
				
		}
		else if($objectCreatorId==$steamUserId){
			$object->set_attribute("OBJ_DESC", "");
			$object->set_content("");
		}

		$widget = new \Widgets\JSWrapper();
		$widget->setJs("location.reload();");

		$ajaxResponseObject->setStatus("ok");
		$ajaxResponseObject->addWidget($widget);
		return $ajaxResponseObject;
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
	}
}
?>
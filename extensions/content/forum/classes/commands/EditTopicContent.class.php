<?php
namespace Forum\Commands;
class EditTopicContent extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$object= \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$objectId = $object->get_id();
		$steamUser= \lms_steam::get_current_user();
		$steamUserId= $steamUser->get_id();
		$steam = $GLOBALS["STEAM"];
		$steamId=$steam->get_id();
		$allowed_write=$object->check_access_write($steamUser);


		$title=$this->params["title"];

		$content=$this->params["content"];
	


		if($allowed_write && $title !="" && $content != "") {
			$attributes = array(
			/* object name cannot be change leave the comment intentionally
			 *"OBJ_NAME" => $_POST["title"],
			 */
			OBJ_DESC => $title
     		);
			$object->set_attributes($attributes, 0);

			$object->set_content($content);
		}
		else{
			throw \Exception("Title or content not set");
		}
		$ajaxResponseObject->setStatus("ok");
		$widget = new \Widgets\JSWrapper();
		$widget->setJs("location.reload();");
		$ajaxResponseObject->addWidget($widget);
		return $ajaxResponseObject;

	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {

	}
}
?>
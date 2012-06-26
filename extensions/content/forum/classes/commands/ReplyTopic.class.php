<?php
namespace Forum\Commands;
class ReplyTopic extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {

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
		$title=$this->params["title"];
		$content=$this->params["content"];
                $path = array();
                $path = explode("/", $this->params["path"]);               
		$objectId=$path[count($path)-1];               
		$forumId=$this->params["forum"];

		$steam=$GLOBALS["STEAM"];
		$steamId=$steam->get_id();


		$object = \steam_factory::get_object($steamId, $objectId);
		$forum = \steam_factory::get_object($steamId, $forumId);



		if(trim($title) != ""){
			$new_annotation = \steam_factory::create_textdoc(
			$steamId,
			rawurlencode($title),
			stripslashes($content)
			);

			$new_annotation->set_attribute("OBJ_DESC",  $title);

			$object->add_annotation( $new_annotation );
			// set acquiring
			$new_annotation->set_acquire($object);

			$subscription = $forum->get_attribute("bid:forum_subscription");
			if ($subscription) {
				foreach($subscription as $key => $user) {
					$user->get_attributes(array("USER_EMAIL"),1);
				}
				$result = $steam->buffer_flush();

				foreach($subscription as $key => $user) {
					$recipient = $user->get_attribute("USER_EMAIL");
					$steam->send_mail_from(
					$recipient,
          "New message in forum " . $forum->get_name . ", thread: " . $object->get_name(),
          "",
          "postmaster",
					1,
          "text/plain"
          );
				}
				$steam->buffer_flush();
			}

		}
		else{
			echo "Ihre Antwort hat keinen Inhalt!";
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
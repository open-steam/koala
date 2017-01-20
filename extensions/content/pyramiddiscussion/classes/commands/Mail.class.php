<?php
namespace Pyramiddiscussion\Commands;
class Mail extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$user = \lms_steam::get_current_user();
		$pyramidRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$pyramiddiscussionExtension = \Pyramiddiscussion::getInstance();
		$pyramiddiscussionExtension->addCSS();
		
		// mail form got submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_mail"])) {
			$group = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_PRIVGROUP");
			if ($group->is_admin($user)) {
				$title = "Rundmail zur Pyramidendiskussion: ". $pyramidRoom->get_attribute("OBJ_DESC");
				$content = nl2br($_POST["content"]);
				$group->mail($title, $content);
				$frameResponseObject->setConfirmText("Rundmail erfolgreich gesendet.");
			}
		}
		
		$content = $pyramiddiscussionExtension->loadTemplate("pyramiddiscussion_mail.template.html");
		$content->setCurrentBlock("BLOCK_PYRAMID_MAIL");
		$content->setVariable("PYRAMID_MAIL", "Rundmail erstellen");
		$content->setVariable("CONTENT_LABEL", "Inhalt:");
		$content->setVariable("SEND_MAIL", "Rundmail senden");
		$content->setVariable("BACK_LABEL", "Zurück");
		$content->setVariable("BACK_URL", $pyramiddiscussionExtension->getExtensionUrl() . "Index/" . $this->id);
		$content->parse("BLOCK_PYRAMID_MAIL");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => "Pyramidendiskussion" , "link" => $pyramiddiscussionExtension->getExtensionUrl() . "Index/" . $this->id),
			array("name" => "Rundmail erstellen"),
		));
		return $frameResponseObject;
	}
}
?>
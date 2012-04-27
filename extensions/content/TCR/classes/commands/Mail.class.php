<?php
namespace TCR\Commands;
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
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$TCR = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$TCRExtension = \TCR::getInstance();
		$TCRExtension->addCSS();
		$admins = $TCR->get_attribute("TCR_ADMINS");
		
		// display error message if user is no admin
		if (!in_array($user->get_id(), $admins)) {
			$actionbar = new \Widgets\Actionbar();
			$actions = array(
				array("name" => "Private Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id),
				array("name" => "Übersicht" , "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Alle Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "documents/" . $this->id));
			$actionbar->setActions($actions);
			$frameResponseObject->addWidget($actionbar);
		
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("<center>Zugang verwehrt. Sie sind kein Administrator in diesem Thesen-Kritik-Replik-Verfahren</center>");
			$frameResponseObject->addWidget($rawWidget);
			$frameResponseObject->setHeadline(array(
				array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Rundmail erstellen")
			));
			return $frameResponseObject;
		}
		
		// mail form got submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_mail"])) {
			$members = $TCR->get_attribute("TCR_USERS");
			if (in_array($user->get_id(), $admins)) {
				$title = "Rundmail zum Thesen-Kritik-Replik Verfahren: ". $TCR->get_name();
				$content = nl2br($_POST["content"]);
				
				foreach ($admins as $admin) {
					if (!in_array($admin, $members)) {
						array_push($members, $admin);
					}
				}
				
				foreach ($members as $member) {
					$member_object = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $member);
					$member_object ->mail($title, $content);
				}
				$frameResponseObject->setConfirmText("Rundmail erfolgreich gesendet.");
			}
		}
		
		$actionbar = new \Widgets\Actionbar();
		$actions = array(
				array("name" => "Konfiguration" , "link" => $TCRExtension->getExtensionUrl() . "configuration/" . $this->id),
				array("name" => "Rundmail erstellen" , "link" => $TCRExtension->getExtensionUrl() . "mail/" . $this->id),
				array("name" => "Private Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id),
				array("name" => "Übersicht" , "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Alle Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "documents/" . $this->id));
		$actionbar->setActions($actions);
		$frameResponseObject->addWidget($actionbar);
		
		$content = $TCRExtension->loadTemplate("tcr_mail.template.html");
		$content->setCurrentBlock("BLOCK_TCR_MAIL");
		$content->setVariable("TCR_MAIL", "Rundmail erstellen");
		$content->setVariable("CONTENT_LABEL", "Inhalt:");
		$content->setVariable("SEND_MAIL", "Rundmail senden");
		$content->parse("BLOCK_TCR_MAIL");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => "Thesen-Kritik-Replik-Verfahren" , "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
			array("name" => "Rundmail erstellen"),
		));
		return $frameResponseObject;
	}
}
?>
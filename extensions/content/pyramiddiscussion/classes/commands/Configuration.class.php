<?php
namespace Pyramiddiscussion\Commands;
class Configuration extends \AbstractCommand implements \IFrameCommand {

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
		$pyramiddiscussionExtension->addJS();
		$basegroup = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_BASEGROUP");
		$admingroup = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_ADMINGROUP");
                $group = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_PRIVGROUP");
		$content = $pyramiddiscussionExtension->loadTemplate("pyramiddiscussion_configuration.template.html");
		
		// if user is no admin display error msg
		if (!$group->is_admin($user)) {
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("Error: Kein Administrator");
			$frameResponseObject->addWidget($rawWidget);
			return $frameResponseObject;
		}
		
		// change configuration
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_options"])) {
			$pyramidRoom->set_attribute("OBJ_DESC", $_POST["title"]);
			$pyramidRoom->set_attribute("PYRAMIDDISCUSSION_DESC", $_POST["info"]);
			$pyramidRoom->set_attribute("PYRAMIDDISCUSSION_ACTCOL", $_POST["phase"]);
			if (isset($_POST["use_deadlines"]) && $_POST["use_deadlines"] == "on") {
				$pyramidRoom->set_attribute("PYRAMIDDISCUSSION_USEDEADLINES", "yes");
				$deadlines = $_POST["deadline"];
				$deadlineArray = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_DEADLINES");
				for ($count = 1; $count <= count($deadlines); $count++) {
					$deadline = $deadlines[$count];
					$time = substr($deadline, 11);
					$deadline = substr($deadline, 0, 10);
					$deadline = mktime(substr($time,0,2), substr($time,3,2), 0, substr($deadline,3,2), substr($deadline,0,2), substr($deadline,6,4));
					$deadlineArray[$count] = $deadline;
				}
				$pyramidRoom->set_attribute("PYRAMIDDISCUSSION_DEADLINES", $deadlineArray);
				$pyramidRoom->set_attribute("PYRAMIDDISCUSSION_OVERRIDE_DEADLINES", 0);
			} else {
				$pyramidRoom->set_attribute("PYRAMIDDISCUSSION_USEDEADLINES", "no");
				$pyramidRoom->set_attribute("PYRAMIDDISCUSSION_OVERRIDE_DEADLINES", 0);
			}
			$frameResponseObject->setConfirmText("Änderungen erfolgreich gespeichert.");
		}
		
		$phase = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_ACTCOL");
		$maxcol = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAXCOL");
		$deadlines = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_DEADLINES");
		
		// display configuration table
		$content->setCurrentBlock("BLOCK_PYRAMID_OPTIONS");
		$content->setVariable("PYRAMID_OPTIONS", "Konfiguration der Pyramidendiskussion");
		$content->setVariable("TITLE_LABEL", "Diskussionsthema:");
		$content->setVariable("TITLE_VALUE", $pyramidRoom->get_attribute("OBJ_DESC"));
		$content->setVariable("INFO_LABEL", "Infotext:");
		if ($pyramidRoom->get_attribute("PYRAMIDDISCUSSION_DESC") != "0") {
			$content->setVariable("INFO_VALUE", $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_DESC"));
		}
		$content->setVariable("START_LABEL", "Anzahl der Startfelder:");
		$content->setVariable("START_VALUE", $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAX"));
		$content->setVariable("BASEGROUP_LABEL", "Basisgruppe:");
		$content->setVariable("BASEGROUP_VALUE", $basegroup->get_name());
		$content->setVariable("ADMINGROUP_LABEL", "Admingruppe:");
                if ($admingroup instanceof \steam_group) {
                    $content->setVariable("ADMINGROUP_VALUE", $admingroup->get_name());
                } else {
                    $content->setVariable("ADMINGROUP_VALUE", "Keine");
                }
		$content->setVariable("EDITOR_LABEL", "Editor-Typ:");
		$editortype = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_EDITOR");
		if ($editortype == "text/plain") {
			$content->setVariable("EDITOR_VALUE", "Einfacher Text");
		} else if ($editortype == "text/html") {
			$content->setVariable("EDITOR_VALUE", "HTML Notation");
		} else {
			$content->setVariable("EDITOR_VALUE", "Wiki Notation");
		}
		$content->setVariable("PHASE_LABEL", "Aktuelle Phase:");
		for ($count = 0; $count <= $maxcol+2; $count++) {
			$content->setCurrentBlock("BLOCK_PHASE_OPTION");
			$content->setVariable("PHASE_ID", $count);
			if ($count == 0) {
				$content->setVariable("PHASE_VALUE", "Gruppeneinteilungsphase");
			} else if ($count <= $maxcol) {
				$content->setVariable("PHASE_VALUE", $count . ". Diskussionsphase");
			} else if ($count == $maxcol+1) {
				$content->setVariable("PHASE_VALUE", "Endphase");
			} else {
				$content->setVariable("PHASE_VALUE", "Pyramide einfrieren");
			}
			if ($count == $phase) {
				$content->setVariable("PHASE_SELECTED", "selected");
			}
			$content->parse("BLOCK_PHASE_OPTION");
		}
        $content->setCurrentBlock('BLOCK_PYRAMID_OPTIONS');

		$content->setVariable("DEADLINES_LABEL", "Benutze Deadlines:");
		if (count($deadlines) > 0 && $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_USEDEADLINES") == "yes") {
			$content->setVariable("DEADLINES_SELECTED", "checked");
		}
		$content->setVariable("MAX_PHASE", $maxcol);
		$content->setVariable("DEADLINE_PHASE_LABEL", "Diskussionsphase");
		$content->setVariable("DEADLINE_LABEL", "Deadline");
		for ($count = 1; $count <= $maxcol; $count++) {
			$content->setCurrentBlock("BLOCK_DEADLINE_ENTRY");
			$content->setVariable("DEADLINE_PHASE", $count);
			if (array_key_exists($count, $deadlines)) {
				$content->setVariable("DEADLINE_PHASE_VALUE", date("d.m.Y H:i", (int) $deadlines[$count]));
			} else {
				$content->setVariable("DEADLINE_PHASE_VALUE", date("d.m.Y H:i", time() + 172800*($count-1)));
			}
			$content->parse("BLOCK_DEADLINE_ENTRY");
		}
        $content->setCurrentBlock('BLOCK_PYRAMID_OPTIONS');
		$content->setVariable("SAVE_CHANGES", "Änderungen speichern");
		$content->setVariable("BACK_LABEL", "Zurück");
		$content->setVariable("BACK_LINK", $pyramiddiscussionExtension->getExtensionUrl() . "Index/" . $this->id);
		$content->parse("BLOCK_PYRAMID_OPTIONS");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => "Pyramidendiskussion" , "link" => $pyramiddiscussionExtension->getExtensionUrl() . "Index/" . $this->id),
			array("name" => "Konfiguration"),
		));
		return $frameResponseObject;
	}
}
?>
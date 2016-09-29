<?php
namespace Questionnaire\Commands;
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
		$questionnaire = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$QuestionnaireExtension = \Questionnaire::getInstance();
		$QuestionnaireExtension->addCSS();
		$QuestionnaireExtension->addJS();

		// access not allowed for non-admins
		$staff = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
		$admin = 0;
		foreach ($staff as $group) {
			if ($group->is_member($user)) {
				$admin = 1;
				break;
			}
		}
		if ($questionnaire->get_creator()->get_id() == $user->get_id()) {
			$admin = 1;
		}
		if ($admin == 0) {
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("<center>Die Bearbeitung dieses Fragebogens ist den Administratoren vorbehalten.</center>");
			$frameResponseObject->addWidget($rawWidget);
			return $frameResponseObject;
		}

		// display actionbar
		$surveys = $questionnaire->get_inventory();
		$surveyCount = 0;
		foreach ($surveys as $survey) {
			if ($survey instanceof \steam_object && (!$survey instanceof \steam_user)) {
				$surveyCount++;
			}
		}
		if ($surveyCount > 0) {
			$actionbar = new \Widgets\Actionbar();
			$actions = array(
				//array("name" => "Neuen Fragebogen erstellen" , "link" => $QuestionnaireExtension->getExtensionUrl() . "edit/" . $this->id . "/"),
				//array("name" => "Import" , "link" => $QuestionnaireExtension->getExtensionUrl() . "import/" . $this->id . "/"),
				//array("name" => "Konfiguration" , "link" => $QuestionnaireExtension->getExtensionUrl() . "configuration/" . $this->id . "/"),
				array("name" => "Übersicht", "link" => $QuestionnaireExtension->getExtensionUrl() . "Index/" . $this->id . "/")
			);
			$actionbar->setActions($actions);
		} else {
			$actionbar = new \Widgets\Actionbar();
			$actions = array(
				//array("name" => "Neuen Fragebogen erstellen" , "link" => $QuestionnaireExtension->getExtensionUrl() . "edit/" . $this->id . "/"),
				//array("name" => "Import" , "link" => $QuestionnaireExtension->getExtensionUrl() . "import/" . $this->id . "/"),
				//array("name" => "Konfiguration" , "link" => $QuestionnaireExtension->getExtensionUrl() . "configuration/" . $this->id . "/")
			);
			$actionbar->setActions($actions);
		}
		$frameResponseObject->addWidget($actionbar);

		// configuration got submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit_questionnaire"])) {
			$questionnaire->set_name($_POST["title"]);
			$questionnaire->set_attribute("QUESTIONNAIRE_DESC", $_POST["desc"]);
			$questionnaire->set_attribute("QUESTIONNAIRE_PARTICIPATION_TIMES", $_POST["times"]);
			$questionnaire->set_attribute("QUESTIONNAIRE_SHOW_PARTICIPANTS", $_POST["participants"]);
			$questionnaire->set_attribute("QUESTIONNAIRE_SHOW_CREATIONTIME", $_POST["creationtime"]);
			$questionnaire->set_attribute("QUESTIONNAIRE_ADMIN_EDIT", $_POST["adminedit"]);
			$questionnaire->set_attribute("QUESTIONNAIRE_OWN_EDIT", $_POST["ownedit"]);

			// delete previous sanctions if current user is creator
			$participants = $questionnaire->get_attribute("QUESTIONNAIRE_GROUP");
			$staff = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
			if ($user->get_id() == $questionnaire->get_creator()->get_id()) {
				foreach ($staff as $group) {
					$questionnaire->set_sanction($group, ACCESS_DENIED);
				}
				foreach ($participants as $group) {
					$questionnaire->set_sanction($group, ACCESS_DENIED);
				}
				foreach ($questionnaire->get_inventory() as $survey) {
					if ($survey instanceof \steam_container) {
						$resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
						if ($resultContainer instanceof \steam_container) {
							foreach ($staff as $group) {
								$resultContainer->set_sanction($group, ACCESS_DENIED);
							}
							foreach ($participants as $group) {
								$resultContainer->set_sanction($group, ACCESS_DENIED);
							}
						}
					}
				}
			}

			// collect submitted sanctions
			$groups = $questionnaire->get_creator()->get_groups();
			$staff = array();
			$participants = array();
			foreach ($groups as $group) {
				if (isset($_POST["participate" . $group->get_id()])) {
					array_push($participants, $group);
				}
				if (isset($_POST["admin" . $group->get_id()])) {
					array_push($staff, $group);
				}
			}

			// set new sanctions
			if ($user->get_id() == $questionnaire->get_creator()->get_id()) {
				foreach ($participants as $group) {
					$questionnaire->set_sanction($group, SANCTION_READ | SANCTION_WRITE);
					foreach ($questionnaire->get_inventory() as $survey) {
						if ($survey instanceof \steam_container) {
							$resultContainer = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $survey->get_path() . "/results");
							if ($resultContainer instanceof \steam_container) {
								$resultContainer->set_sanction($group, SANCTION_READ | SANCTION_WRITE | SANCTION_INSERT);
							}
						}
					}
				}
				foreach ($staff as $group) {
					$questionnaire->set_sanction($group, SANCTION_ALL);
				}
				$questionnaire->set_attribute("QUESTIONNAIRE_GROUP", $participants);
				$questionnaire->set_attribute("QUESTIONNAIRE_STAFF", $staff);
			}

			//$frameResponseObject->setConfirmText("Änderungen erfolgreich gespeichert.");
		}

		// display configuration form
		$content = $QuestionnaireExtension->loadTemplate("questionnaire_configuration.template.html");
		$content->setCurrentBlock("BLOCK_CONFIGURATION_TABLE");
		$content->setVariable("QUESTIONNAIRE_OPTIONS", "Allgemeine Einstellungen");
		$content->setVariable("TITLE_LABEL", "Titel:*");
		$content->setVariable("TITLE_VALUE", $questionnaire->get_name());
		$content->setVariable("DESC_LABEL", "Beschreibung:");
		if ($questionnaire->get_attribute("QUESTIONNAIRE_DESC") != "0") {
			$content->setVariable("DESC_VALUE", $questionnaire->get_attribute("QUESTIONNAIRE_DESC"));
		}
		$content->setVariable("TIMES_LABEL", "Ausfüllen:");
		$content->setVariable("ONE_TIME", "einfach");
		$content->setVariable("MANY_TIMES", "mehrfach");
		if ($questionnaire->get_attribute("QUESTIONNAIRE_PARTICIPATION_TIMES") == 0) {
			$content->setVariable("MANY_TIMES_CHECKED", "checked");
		} else {
			$content->setVariable("ONE_TIME_CHECKED", "checked");
		}
		$content->setVariable("RIGHTS_LABEL", "Zugriffsrechte");
		$content->setVariable("PARTICIPATE_LABEL", "Ausfüllen");
		$content->setVariable("EDIT_LABEL", "Bearbeiten und Auswerten");
		$content->setVariable("OWNER_LABEL", "Besitzer");
		$content->setVariable("OWNER_NAME", $questionnaire->get_creator()->get_full_name());
		$content->setVariable("GROUP_LABEL", "Gruppe");
		$groups = $questionnaire->get_creator()->get_groups();
		$participants = $questionnaire->get_attribute("QUESTIONNAIRE_GROUP");
		$admins = $questionnaire->get_attribute("QUESTIONNAIRE_STAFF");
		foreach ($groups as $group) {
			$content->setCurrentBlock("BLOCK_GROUP");
			$content->setVariable("GROUP_NAME", $group->get_name());
			$content->setVariable("GROUP_ID", $group->get_id());
			if (in_array($group, $participants)) {
				$content->setVariable("PARTICIPATE_CHECKED", "checked");
			}
			if (in_array($group, $admins)) {
				$content->setVariable("ADMIN_CHECKED", "checked");
			}
			if ($user->get_id() != $questionnaire->get_creator()->get_id()) {
				$content->setVariable("PARTICIPATE_DISABLED", "disabled");
				$content->setVariable("ADMIN_DISABLED", "disabled");
			}
			$content->parse("BLOCK_GROUP");
		}
		$content->setVariable("RESULT_OPTIONS", "Ergebnis-Einstellungen");
		$content->setVariable("PARTICIPANTS_LABEL", "Teilnehmer anzeigen:");
		$content->setVariable("CREATIONTIME_LABEL", "Erstellungszeit anzeigen:");
		$content->setVariable("ADMINEDIT_LABEL", "alle Antworten von Administratoren editierbar:");
		$content->setVariable("OWNEDIT_LABEL", "eigene Antworten editierbar:");
		$content->setVariable("LABEL_YES", "Ja");
		$content->setVariable("LABEL_NO", "Nein");
		if ($questionnaire->get_attribute("QUESTIONNAIRE_SHOW_PARTICIPANTS") == 1) {
			$content->setVariable("PARTICIPANTS_YES_CHECKED", "checked");
		} else {
			$content->setVariable("PARTICIPANTS_NO_CHECKED", "checked");
		}
		if ($questionnaire->get_attribute("QUESTIONNAIRE_SHOW_CREATIONTIME") == 1) {
			$content->setVariable("CREATIONTIME_YES_CHECKED", "checked");
		} else {
			$content->setVariable("CREATIONTIME_NO_CHECKED", "checked");
		}
		if ($questionnaire->get_attribute("QUESTIONNAIRE_ADMIN_EDIT") == 1) {
			$content->setVariable("ADMINEDIT_YES_CHECKED", "checked");
		} else {
			$content->setVariable("ADMINEDIT_NO_CHECKED", "checked");
		}
		if ($questionnaire->get_attribute("QUESTIONNAIRE_OWN_EDIT") == 1) {
			$content->setVariable("OWNEDIT_YES_CHECKED", "checked");
		} else {
			$content->setVariable("OWNEDIT_NO_CHECKED", "checked");
		}
		$content->setVariable("EDIT_QUESTIONNAIRE", "Änderungen speichern");
		$content->parse("BLOCK_CONFIGURATION_TABLE");

		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		return $frameResponseObject;
	}
}
?>

<?php
namespace TCR\Commands;
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
		$TCR = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$TCRExtension = \TCR::getInstance();
		$TCRExtension->addCSS();
		$content = $TCRExtension->loadTemplate("tcr_configuration.template.html");
		$group = $TCR->get_attribute("TCR_GROUP");
		$members = $group->get_members();
		$admins = $TCR->get_attribute("TCR_ADMINS"); 
		$users = $TCR->get_attribute("TCR_USERS");
		
		if ($group->get_name() == "learners") {
			$parent = $group->get_parent_group();
			$courseOrGroup = "Kurs: " . $parent->get_attribute("OBJ_DESC") . " (" . $parent->get_name() . ")";
			$courseOrGroupUrl = PATH_URL . "semester/" . $parent->get_id();
		} else {
			$courseOrGroup = "Gruppe: " . $group->get_name();
			$courseOrGroupUrl = PATH_URL . "groups/" . $group->get_id();
		}
		
		if ($group->get_name() == "learners") {
			$parent = $group->get_parent_group();
			$groupname = $parent->get_attribute("OBJ_DESC") . " (" . $parent->get_name() . ")";
			$subgroups = $parent->get_subgroups();
			foreach ($subgroups as $subgroup) {
				if ($subgroup->get_name() == "staff") {
					$staff = $subgroup->get_members();
					foreach ($staff as $staffMember) {
						if ($staffMember instanceof \steam_user) {
							array_push($members, $staffMember);
						}
					}
					break;
				}
			}
		} else {
			$groupname = $group->get_name();
		}
		
		// configuration form got submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_configuration"])) {
			$TCR->set_attribute("OBJ_DESC", $_POST["title"]);
			if (intval($_POST["rounds"]) > 0) {
				$TCR->set_attribute("TCR_ROUNDS", $_POST["rounds"]);
			}
			if (isset($_POST["admin"])) {
				$admins_post = $_POST["admin"];
			} else {
				$admins_post = array();
			}
			if (isset($_POST["member"])) {
				$members_post = $_POST["member"];
			} else {
				$members_post = array();
			}
			foreach ($members as $member) {
				if ($member instanceof \steam_user) {
					if (!array_key_exists($member->get_id(), $admins_post)) {
						$admins_post[$member->get_id()] = "off";
					}
					if (!array_key_exists($member->get_id(), $members_post)) {
						$members_post[$member->get_id()] = "off";
					}
				}
			}
			$former_admins_post = $_POST["formeradmin"];
			$former_members_post = $_POST["formermember"];
			foreach ($members as $member) {
				if ($member instanceof \steam_user) {
					// set new admin rights
					if ($admins_post[$member->get_id()] != $former_admins_post[$member->get_id()] && $member->get_id() != $TCR->get_creator()->get_id()) {
						if ($admins_post[$member->get_id()] == "off" && $former_admins_post[$member->get_id()] == "on") {
							unset($admins[array_search($member->get_id(), $admins)]);
							$admins = array_values($admins);
						} else if ($admins_post[$member->get_id()] == "on" && $former_admins_post[$member->get_id()] == "off") {
							array_push($admins, $member->get_id());
						}
					}
					// set new members
					if ($members_post[$member->get_id()] != $former_members_post[$member->get_id()]) {
						if ($members_post[$member->get_id()] == "off" && $former_members_post[$member->get_id()] == "on") {
							unset($users[array_search($member->get_id(), $users)]);
							$users = array_values($users);
						} else if ($members_post[$member->get_id()] == "on" && $former_members_post[$member->get_id()] == "off") {
							array_push($users, $member->get_id());
						}
					}
				}
			}
			$TCR->set_attribute("TCR_ADMINS", $admins);
			$TCR->set_attribute("TCR_USERS", $users);
		}
		
		// display error message if current user is no admin
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
				array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
				array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
				array("name" => "Konfiguration")
			));
			return $frameResponseObject;
		}
		
		// display actionbar
		$actionbar = new \Widgets\Actionbar();
		$actions = array(
			array("name" => "Konfiguration" , "link" => $TCRExtension->getExtensionUrl() . "configuration/" . $this->id),
			array("name" => "Private Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id),
			array("name" => "Übersicht" , "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
			array("name" => "Alle Dokumente" , "link" => $TCRExtension->getExtensionUrl() . "documents/" . $this->id));
		$actionbar->setActions($actions);
		$frameResponseObject->addWidget($actionbar);

		// display configuration table
		$content->setCurrentBlock("BLOCK_TCR_CONFIGURATION");
		$content->setVariable("TCR_OPTIONS", "Thesen-Kritik-Replik-Verfahren Konfiguration");
		$content->setVariable("TITLE_LABEL", "Titel");
		$content->setVariable("TITLE_VALUE", $TCR->get_attribute("OBJ_DESC"));
		$content->setVariable("ROUNDS_LABEL", "Runden");
		$content->setVariable("ROUNDS_VALUE", $TCR->get_attribute("TCR_ROUNDS"));
		$content->setVariable("GROUP_LABEL", "Arbeitsgruppe");
		$content->setVariable("GROUP_VALUE", $groupname);
		
		// user management
		$content->setVariable("USERS_LABEL", "Benutzerverwaltung");
		$content->setVariable("USER_LABEL", "Name");
		$content->setVariable("ADMIN_LABEL", "Administrator");
		$content->setVariable("MEMBER_LABEL", "Teilnehmer");
		usort($members, "sort_workplans");
		foreach ($members as $member) {
			if ($member instanceof \steam_user) {
				$content->setCurrentBlock("BLOCK_USER");
				$content->setVariable("USER_NAME", $member->get_full_name() . " (" . $member->get_name() . ")");
				$content->setVariable("USER_ID", $member->get_id());
				if (in_array($member->get_id(), $admins)) {
					$content->setVariable("ADMIN_CHECKED", "checked");
					if ($member->get_id() == $TCR->get_creator()->get_id()) {
						$content->setVariable("ADMIN_DISABLED", "disabled");
					}
					$content->setVariable("FORMER_ADMIN", "on");
				} else {
					$content->setVariable("FORMER_ADMIN", "off");
				}
				if (in_array($member->get_id(), $users)) {
					$content->setVariable("MEMBER_CHECKED", "checked");
					$content->setVariable("FORMER_MEMBER", "on");
				} else {
					$content->setVariable("FORMER_MEMBER", "off");
				}
				$content->parse("BLOCK_USER");
			}
		}
		$content->setVariable("SAVE_CHANGES", "Änderungen speichern");
		$content->parse("BLOCK_TCR_CONFIGURATION");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
			array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
			array("name" => "Konfiguration")
		));
		return $frameResponseObject;
	}
}
?>
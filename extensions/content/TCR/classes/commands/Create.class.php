<?php
namespace TCR\Commands;
class Create extends \AbstractCommand implements \IFrameCommand, \IAjaxCommand {
	
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
		if (!isset($this->params["group_course"])) {
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
			$ajaxResponseObject->addWidget($rawWidget);
			return $ajaxResponseObject;
		}
		
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		if ($this->params["group_course"] == 1) {
			// course
			if (!(isset($this->params["course"]))) {
				$rawWidget = new \Widgets\RawHtml();
				$rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
				$ajaxResponseObject->addWidget($rawWidget);
				return $ajaxResponseObject;
			}
			$course = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["course"]);
			$subgroups = $course->get_subgroups();
			foreach ($subgroups as $subgroup) {
				if ($subgroup->get_name() == "staff") {
					$staff = $subgroup->get_members();
					$admins = array();
					foreach ($staff as $staffMember) {
						if ($staffMember instanceof \steam_user) {
							array_push($admins, $staffMember->get_id());
						}
					}
				} else if ($subgroup->get_name() == "learners") {
					$group = $subgroup;
				}
			}
		} else {
			// group
			if (!(isset($this->params["group"]))) {
				$rawWidget = new \Widgets\RawHtml();
				$rawWidget->setHtml("Error: Kurs oder Gruppe auswählen");
				$ajaxResponseObject->addWidget($rawWidget);
				return $ajaxResponseObject;
			}
			$group = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->params["group"]);
			$admins = array($user->get_id());
		}
		
		if (intval($this->params["rounds"]) == 0) {
			$rawWidget = new \Widgets\RawHtml();
			$rawWidget->setHtml("Error: Rundenzahl bitte als Integer eingeben.");
			$ajaxResponseObject->addWidget($rawWidget);
			return $ajaxResponseObject;
		}
		// create data structure and set access rights
		$TCR = \steam_factory::create_room($GLOBALS["STEAM"]->get_id(), $this->params["title"], $group->get_workroom(), $this->params["title"]);
        \steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "theses", $TCR, "container for theses");
		\steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "reviews", $TCR, "container for reviews");
		\steam_factory::create_container($GLOBALS["STEAM"]->get_id(), "responses", $TCR, "container for responses");
		$TCR->set_attribute("OBJ_TYPE", "TCR_CONTAINER");
		$TCR->set_attribute("TCR_ROUNDS", $this->params["rounds"]);
		$TCR->set_attribute("TCR_USERS", array());
		$TCR->set_attribute("TCR_ADMINS", $admins);
		$TCR->set_attribute("TCR_GROUP", $group);
		$TCR->set_sanction_all($group);
		
		$ajaxResponseObject->setStatus("ok");
		$jswrapper = new \Widgets\JSWrapper();
		$jswrapper->setJs(<<<END
		closeDialog();
END
		);
		$ajaxResponseObject->addWidget($jswrapper);
		
		return $ajaxResponseObject;
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$TCR = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$TCRExtension = \TCR::getInstance();
		$TCRExtension->addCSS();
		$TCRExtension->addJS();
		$content = $TCRExtension->loadTemplate("tcr_create.template.html");
		$kindOfDocument = $this->params[2];
		
		// display create element dialog
		$content->setCurrentBlock("BLOCK_CREATE_ELEMENT");
		if ($kindOfDocument == 0) {
			$create_label = "Neue These erstellen";
		} else if ($kindOfDocument == 1) {
			$create_label = "Neue Kritik erstellen";
			$thesisID = $this->params[3];
			$content->setVariable("ELEMENT_ID", $thesisID);
		} else {
			$create_label = "Neue Replik erstellen";
			$reviewID = $this->params[3];
			$content->setVariable("ELEMENT_ID", $reviewID);
		}
		$content->setVariable("CREATE_LABEL", $create_label);
		$content->setVariable("TITLE_LABEL", "Titel");
		$content->setVariable("DESC_LABEL", "Untertitel / Beschreibung");
		$content->setVariable("PLAIN_LABEL", "Text-Dokument erstellen");
		$content->setVariable("FILE_LABEL", "Datei hochladen");
		$content->setVariable("CONTENT_LABEL", "Inhalt");
		$content->setVariable("SUBMIT_CREATE", $create_label);
		$content->setVariable("BACK_LABEL", "Zurück");
		$content->setVariable("BACK_URL", $TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id . "/" . $this->params[2]);
		$content->setVariable("KIND_VALUE", $kindOfDocument);
		$content->setVariable("ROUND_VALUE", $this->params[1]);
		// max file size message
		$max_file_size = parse_filesize(ini_get('upload_max_filesize'));
		$max_post_size = parse_filesize(ini_get('post_max_size'));
		if ($max_post_size > 0 && $max_post_size < $max_file_size) {
			$max_file_size = $max_post_size;
		}
		$content->setVariable("UPLOAD_MAXSIZE", str_replace("%SIZE", readable_filesize($max_file_size), gettext("The maximum allowed file size is %SIZE.")));
		$content->parse("BLOCK_CREATE_ELEMENT");
		
		$group = $TCR->get_attribute("TCR_GROUP");
		if ($group->get_name() == "learners") {
			$parent = $group->get_parent_group();
			$courseOrGroup = "Kurs: " . $parent->get_attribute("OBJ_DESC") . " (" . $parent->get_name() . ")";
			$courseOrGroupUrl = PATH_URL . "semester/" . $parent->get_id();
		} else {
			$courseOrGroup = "Gruppe: " . $group->get_name();
			$courseOrGroupUrl = PATH_URL . "groups/" . $group->get_id();
		}
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
			array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $this->id),
			array("name" => "Private Dokumente", "link" => $TCRExtension->getExtensionUrl() . "privateDocuments/" . $this->id),
			array("name" => $create_label)
		));
		return $frameResponseObject;
	}
}
?>
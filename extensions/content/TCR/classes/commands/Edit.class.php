<?php
namespace TCR\Commands;
class Edit extends \AbstractCommand implements \IFrameCommand {
	
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
		$element = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$path = $element->get_path();
		$path = substr($path, 0, strrpos($path, "/"));
		$type = substr($path, strrpos($path, "/")+1, strlen($path));
		$TCR = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), substr($path, 0, strrpos($path, "/")));
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$TCRExtension = \TCR::getInstance();
		$TCRExtension->addCSS();
		$TCRExtension->addJS();
		$content = $TCRExtension->loadTemplate("tcr_edit.template.html");
		
		// determine the type of element to be edited
		if ($type == "theses") {
			$edit_label = "These bearbeiten";
			$kind = 0;
		} else if ($type == "reviews") {
			$edit_label = "Kritik bearbeiten";
			$kind = 1;
		} else {
			$edit_label = "Replik bearbeiten";
			$kind = 2;
		}
		
		// display element edit form
		$content->setCurrentBlock("BLOCK_EDIT_ELEMENT");
		$content->setVariable("EDIT_LABEL", $edit_label);
		$content->setVariable("TITLE_LABEL", "Titel");
		$content->setVariable("TITLE_VALUE", $element->get_name());
		$content->setVariable("DESC_LABEL", "Untertitel / Beschreibung");
		$content->setVariable("DESC_VALUE", $element->get_attribute("OBJ_DESC"));
		$content->setVariable("CONTENT_LABEL", "Inhalt");
		// if document is plain text display text area to edit it
		if ($element->get_attribute("DOC_MIME_TYPE") == "text/plain") {
			$content->setVariable("DISPLAY_IMG", "none");
			$content->setVariable("CONTENT_VALUE", $element->get_content());
			$content->setVariable("TEXTFIELD_VALUE", "Texteingabe");
			$content->setVariable("UPLOAD_VALUE", "Datei hochladen");
			$content->setVariable("SHOW_RADIO_FILE", "none");
		} else {
		// if document is of another mime type display upload dialog
			$content->setVariable("DISPLAY_TEXT", "none");
			$content->setVariable("ASSET_URL", $TCRExtension->getAssetUrl());
			$content->setVariable("DOWNLOAD_URL", PATH_URL . "download/Document/" . $this->id);
			$content->setVariable("DOWNLOAD_TITLE", "Datei herunterladen");
			$content->setVariable("KEEP_VALUE", "Aktuelle Datei beibehalten");
			$content->setVariable("UPLOAD_VALUE", "Neue Datei hochladen");
			$content->setVariable("TEXTFIELD_VALUE", "Neues Textdokument erstellen");
			$content->setVariable("SHOW_RADIO_TEXT", "none");
		}
		$content->setVariable("TCR_WARNING", "Achtung! Der vorherige Inhalt der These wird beim Abschicken des Formulars überschrieben.");
		$content->setVariable("BACK_LABEL", "Zurück");
		$content->setVariable("BACK_URL", $TCRExtension->getExtensionUrl() . "privateDocuments/" . $TCR->get_id() . "/" . $kind);
		$content->setVariable("SUBMIT_EDIT", "Änderungen speichern");
		$content->setVariable("ID_VALUE", $this->id);
		$content->setVariable("KIND_VALUE", $kind);
		$content->setVariable("ROUND_VALUE", $element->get_attribute("TCR_ROUND"));
		
		// max file size message
		$max_file_size = parse_filesize(ini_get('upload_max_filesize'));
		$max_post_size = parse_filesize(ini_get('post_max_size'));
		if ($max_post_size > 0 && $max_post_size < $max_file_size) {
			$max_file_size = $max_post_size;
		}
		$content->setVariable("UPLOAD_MAXSIZE", str_replace("%SIZE", readable_filesize($max_file_size), gettext("The maximum allowed file size is %SIZE.")));
		$content->parse("BLOCK_EDIT_ELEMENT");
		
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
			array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $TCR->get_id()),
			array("name" => "Private Dokumente", "link" => $TCRExtension->getExtensionUrl() . "privateDocuments/" . $TCR->get_id()),
			array("name" => $edit_label)
		));
		return $frameResponseObject;
	}
}
?>
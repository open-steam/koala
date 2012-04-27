<?php
namespace TCR\Commands;
class View extends \AbstractCommand implements \IFrameCommand {
	
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
		$released = $element->get_attribute("TCR_RELEASED");
		$content = $TCRExtension->loadTemplate("tcr_view.template.html");
	
		// determine the type of element to be displayed
		if ($type == "theses") {
			$kind = 0;
			$view_label = "These anzeigen";
			$edit_label = "These bearbeiten";
			$release_label = "These veröffentlichen";
		} else if ($type == "reviews") {
			$kind = 1;
			$view_label = "Kritik anzeigen";
			$edit_label = "Kritik bearbeiten";
			$release_label = "Kritik veröffentlichen";
		} else {
			$kind = 2;
			$view_label = "Replik anzeigen";
			$edit_label = "Replik bearbeiten";
			$release_label = "Replik veröffentlichen";
		}
		
		// display element view
		$content->setCurrentBlock("BLOCK_EDIT_ELEMENT");
		$content->setVariable("EDIT_LABEL", $edit_label);
		$content->setVariable("PARAMS_AJAX", "{ id : ". $element->get_id() . ", tcr : " . $TCR->get_id() . ", type : " . $kind . ", action : 'edit' }");
		$content->setVariable("RELEASE_LABEL", $release_label);
		$content->setVariable("RELEASE_ID", $element->get_id());
		$content->setVariable("RELEASE_TYPE", $kind);
		$content->setVariable("RELEASE_TCR_ID", $TCR->get_id());
		$content->setVariable("TITLE_VALUE", $element->get_attribute("OBJ_DESC"));
		$content->setVariable("CONTENT_VALUE", $element->get_content());
		$author = $element->get_creator();
		$pic_id = $author->get_attribute("OBJ_ICON")->get_id();
		$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/42/56";		
		$content->setVariable("AUTHOR_NAME", $author->get_full_name());
		$content->setVariable("AUTHOR_PIC", $pic_link);
		$content->setVariable("AUTHOR_URL", PATH_URL . "user/index/" . $author->get_name());
		$content->setVariable("DOCUMENT_RELEASED_LABEL", "Veröffentlichung:");
		$content->setVariable("DOCUMENT_RELEASED", date("d.m.Y H:i:s", $element->get_attribute("TCR_RELEASED")));
		$content->setVariable("FILE_LABEL", "Angehängte Dateien:");
		$content->setVariable("UPLOAD_FILES", "Weitere Dateien anhängen");
		$content->setVariable("PARAMS_AJAX_FILE", "{ id : ". $element->get_id() . ", tcr : " . $TCR->get_id() . ", type : " . $kind . ", action : 'upload' }");
		
		// display added files
		$filecontainer = $element->get_attribute("TCR_FILES");
		$nofiles = false;
		if ($filecontainer != "0") {
			$container = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $filecontainer);
			$files = $container->get_inventory();
			foreach ($files as $file) {
				$content->setCurrentBlock("BLOCK_FILE");
				$content->setVariable("FILE_NAME", $file->get_name());
				$content->setVariable("FILE_URL", PATH_URL . "download/Document/" . $file->get_id());
				$content->setVariable("FILE_TIME", "(hochgeladen am " . date("d.m.Y H:i:s", $file->get_attribute("OBJ_CREATION_TIME")) . ")");
				$content->setVariable("FILE_ID", $file->get_id());
				$content->setVariable("ASSET_URL", $TCRExtension->getAssetUrl() . "icons");
				$content->setVariable("DELETE_TITLE", "Datei löschen");
				if ($released != 0) {
					$content->setVariable("ELEMENT_RELEASED", "none");
				}
				$content->parse("BLOCK_FILE");
			}
			if (count($files) == 0) {
				$nofiles = true;
			}
		} else {
			$nofiles = true;
		}
		if ($nofiles && $released != 0) {
			$content->setVariable("DISPLAY_FILES", "none");
		} 
		
		// do not display some parts depending on if the document is released or not
		if ($released == 0) {
			$content->setVariable("ELEMENT_NOT_RELEASED", "none");
			$content->setVariable("NOT_RELEASED_COLSPAN", "colspan='2'");
		} else {
			$content->setVariable("ELEMENT_RELEASED", "none");
			if (!$nofiles) {
				$content->setVariable("RELEASED_ROWSPAN", "rowspan='2'");
			}
		}
		
		// display comments
		$content->setVariable("COMMENTS_LABEL", "Kommentare");
		$annotations = $element->get_annotations();
		usort($annotations, "sortRepliesByDate");
		if (count($annotations) == 0) {
			$content->setCurrentBlock("BLOCK_NO_COMMENTS");
			$content->setVariable("NO_COMMENTS", "Keine Kommentare vorhanden.");
			$content->parse("BLOCK_NO_COMMENTS");
		} else {
			for ($count = 0; $count < count($annotations); $count++) {
				$author = $annotations[$count]->get_creator();
				$content->setCurrentBlock("BLOCK_COMMENT");
				$content->setVariable("COMMENT_CONTENT", nl2br($annotations[$count]->get_content()));
				$content->setVariable("COMMENT_CONTENT_NOBR", $annotations[$count]->get_content());
				$content->setVariable("COMMENT_TITLE", $annotations[$count]->get_name());
				$content->setVariable("COMMENT_ID", $annotations[$count]->get_id());
				$content->setVariable("ASSET", $TCRExtension->getAssetUrl());
				$content->setVariable("EDIT_COMMENT", "Kommentar bearbeiten");
				// if user is not the author dont display edit button
				if ($author->get_id() != $user->get_id()) {
					$content->setVariable("SHOW_COMMENT_EDIT", "none");
				}
				$pic_id = $author->get_attribute("OBJ_ICON")->get_id();
				$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/15/20";
				$content->setVariable("COMMENT_AUTHOR", "von <img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $author->get_name() . ">" . $author->get_full_name() . "</a>");
				$content->setVariable("COMMENT_DATE", "am " . date("d.m.Y H:i:s", (int) $annotations[$count]->get_attribute("OBJ_CREATION_TIME")));
				$content->parse("BLOCK_COMMENT");
			}
		}
		$content->setVariable("CREATE_COMMENT", "Kommentar hinzufügen");
		$content->setVariable("COMMENT_ELEMENT_ID", $element->get_id());
		$content->setVariable("COMMENT_TCR_ID", $TCR->get_id());
		$content->setVariable("TITLE_LABEL", "Titel:");
		$content->setVariable("NEW_TITLE", "Kommentar");
		$content->setVariable("CONTENT_LABEL", "Kommentar:");
		$content->setVariable("CANCEL_LABEL", "Abbrechen");
		$content->setVariable("EDIT_COMMENT", "Kommentar bearbeiten");
		$content->setVariable("EDIT_COMMENT_SUBMIT", "Änderungen speichern");
		
		if (getenv("HTTP_REFERER") == "" || getenv("HTTP_REFERER") == PATH_URL . "tcr/view/" . $this->id) {
			$content->setVariable("BACK_URL", (PATH_URL . "tcr/Index/" . $TCR->get_id()));
			$content->setVariable("BACK_LABEL", "Zurück zur Übersicht");
		} else {
			$content->setVariable("BACK_URL", getenv("HTTP_REFERER"));
			$content->setVariable("BACK_LABEL", "Zurück");
		}
		$content->parse("BLOCK_EDIT_ELEMENT");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $TCR->get_id()),
			array("name" => $view_label)
		));
		return $frameResponseObject;
	}
}
?>
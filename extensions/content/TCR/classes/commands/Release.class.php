<?php
namespace TCR\Commands;
class Release extends \AbstractCommand implements \IFrameCommand {
	
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
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$path = $element->get_path();
		$path = substr($path, 0, strrpos($path, "/"));
		$type = substr($path, strrpos($path, "/")+1, strlen($path));
		$TCR = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), substr($path, 0, strrpos($path, "/")));
		$TCRExtension = \TCR::getInstance();
		$TCRExtension->addCSS();
		$TCRExtension->addJS();
		$content = $TCRExtension->loadTemplate("tcr_release.template.html");
		$members = $TCR->get_attribute("TCR_USERS");
		sort($members);
		$critics = $element->get_attribute("TCR_REVIEWS");
		$group = $TCR->get_attribute("TCR_GROUP");
		$private = 1;
		
		// determine where the user is coming from
		$referer = getenv("HTTP_REFERER");
		if (strpos($referer, "Index") !== false) {
			$backurl = $TCRExtension->getExtensionUrl() . "Index/" . $TCR->get_id();
			$referer = 1;
		} else if (strpos($referer, "documents") !== false) {
			$backurl = $TCRExtension->getExtensionUrl() . "documents/" . $TCR->get_id();
			$referer = 2;
		} else if (strpos($referer, "privateDocuments") !== false) {
			$backurl = $TCRExtension->getExtensionUrl() . "privateDocuments/" . $TCR->get_id();
			$referer = 3;
		}
		
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			// if a new comment was submitted
			if (isset($_POST["add_comment"])) {
				$new_comment = \steam_factory::create_textdoc($GLOBALS[ "STEAM" ]->get_id(), $_POST["title"], stripslashes($_POST["content"]));
				$new_comment->set_read_access($group);
				$new_comment->set_write_access($group);
				$element->add_annotation($new_comment);
			}
			
			// if a comment edit got submitted
			if (isset($_POST["edit_comment"])) {
				$comment = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_POST["edit_id"]);
				$comment->set_content($_POST["content"]);
				$comment->set_name($_POST["title"]);
			}
			
			// determine where the user is coming from
			$referer = $_POST["referer"];
			if ($referer == 1) {
				$backurl = $TCRExtension->getExtensionUrl() . "Index/" . $TCR->get_id();
			} else if ($referer == 2) {
				$backurl = $TCRExtension->getExtensionUrl() . "documents/" . $TCR->get_id();
			} else if ($referer == 3) {
				$backurl = $TCRExtension->getExtensionUrl() . "privateDocuments/" . $TCR->get_id();
			}
		}
		
		// display dialog
		$content->setCurrentBlock("BLOCK_RELEASE_ELEMENT");
		if ($type == "theses") {
			if (is_array($critics) && count($critics) > 0) {
				// display already released thesis
				$release_label = "These anzeigen";
				$content->setVariable("SHOW_CRITICS", "none");
				$content->setVariable("SHOW_RELEASE", "none");
				$private = 0;
				$released = 1;
			} else {
				// release dialog
				$release_label = "These veröffentlichen";
				$released = 0;
			}
			$kind = 0;
			$kindWord = "These";
		} else if ($type == "reviews") {
			$released = $element->get_attribute("TCR_RELEASED");
			if ($released != 0) {
				// display already released review
				$release_label = "Kritik anzeigen";
				$content->setVariable("SHOW_RELEASE", "none");
				$private = 0;
			} else {
				// release dialog
				$release_label = "Kritik veröffentlichen";
			}
			$content->setVariable("SHOW_CRITICS", "none");
			$kind = 1;
			$kindWord = "Kritik";
		} else {
			$released = $element->get_attribute("TCR_RELEASED");
			if ($released != 0) {
				// display already released response
				$release_label = "Replik anzeigen";
				$content->setVariable("SHOW_RELEASE", "none");
				$private = 0;
			} else {
				// release dialog
				$release_label = "Replik veröffentlichen";
			}
			$content->setVariable("SHOW_CRITICS", "none");
			$kind = 2;
			$kindWord = "Replik";
		}
		
		if ($released == 0 && $referer == 3) {
			$backurl = $TCRExtension->getExtensionUrl() . "privateDocuments/" . $TCR->get_id() . "/" . $kind;
		}
		
		$content->setVariable("SUBMIT_URL", $TCRExtension->getExtensionUrl() . "privateDocuments/" . $TCR->get_id() . "/" . $kind);
		$content->setVariable("RELEASE_LABEL", $release_label);
		$content->setVariable("TITLE_LABEL", "Titel");
		$content->setVariable("TITLE_VALUE", $element->get_name());
		$content->setVariable("DESC_LABEL", "Untertitel / Beschreibung");
		$content->setVariable("DESC_VALUE", $element->get_attribute("OBJ_DESC"));
		$content->setVariable("AUTHOR_LABEL", "Autor");
		$author = $element->get_creator();
		$content->setVariable("AUTHOR_VALUE", $author->get_full_name() . " (" . $author->get_name() . ")");
		$content->setVariable("CONTENT_LABEL", "Inhalt");
		if ($element->get_attribute("DOC_MIME_TYPE") == "text/plain") {
			// if element is plain text display content directly
			$content->setVariable("DISPLAY_DOWNLOAD", "none");
			$content->setVariable("CONTENT_VALUE", nl2br($element->get_content()));
		} else {
			// if element is of another mime type display download dialog
			$content->setVariable("DISPLAY_TEXT", "none");
			$content->setVariable("ASSET_URL", $TCRExtension->getAssetUrl());
			$content->setVariable("DOWNLOAD_URL", PATH_URL . "download/Document/" . $this->id);
			$content->setVariable("DOWNLOAD_TITLE", "Datei herunterladen");
		}
		// display select critic dialog
		$content->setVariable("CRITICS_LABEL", "Kritiker(in)");
		$radioSelect = 0;
		foreach ($members as $memberID) {
			$member = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $memberID);
			$content->setCurrentBlock("BLOCK_CRITIC");
			$content->setVariable("CRITIC_ID", $member->get_id());
			$content->setVariable("CRITIC_NAME", $member->get_full_name());
			if ($radioSelect == 0) {
				$content->setVariable("CRITIC_CHECKED", "checked");
				$radioSelect++;
			}
			$content->parse("BLOCK_CRITIC");
		}
		$content->setVariable("RELEASE_WARNING", "Wenn Sie die " . $kindWord ." veröffentlichen, können Sie sie nicht mehr ändern oder löschen.");
		$content->setVariable("SUBMIT_RELEASE", "Veröffentlichen");
		$content->setVariable("BACK_LABEL", "Zurück");
		$content->setVariable("BACK_URL", $backurl);
		$content->setVariable("KIND_VALUE", $kind);
		$content->setVariable("ID_VALUE", $this->id);
		$content->parse("BLOCK_RELEASE_ELEMENT");
		
		// display comments
		if ($released != 0) {
			$comments = $element->get_annotations();
			usort($comments, "sortRepliesByDate");
			$content->setCurrentBlock("BLOCK_TCR_COMMENTS");
			$content->setVariable("COMMENTS_LABEL", "Kommentare");
			if (count($comments) == 0) {
				$content->setCurrentBlock("BLOCK_NO_COMMENTS");
				$content->setVariable("NO_COMMENTS", "Es sind noch keine Kommentare zu diesem Dokument vorhanden.");
				$content->parse("BLOCK_NO_COMMENTS");
			} else {
				for ($count = 0; $count < count($comments); $count++) {
					$author = $comments[$count]->get_creator();
					$content->setCurrentBlock("BLOCK_COMMENT");
					$content->setVariable("COMMENT_CONTENT", nl2br($comments[$count]->get_content()));
					$content->setVariable("COMMENT_CONTENT_NOBR", $comments[$count]->get_content());
					$content->setVariable("COMMENT_TITLE", $comments[$count]->get_name());
					$content->setVariable("COMMENT_ID", $comments[$count]->get_id());
					$content->setVariable("ASSET", $TCRExtension->getAssetUrl());
					$content->setVariable("EDIT_COMMENT", "Kommentar bearbeiten");
					// if user is not the author and admin options are not shown dont display edit button
					if ($author->get_id() != $user->get_id()) {
						$content->setVariable("SHOW_COMMENT_EDIT", "none");
					}
					$content->setVariable("COMMENT_AUTHOR", "von " . $author->get_name() . " (" . $author->get_full_name() . ")");
					$content->setVariable("COMMENT_DATE", "am " . date("d.m.Y H:i", (int) $comments[$count]->get_attribute("OBJ_CREATION_TIME")));
					$content->parse("BLOCK_COMMENT");
				}
			}
			$content->parse("BLOCK_TCR_COMMENTS");
			
			$content->setCurrentBlock("BLOCK_CREATE_COMMENT");
			$content->setVariable("CREATE_COMMENT", "Kommentar hinzufügen");
			$content->setVariable("BACKURL_LABEL", "Zurück");
			$content->setVariable("BACKURL", $backurl);
			$content->setVariable("TITLE_LABEL_COMMENT", "Titel:");
			$content->setVariable("CONTENT_LABEL_COMMENT", "Kommentar:");
			$content->setVariable("BACK_LABEL_COMMENT", "Abbrechen");
			$content->setVariable("EDIT_COMMENT", "Kommentar bearbeiten");
			$content->setVariable("EDIT_COMMENT_SUBMIT", "Änderungen speichern");
			$content->setVariable("REFERER_VALUE", $referer);
			$content->parse("BLOCK_CREATE_COMMENT");
		} else {
			$content->setVariable("DISPLAY_COMMENTS", "none");
		}
		
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
		if ($private == 1) {
			$frameResponseObject->setHeadline(array(
				array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
				array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $TCR->get_id()),
				array("name" => "Private Dokumente", "link" => $TCRExtension->getExtensionUrl() . "privateDocuments/" . $TCR->get_id()),
				array("name" => $release_label)
			));
		} else {
			$frameResponseObject->setHeadline(array(
				array("name" => $courseOrGroup , "link" => $courseOrGroupUrl), 
				array("name" => "Thesen-Kritik-Replik-Verfahren", "link" => $TCRExtension->getExtensionUrl() . "Index/" . $TCR->get_id()),
				array("name" => $release_label)
			));
		}
		return $frameResponseObject;
	}
}
?>
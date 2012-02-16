<?php
namespace Pyramiddiscussion\Commands;
class ViewPosition extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;
	private $pyramiddiscussion;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->pyramiddiscussion = $this->params[0]: "";
		isset($this->params[1]) ? $this->id = $this->params[1]: "";
	}

	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		$user = $GLOBALS["STEAM"]->get_current_steam_user();
		$pyramidPosition = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$positionGroup = $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_RELGROUP");
		$pyramidRoom = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->pyramiddiscussion);
		$phase = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_ACTCOL");
		$basegroup = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_BASEGROUP");
		$admingroup = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_ADMINGROUP");
		$adminconfig = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_ADMINCONFIG");
		$pyramiddiscussionExtension = \Pyramiddiscussion::getInstance();
		$pyramiddiscussionExtension->addCSS();
		$pyramiddiscussionExtension->addJS();
		$content = $pyramiddiscussionExtension->loadTemplate("pyramiddiscussion_viewposition.template.html");
		
		// if a new comment was submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_comment"])) {
			$new_comment = \steam_factory::create_textdoc($GLOBALS[ "STEAM" ]->get_id(), $_POST["title"], stripslashes($_POST["content"]));
			$new_comment->set_read_access($basegroup);
			$new_comment->set_read_access($admingroup);
			$new_comment->set_write_access($basegroup);
			$new_comment->set_write_access($admingroup);
			$read_states = array();
			$read_states[$user->get_id()] = 1;
			$new_comment->set_attribute("PYRAMIDDISCUSSION_COMMENT_READ_STATES", $read_states);
			$pyramidPosition->add_annotation($new_comment);
		}
		
		// if a comment edit got submitted
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit_comment"])) {
			$comment = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_POST["edit_id"]);
			$comment->set_content($_POST["content"]);
			$comment->set_name($_POST["title"]);
			$read_states = array();
			$read_states[$user->get_id()] = 1;
			$comment->set_attribute("PYRAMIDDISCUSSION_COMMENT_READ_STATES", $read_states);
		}
		
		// check if user is admin and if adminoptions are shown
		$showadmin = 1;
		if ($admingroup->is_member($user)) {
			if(array_key_exists($user->get_id(), $adminconfig)) {
				$options = $adminconfig[$user->get_id()];
				if (isset($options["show_adminoptions"]) && $options["show_adminoptions"] == "false") {
					$showadmin = 0;
				}
			}
		} else $showadmin = 0;
		// get names of the users participating in this position
		$participants = $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_PARTICIPANT_MANAGEMENT");
		$positionMembers = $positionGroup->get_members();
		$names = "";
		$users = array();
		while (count($positionMembers) > 0) {
			$currentMember = array_pop($positionMembers);
			if ($currentMember instanceof \steam_user) {
				if (!isset($participants[$currentMember->get_id()])) {
					$participants[$currentMember->get_id()] = 0;
					$pyramidRoom->set_attribute("PYRAMIDDISCUSSION_PARTICIPANT_MANAGEMENT", $participants);
				}
				if ($participants[$currentMember->get_id()] >= $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_COLUMN") || $participants[$currentMember->get_id()] == 0) {
					array_push($users, $currentMember->get_id());
				}
			} else {
				$groupMembers = $currentMember->get_members();
				for ($count = 0; $count < count($groupMembers); $count++) {
					array_push($positionMembers, $groupMembers[$count]);
				}
			}
		}

		// determine if current user already read this position
		$read_position = 0;
		$read_position_states = $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_POS_READ_STATES");
		if (is_array($read_position_states)) {
			if (array_key_exists($user->get_id(), $read_position_states)) {
				$read_position = $read_position_states[$user->get_id()];
			}
		}
		if ($pyramidPosition->get_content() == "0" || $pyramidPosition->get_content() == "") {
			$read_position = 1;
		}
		// display current position
		$content->setCurrentBlock("BLOCK_PYRAMID_POSITION");
		if ($pyramidPosition->get_attribute("PYRAMIDDISCUSSION_POS_TITLE") != "0" && $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_POS_TITLE") != "") {
			$content->setVariable("PYRAMID_POSITION", $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_POS_TITLE"));	
		} else {
			$content->setVariable("PYRAMID_POSITION", "Position_" . $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_COLUMN") . "_" . $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_ROW"));	
		}
		$positionPhase = $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_COLUMN");
		$positionRow = $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_ROW");
		if ($positionPhase != 1) {
			$previous1 = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $pyramidRoom->get_path() . "/Position_" . ($positionPhase-1) . "_" . (($positionRow)*2-1));
			$previous2 = \steam_factory::get_object_by_name($GLOBALS["STEAM"]->get_id(), $pyramidRoom->get_path() . "/Position_" . ($positionPhase-1) . "_" . ($positionRow*2));
			$content->setVariable("PREVIOUS1_SHOW", "Position " . ($positionPhase-1) . "-" . (($positionRow)*2-1) . " anzeigen");
			$content->setVariable("PREVIOUS2_SHOW", "Position " . ($positionPhase-1) . "-" . ($positionRow*2) . " anzeigen");
			$content->setVariable("PREVIOUS1_HIDE", "Position " . ($positionPhase-1) . "-" . (($positionRow)*2-1) . " ausblenden");
			$content->setVariable("PREVIOUS2_HIDE", "Position " . ($positionPhase-1) . "-" . ($positionRow*2) . " ausblenden");
			$content->setVariable("PREVIOUS1_CONTENT", $previous1->get_content());
			$content->setVariable("PREVIOUS2_CONTENT", $previous2->get_content());
		} else {
			$content->setVariable("DISPLAY_PREVIOUS", "none");
		}
		if ($read_position == 0) {
			$content->setVariable("POSITION_NEW", "(neu)");
			// current user has now read this position
			if (is_array($read_position_states)) {
				$read_position_states[$user->get_id()] = 1;
			} else {
				$read_position_states = array();
				$read_position_states[$user->get_id()] = 1;
			}
			$pyramidPosition->set_attribute("PYRAMIDDISCUSSION_POS_READ_STATES", $read_position_states);
		} 
		$content->setVariable("ASSETURL", $pyramiddiscussionExtension->getAssetUrl());
		$content->setVariable("EDIT_POSITION", "Position bearbeiten");
		$content->setVariable("PARAMS_AJAX", "{ id : ". $pyramidPosition->get_id() . ", action : 'edit' }");
		// do not display edit function if user is not member of this position and adminoptions are not shown
		if (!in_array($user->get_id(), $users) && $showadmin == 0) {
			$content->setVariable("DISPLAY_EDIT", "none");
		}
		// do not display edit function if position is from a former phase and adminoptions are not shown
		if ($phase > $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_COLUMN") && $showadmin == 0) {
			$content->setVariable("DISPLAY_EDIT", "none");
		}
		if ($phase == $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAXCOL")+2) {
			$content->setVariable("DISPLAY_EDIT", "none");
		}
		$content->setVariable("POSITION_TITLE", $pyramidPosition->get_attribute("OBJ_DESC"));
		if ($pyramidPosition->get_content() != "0") {
			$content->setVariable("POSITION_CONTENT", nl2br($pyramidPosition->get_content()));
		}
		$content->setVariable("POSITION_AUTHORS", "Autoren:");
		foreach ($users as $currentAuthor) {
			$content->setCurrentBlock("BLOCK_POSITION_AUTHOR");
			$currentAuthor = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $currentAuthor);
			$pic_id = $currentAuthor->get_attribute("OBJ_ICON")->get_id();
			$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/42/56";
			$content->setVariable("AUTHOR_URL", PATH_URL . "user/index/" . $currentAuthor->get_name());
			$content->setVariable("AUTHOR_PIC", $pic_link);
			$content->setVariable("AUTHOR_NAME", $currentAuthor->get_full_name());
			$content->parse("BLOCK_POSITION_AUTHOR");
		}
		$content->setVariable("POSITION_LAST_CHANGED", "Zuletzt geändert:");
		if ($pyramidPosition->get_attribute("DOC_LAST_MODIFIED") != 0) {
			$content->setVariable("POSITION_LAST_CHANGED_DATE", "am " . date("d.m.Y H:i", (int) $pyramidPosition->get_attribute("DOC_LAST_MODIFIED")));
		}
		if (is_object($pyramidPosition->get_attribute("DOC_USER_MODIFIED"))) { 
			$content->setVariable("POSITION_LAST_CHANGED_USER", "von " . $pyramidPosition->get_attribute("DOC_USER_MODIFIED")->get_full_name());
		} 
		// display comments
		$content->setVariable("COMMENTS_LABEL", "Kommentare");
		$annotations = $pyramidPosition->get_annotations();
		usort($annotations, "sortRepliesByDate");
		if (count($annotations) == 0) {
			$content->setCurrentBlock("BLOCK_NO_COMMENTS");
			$content->setVariable("NO_COMMENTS", "Keine Kommentare zu dieser Position vorhanden.");
			$content->parse("BLOCK_NO_COMMENTS");
		} else {
			for ($count = 0; $count < count($annotations); $count++) {
				$author = $annotations[$count]->get_creator();
				// determine if current user has read this comment
				$read_comment = 0;
				$read_comment_states = $annotations[$count]->get_attribute("PYRAMIDDISCUSSION_COMMENT_READ_STATES");
				if (is_array($read_comment_states)) {
					if (array_key_exists($user->get_id(), $read_comment_states)) {
						$read_comment = $read_comment_states[$user->get_id()];
					}
				}
				$content->setCurrentBlock("BLOCK_COMMENT");
				if ($read_comment == 0) {
					$content->setVariable("COMMENT_NEW", "(neu)");
					// current user has now read this comment
					if (is_array($read_comment_states)) {
						$read_comment_states[$user->get_id()] = 1;
					} else {
						$read_comment_states = array();
						$read_comment_states[$user->get_id()] = 1;
					}
					$annotations[$count]->set_attribute("PYRAMIDDISCUSSION_COMMENT_READ_STATES", $read_comment_states);
				}
				$content->setVariable("COMMENT_CONTENT", nl2br($annotations[$count]->get_content()));
				$content->setVariable("COMMENT_CONTENT_NOBR", $annotations[$count]->get_content());
				$content->setVariable("COMMENT_TITLE", $annotations[$count]->get_name());
				$content->setVariable("COMMENT_ID", $annotations[$count]->get_id());
				$content->setVariable("ASSET", $pyramiddiscussionExtension->getAssetUrl());
				$content->setVariable("EDIT_COMMENT", "Kommentar bearbeiten");
				// if user is not the author and admin options are not shown dont display edit button
				if ($author->get_id() != $user->get_id() && $showadmin == 0) {
					$content->setVariable("SHOW_COMMENT_EDIT", "none");
				}
				if ($phase == $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAXCOL")+2) {
					$content->setVariable("SHOW_COMMENT_EDIT", "none");
				}
				$pic_id = $author->get_attribute("OBJ_ICON")->get_id();
				$pic_link = ( $pic_id == 0 ) ? PATH_URL . "styles/standard/images/anonymous.jpg" : PATH_URL . "download/image/" . $pic_id . "/15/20";
				$content->setVariable("COMMENT_AUTHOR", "von <img style='vertical-align:middle;' src=" . $pic_link . ">&nbsp<a href=" . PATH_URL . "user/index/" . $author->get_name() . ">" . $author->get_full_name() . "</a>");
				$content->setVariable("COMMENT_DATE", "am " . date("d.m.Y H:i", (int) $annotations[$count]->get_attribute("OBJ_CREATION_TIME")));
				$content->parse("BLOCK_COMMENT");
			}
		}
		$content->setVariable("CREATE_COMMENT", "Kommentar hinzufügen");
		if ($phase == $pyramidRoom->get_attribute("PYRAMIDDISCUSSION_MAXCOL")+2) {
			$content->setVariable("DISPLAY_CREATE_COMMENT", "none");
		}
		$content->setVariable("TITLE_LABEL", "Titel:");
		$content->setVariable("NEW_TITLE", "Kommentar");
		$content->setVariable("CONTENT_LABEL", "Kommentar:");
		$content->setVariable("BACK_LABEL", "Abbrechen");
		$content->setVariable("EDIT_COMMENT", "Kommentar bearbeiten");
		$content->setVariable("EDIT_COMMENT_SUBMIT", "Änderungen speichern");
		$content->setVariable("PYRAMID_BACK", "Zurück zur Pyramide");
		$content->setVariable("PYRAMID_URL", $pyramiddiscussionExtension->getExtensionUrl() . "Index/" . $this->pyramiddiscussion);
		$content->parse("BLOCK_PYRAMID_POSITION");
		
		$rawWidget = new \Widgets\RawHtml();
		$rawWidget->setHtml($content->get());
		$frameResponseObject->addWidget($rawWidget);
		$frameResponseObject->setHeadline(array(
			array("name" => "Pyramidendiskussion" , "link" => $pyramiddiscussionExtension->getExtensionUrl() . "Index/" . $this->pyramiddiscussion),
			array("name" => "Diskussionsphase " . $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_COLUMN") . " Position " . $pyramidPosition->get_attribute("PYRAMIDDISCUSSION_ROW")),
		));
		
		return $frameResponseObject;
	}
}
?>
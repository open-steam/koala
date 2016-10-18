<?php
namespace Wiki\Commands;
class Versions extends \AbstractCommand implements \IFrameCommand {

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
		require_once( PATH_LIB . "wiki_handling.inc.php" );

		$portal = \lms_portal::get_instance();
		$portal->initialize( GUEST_NOT_ALLOWED );

		// Disable caching
		// TODO: Work on cache handling. An enabled cache leads to bugs
		// if used with the wiki.
		\CacheSettings::disable_caching();

		$WikiExtension = \Wiki::getInstance();
		$WikiExtension->addCSS();
		$wiki_doc = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
		$wiki_container = $wiki_doc->get_environment();

		if (!($wiki_container->check_access_read())) {
				$errorHtml = new \Widgets\RawHtml();
				$errorHtml->setHtml("Das Wiki kann nicht angezeigt werden, da Sie nicht über die erforderlichen Leserechte verfügen.");
				$frameResponseObject->addWidget($errorHtml);
				return $frameResponseObject;
		}

		$wiki_html_handler = new \koala_wiki($wiki_container);
		$wiki_html_handler->set_admin_menu( "versions", $wiki_doc );

		$grp = $wiki_container->get_environment()->get_creator();
		if ($grp->get_name() == "learners" && $grp->get_attribute(OBJ_TYPE) == "course_learners") {
		  	$grp = $grp->get_parent_group();
		}

		if($wiki_container->get_attribute("UNIT_TYPE")){
		    $place = "units";
		}
		else{
		    $place = "communication";
		}

		$content = $WikiExtension->loadTemplate("wiki_versions.template.html" );

		$prev_versions = $wiki_doc->get_previous_versions();

		if(!is_array($prev_versions)){
			$prev_versions = array();
		}

		array_unshift($prev_versions, $wiki_doc);

		$no_versions = count( $prev_versions );

		$content->setCurrentBlock( "BLOCK_VERSION_LIST" );

		if(isset($_GET["markedfordiff"]) && !empty($_GET["markedfordiff"])){
			$uri_params = "?markedfordiff=" . $_GET["markedfordiff"];
		}

		$entry_name = str_replace ( ".wiki", "", $wiki_doc->get_identifier() );
		$start = 0;
		$end = count($prev_versions);
		$content->setVariable("LABEL_VERSIONS", gettext("Available Versions for the entry") . " \"" . h($entry_name) . "\"");

		$content->setVariable( "LABEL_VERSION_NUMBER", gettext("Version number"));
		$content->setVariable( "LABEL_SIZE", gettext("Size"));
		$content->setVariable( "LABEL_DATE", gettext("Modification date"));
		$content->setVariable( "LABEL_CREATOR", gettext("Modified by"));
		$content->setVariable( "LABEL_ACTION", gettext("Action"));

		// Use buffer for document attributes
		$attributes_tnr = array();
		for( $i = $start; $i < $end; $i++ ){
			$attributes_tnr[$prev_versions[$i]->get_id()] = $prev_versions[$i]->get_attributes( array( DOC_USER_MODIFIED, DOC_LAST_MODIFIED, DOC_VERSION, DOC_SIZE ), TRUE);
		}
		$attributes_result = $GLOBALS["STEAM"]->buffer_flush();

		// use buffer for author attributes
		$author_tnr = array();
		for( $i = $start; $i < $end; $i++ ){
			$author_tnr[$prev_versions[$i]->get_id()] = $attributes_result[$attributes_tnr[$prev_versions[$i]->get_id()]][DOC_USER_MODIFIED]->get_attributes( array(USER_FIRSTNAME, USER_FULLNAME, OBJ_NAME) , TRUE);
		}
		$author_result = $GLOBALS["STEAM"]->buffer_flush();

		for( $i = $start; $i < $end; $i++ ){
			$doc = $prev_versions[$i];
			$attributes = $attributes_result[$attributes_tnr[$doc->get_id()]];
			$last_author  = $author_result[$author_tnr[$doc->get_id()]];
			$content->setCurrentBlock( "BLOCK_VERSION" );

			if ($i % 2 == 1) {
  			$content->setVariable("CLASS", "class='white'");
  		}

			if ( $doc instanceof \steam_document ){
				$content->setVariable( "VALUE_SIZE", get_formatted_filesize( $doc->get_content_size() ) );
				$content->setVariable( "VALUE_CREATOR_LINK", PATH_URL . "user/index/" . $author_result[$author_tnr[$doc->get_id()]][OBJ_NAME] . "/" );
				$content->setVariable( "VALUE_CREATOR", h($last_author[ USER_FIRSTNAME ]) . " " . h($last_author[ USER_FULLNAME ]) );
				$content->setVariable( "VALUE_DATE", strftime( "%x %X", $attributes[ "DOC_LAST_MODIFIED" ] ) );

		    if ($doc->get_id() !== $wiki_doc->get_id()) {
	      	$content->setVariable( "VALUE_VERSION_LINK", PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/" . $doc->get_id() . "/" );
	      	$content->setVariable( "VALUE_VERSION_NUMBER", "Version " . h($attributes_result[$attributes_tnr[$doc->get_id()]][DOC_VERSION]) );
					if($wiki_container->check_access_write()){
						$content->setCurrentBlock("BLOCK_RECOVER");
	      		$content->setVariable( "VALUE_ACTION_RECOVER", "&raquo; " . gettext("Recover this version"));
	      		$content->setVariable( "VALUE_RECOVER_LINK", PATH_URL . "wiki/recover/" . $wiki_doc->get_id() . "/" . $doc->get_id() . "/");
						$content->parse("BLOCK_RECOVER");
					}
				} else {
			    $content->setVariable( "VALUE_VERSION_LINK", PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/" );
			    $content->setVariable( "VALUE_VERSION_NUMBER", "Version " . h($attributes_result[$attributes_tnr[$doc->get_id()]][DOC_VERSION]) . " (" . gettext("current") . ")" );
			  }

				if( isset( $_GET["markedfordiff"] ) && $_GET["markedfordiff"] == $doc->get_id()){
					$content->setVariable( "VALUE_ACTION_MARK", "&raquo; " . gettext("Currently marked for version compare"));
				}
				else{
					$content->setVariable( "VALUE_ACTION_MARK", "<a href=\"" . PATH_URL . "wiki/versions/" . $wiki_doc->get_id() . "/?markedfordiff=" . $doc->get_id() . "\">" . "&raquo; " . gettext("Mark for version compare") . "</a>");
				}
				if($attributes[DOC_VERSION] != 1){
					$content->setVariable( "VALUE_ACTION_DIFF", "&raquo; " . gettext("Compare to previous version") . " " . ($attributes[ DOC_VERSION ] - 1) );
					$content->setVariable( "VALUE_DIFF_LINK", PATH_URL . "wiki/compare/" . $wiki_doc->get_id() . "/" . $doc->get_id() . "/" . $prev_versions[$i+1]->get_id());
				}
				if(isset($_GET["markedfordiff"]) &&  $_GET["markedfordiff"] != $doc->get_id()){
					$marked = \steam_factory::get_object( $GLOBALS["STEAM"]->get_id(), $_GET["markedfordiff"] );
					$content->setVariable( "VALUE_ACTION_MARKED_DIFF", "<a href=\"" . PATH_URL . "wiki/compare/" . $wiki_doc->get_id() . "/" . $doc->get_id() . "/" . $_GET["markedfordiff"] . "\">" . "&raquo; " . gettext("Compare to marked version") . " " . $marked->get_version() . "</a>");
				}
/*
				$markedfordiff = "";
				if(isset($_GET["markedfordiff"]) && $_GET["markedfordiff"]){
					$markedfordiff = $_GET["markedfordiff"];
				}

				$prevVersionId = "";
				if($attributes[DOC_VERSION] != 1){
					$prevVersionId = $prev_versions[$i+1]->get_id();
				}

				$params = array(
					array("key" => "docId", "value" => $doc->get_id()),
					array("key" => "wikiDocId", "value" => $wiki_doc->get_id()),
					array("key" => "markedfordiff", "value" => $markedfordiff),
					array("key" => "docVersion", "value" => $attributes[DOC_VERSION]),
					array("key" => "prevVersionId", "value" => $prevVersionId)
				);

				$popupMenu = new \Widgets\PopupMenu();
				$popupMenu->setCommand("GetPopupMenuVersion");
				$popupMenu->setNamespace("Wiki");
				$popupMenu->setData($wiki_doc);
				$popupMenu->setElementId("wiki-overlay");
				$popupMenu->setParams($params);
				$content->setVariable("POPUPMENUANKER", $popupMenu->getHtml());
*/
			}

			//is user authorized to delete version?

			$content->setVariable( "MESSAGE_DELETION", "Diese Version wirklich löschen?" );
/*
			$current_user = \lms_steam::get_current_user();
			$admin_group = \steam_factory::get_group( $GLOBALS["STEAM"]->get_id(), "admin" );
			$isAdmin = ( is_object( $admin_group ) && $admin_group->is_member( $current_user ) );
			$usersEntry = $last_author["OBJ_NAME"] === $current_user->get_name();
			*/
			$notCurrentVersion = $doc->get_id() !== $wiki_doc->get_id();

			if($wiki_container->check_access_write() && $notCurrentVersion){
				$content->setVariable( "VALUE_ACTION_DELETE", "<a href=\"" . PATH_URL . "wiki/delete/version/" . $doc->get_id() . "\" onclick=\"return confirmDeletion();\">" . "&raquo; " . "Diese Version löschen" . "</a><br \/>" );
			}

			$content->parse( "BLOCK_VERSION" );
		}
		$content->parse( "BLOCK_VERSION_LIST" );

		$wiki_html_handler->set_main_html( $content->get() );

		(WIKI_FULL_HEADLINE) ?
		$headline = array(
						$rootlink[0],
						$rootlink[1],
						array( "link" => $rootlink[1]["link"] . "{$place}/", "name" => gettext("{$place}")),
						array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
						array( "link" => PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
						array( "link" => "", "name" => gettext("Version management"))
						):
		$headline = array(
						array( "name" =>  h($wiki_container->get_name()), "link" => PATH_URL . "wiki/Index/" . $wiki_container->get_id() . "/"),
						array( "link" => PATH_URL . "wiki/entry/" . $wiki_doc->get_id() . "/", "name" => str_replace( ".wiki", "", h($wiki_doc->get_name()) ) ),
						array( "link" => "", "name" => gettext("Version management"))
						);

		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($wiki_html_handler->get_html());
		$PopupMenuStyle = \Widgets::getInstance()->readCSS("PopupMenu.css");
		$rawHtml->setCss($PopupMenuStyle . ".popupmenuanker {display:block;}");
		$frameResponseObject->addWidget($rawHtml);
		$frameResponseObject->setHeadline($headline);
		return $frameResponseObject;
	}
}
?>

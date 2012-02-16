<?php
namespace Course\Commands;
class Communication extends \AbstractCommand implements \IFrameCommand {

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
		$frameResponseObject = $this->execute($frameResponseObject);
		return $frameResponseObject;
	}

	public function execute (\FrameResponseObject $frameResponseObject) {
		//$portal = \lms_portal::get_instance();
		//$portal->initialize( GUEST_NOT_ALLOWED );
		//$portal->set_guest_allowed( GUEST_NOT_ALLOWED );

		$user = \lms_steam::get_current_user();
		//$portal_user = $portal->get_user();
		//$path = $request->getPath();
		$group_course = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Courses." . $this->params[0] . "." . $this->params[1]);
		$group = new \koala_group_course( $group_course );
		if ( ! $group instanceof \koala_group )
		{
			throw new \Exception( "Is not a koala_group: " . $group_id, E_PARAMETER );
		}
		switch( get_class( $group ) )
		{
			case( "koala_group_course" ):
				$html_handler_group = new \koala_html_course( $group );
				$html_handler_group->set_context( "communication" );
				break;

			default:
				$html_handler_group = new \koala_html_group( $group );
				$html_handler_group->set_context( "communication" );
				break;
		}
		$content = \Course::getInstance()->loadTemplate("groups_communication.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "groups_communication.template.html" );
		$workroom = $group->get_workroom();
		$read_access = $workroom->check_access_read( $user );
		if (!$read_access) {
			throw new Exception( "No read access on container: id=" . $workroom->get_id(), E_USER_RIGHTS );
		}
		$cache = get_cache_function( \lms_steam::get_current_user()->get_name(), 600 );
		$communication_objects = $cache->call( "lms_steam::get_group_communication_objects", $workroom->get_id(), CLASS_MESSAGEBOARD | CLASS_CALENDAR | CLASS_CONTAINER | CLASS_ROOM );

		$forums = array();
		$weblogs = array();
		$wikis = array();

		foreach($communication_objects as $object) {
			if ($object["OBJ_CLASS"] === "steam_messageboard") $forums[] = $object;
			else if ($object["OBJ_CLASS"] === "steam_calendar") $weblogs[] = $object;
			else if ( ($object["OBJ_CLASS"] === "steam_container" || $object["OBJ_CLASS"] === "steam_room") && ($object["OBJ_TYPE"] != null && ($object["OBJ_TYPE"] == "KOALA_WIKI" || $object["OBJ_TYPE"] == "container_wiki_koala" ) ) ) {
				$wikis[] = $object;
			}
		}

		$content->setVariable( "LABEL_FORUMS", gettext( "Discussion Boards" ) );
		if ( count( $forums ) > 0 )
		{
			$content->setCurrentBlock( "BLOCK_FORUMS" );
			$content->setVariable( "LABEL_FORUM_DESCRIPTION", gettext( "Forum / description" ) );
			$content->setVariable( "LABEL_ARTICLES", gettext( "Articles" ) );
			$content->setVariable( "LABEL_ACCESS", gettext( "Access" ) );
			$content->setVariable( "LABEL_LAST_COMMENT", gettext( "Last comment") );

			$access_descriptions = \lms_forum::get_access_descriptions( $group );
			foreach( $forums as $forum )
			{
				$cache = get_cache_function( $forum[ "OBJ_ID" ], 600 );
				$discussions = $cache->call( "lms_forum::get_discussions", $forum[ "OBJ_ID" ] );
				$latest_post = isset($discussions[ 0 ])?$discussions[0]:FALSE;
				$content->setCurrentBlock( "BLOCK_FORUM" );
				$content->setVariable( "NAME_FORUM", h($forum[ "OBJ_NAME" ]) );
				$content->setVariable( "LINK_FORUM", PATH_URL . "forums/" . $forum[ "OBJ_ID" ] . "/");
				$content->setVariable( "OBJ_DESC", get_formatted_output($forum[ "OBJ_DESC" ]) );
				$language = ( ! empty( $forum[ "FORUM_LANGUAGE" ] ) ) ? $forum[ "FORUM_LANGUAGE" ] : "German";
				$content->setVariable( "VALUE_LANGUAGE", $language );
				$access = "<span title=\"". $access_descriptions[$forum["KOALA_ACCESS"]]["label"] . "\">" . $access_descriptions[$forum["KOALA_ACCESS"]]["summary_short"] . "</span>";
				$content->setVariable( "VALUE_ACCESS", $access);
				$content->setVariable( "VALUE_ARTICLES", count( $discussions ) );
				if ( $latest_post )
				{
					$content->setVariable( "SUBJECT_LAST_COMMENT", h($latest_post[ "LATEST_POST_TITLE" ]) );
					$content->setVariable( "LINK_LAST_COMMENT", PATH_URL . "forums/" . $latest_post[ "OBJ_ID" ] . "/" );
					$content->setVariable( "POSTED_BY_LABEL", "(" . h($latest_post[ "LATEST_POST_AUTHOR" ]) . ", " . how_long_ago( $latest_post[ "LATEST_POST_TS" ] ) . ")" );
				} else {
					$content->setVariable( "POSTED_BY_LABEL", gettext("-") );
				}
				$content->parse( "BLOCK_FORUM" );
			}
			$content->parse( "BLOCK_FORUMS" );
		}
		else
		{
			$content->setVariable( "LABEL_NO_FORUMS_FOUND", "<b>" . gettext( "No forums available. Either no forums are created in this context, or you have no rights to read them." ) . "</b>" );
		}
		$content->setVariable( "LABEL_WEBLOGS", gettext( "Weblogs" ) );
		if( count( $weblogs ) > 0 )
		{
			$content->setCurrentBlock( "BLOCK_WEBLOGS" );
			$content->setVariable( "LABEL_WEBLOG_DESCRIPTION", gettext( "Weblog / description" ) );
			$content->setVariable( "LABEL_WEBLOG_ENTRIES", gettext( "Entries" ) );
			$content->setVariable( "LABEL_WEBLOG_ACCESS", gettext( "Access" ) );
			$content->setVariable( "LABEL_WEBLOG_LAST_ENTRY", gettext( "Last entry" ) );
			$access_descriptions = \lms_weblog::get_access_descriptions( $group );
			foreach( $weblogs as $weblog )
			{
				$cache = get_cache_function( $weblog[ "OBJ_ID" ], 600 );
				$entries  = $cache->call( "lms_weblog::get_items", $weblog[ "OBJ_ID" ] );
				$last_entry = isset($entries[0])?$entries[0]:FALSE;
				$content->setCurrentBlock( "BLOCK_WEBLOG" );
				$content->setVariable( "NAME_WEBLOG", h($weblog[ "OBJ_NAME" ]) );
				$content->setVariable( "LINK_WEBLOG", PATH_URL . "weblog/" . $weblog[ "OBJ_ID" ] . "/" );
				$content->setVariable( "WEBLOG_OBJ_DESC", get_formatted_output($weblog[ "OBJ_DESC" ]) );
				$title = $access_descriptions[$weblog["KOALA_ACCESS"]]["label"];
				if ( $weblog["KOALA_ACCESS"] == PERMISSION_PRIVATE_READONLY && !($group instanceof koala_html_course)) {
					$obj = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $weblog[ "OBJ_ID"], CLASS_CALENDAR );
					$creator = $obj->get_creator();
					if ($creator->get_id() != \lms_steam::get_current_user()->get_id() ) {
						$title = str_replace( "%NAME", $creator->get_name(), $title );
					} else {
						$title = str_replace( "%NAME", "you", $title );
					}
				}
				$content->setVariable( "VALUE_WEBLOG_LANGUAGE", "German" );
				$access = "<span title=\"". $title . "\">" . $access_descriptions[$weblog["KOALA_ACCESS"]]["summary_short"] . "</span>";
				$content->setVariable( "VALUE_WEBLOG_ACCESS", $access);
				$content->setVariable( "VALUE_WEBLOG_ARTICLES", count( $entries ) );
				$content->setVariable( "LINK_WEBLOG_LAST_ENTRY", PATH_URL . "weblog/" . $last_entry[ "OBJ_ID" ] . "/" );
				if ($last_entry) {
					$content->setVariable( "SUBJECT_WEBLOG_LAST_ENTRY", h($last_entry[ "DATE_TITLE" ]) );
					$content->setVariable( "WEBLOG_POSTED_BY_LABEL", "(" . h($last_entry[ "AUTHOR" ]) . ", " . how_long_ago( $last_entry[ "DATE_START_DATE" ] ) . ")" );
				}
				else {
					$content->setVariable( "WEBLOG_POSTED_BY_LABEL", gettext("-") );
				}
				$content->parse( "BLOCK_WEBLOG" );
			}
			$content->parse( "BLOCK_WEBLOGS" );
		}
		else
		{
			$content->setVariable( "LABEL_NO_WEBLOGS_FOUND", "<b>" . gettext( "No weblogs available. Either no weblogs are created in this context, or you have no rights to read them." ) . "</b>" );
		}
		$content->setVariable( "LABEL_WIKIS", gettext( "Wikis" ) );
		if( count( $wikis ) > 0 )
		{
			$content->setCurrentBlock( "BLOCK_WIKIS" );
			$content->setVariable( "LABEL_WIKI_DESCRIPTION", gettext( "Wiki / description" ) );
			$content->setVariable( "LABEL_WIKI_ENTRIES", gettext( "Entries" ) );
			$content->setVariable( "LABEL_WIKI_ACCESS", gettext( "Access" ) );
			$content->setVariable( "LABEL_WIKI_LAST_ENTRY", gettext( "Last entry" ) );
			$access_descriptions = lms_wiki::get_access_descriptions( $group );
			foreach( $wikis as $wiki )
			{
				$cache = get_cache_function( $wiki[ "OBJ_ID" ], 600 );
				$entries  = $cache->call( "lms_wiki::get_items", $wiki[ "OBJ_ID" ] );
				$last_entry = isset($entries[0])?$entries[0]:FALSE;
				$content->setCurrentBlock( "BLOCK_WIKI" );
				$content->setVariable( "NAME_WIKI", h($wiki[ "OBJ_NAME" ]) );
				$content->setVariable( "LINK_WIKI", PATH_URL . "wiki/" . $wiki[ "OBJ_ID" ] . "/" );
				$content->setVariable( "WIKI_OBJ_DESC", get_formatted_output($wiki[ "OBJ_DESC" ]) );
				$title = $access_descriptions[$wiki["KOALA_ACCESS"]]["label"];
				if ( $wiki["KOALA_ACCESS"] == PERMISSION_PRIVATE_READONLY && !($group instanceof koala_html_course)) {
					$obj = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $wiki[ "OBJ_ID" ], CLASS_CONTAINER );
					$creator = $obj->get_creator();
					if ($creator->get_id() != lms_steam::get_current_user()->get_id() ) {
						$title = str_replace( "%NAME", $creator->get_name(), $title );
					} else {
						$title = str_replace( "%NAME", "you", $title );
					}
				}
				$access = "<span title=\"". $title . "\">" . $access_descriptions[$wiki["KOALA_ACCESS"]]["summary_short"] . "</span>";
				$content->setVariable( "VALUE_WIKI_ACCESS", $access);
				$content->setVariable( "VALUE_WIKI_ARTICLES", count( $entries ) );
				$content->setVariable( "LINK_WIKI_LAST_ENTRY", PATH_URL . "wiki/" . $last_entry[ "OBJ_ID" ] . "/" );
				$content->setVariable( "SUBJECT_WIKI_LAST_ENTRY", str_replace( ".wiki", "", h($last_entry[ "OBJ_NAME" ]) ) );
				$content->setVariable( "WIKI_POSTED_BY_LABEL", ($last_entry[ "DOC_LAST_MODIFIED" ] != null) ? "(".h($last_entry[ "DOC_USER_MODIFIED" ]) . ", " . how_long_ago( $last_entry[ "DOC_LAST_MODIFIED" ] ) . ")" : "-" );

				$content->parse( "BLOCK_WIKI" );
			}
			$content->parse( "BLOCK_WIKIS" );
		}
		else
		{
			$content->setVariable( "LABEL_NO_WIKIS_FOUND", "<b>" . gettext( "No wikis available. Either no wikis are created in this context, or you have no rights to read them." ) . "</b>" );
		}
		$html_handler_group->set_html_left( $content->get() );
		//$portal->set_page_main( $html_handler_group->get_headline(), $html_handler_group->get_html(), "");
		$frameResponseObject->setHeadline($html_handler_group->get_headline());
		$widget = new \Widgets\RawHtml();
		$widget->setHtml($html_handler_group->get_html());
		$frameResponseObject->addWidget($widget);
		return $frameResponseObject;

	}
}









?>
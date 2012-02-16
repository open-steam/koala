<?php
namespace Weblog\Commands;

require_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "comments_handling.inc.php" );

class Comments extends \AbstractCommand implements \IFrameCommand {

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
		//$portal->set_confirmation();

		$user = \lms_steam::get_current_user();

		//$path = $request->getPath();
		$STEAM = $GLOBALS["STEAM"];

		$weblogId = $this->id;


		$weblog = \steam_factory::get_object( $STEAM->get_id(), $weblogId ) ;
		//if ( ! $weblog = steam_factory::get_object( $STEAM->get_id(), $_GET[ "id" ] ) )
		//{
		//	include( "bad_link.php" );
		//	exit;
		//}
		defined("OBJ_ID") or define("OBJ_ID", $weblogId);
		
		if ( ! $weblog instanceof \steam_calendar )
		{
			if ( $weblog instanceof \steam_container )
			{
				$category = $weblog;
				$categories = $category->get_environment();
				$weblog = new \steam_weblog( (String)$GLOBALS[ "STEAM" ]->get_id(), $categories->get_environment()->get_id() );
			}
			elseif ( $weblog instanceof \steam_date )
			{
				$date = $weblog;
				$weblog = new \steam_weblog( (String)$GLOBALS[ "STEAM" ]->get_id(), $date->get_environment()->get_id() );
			}
			else
			{
				include( "bad_link.php" );
				exit;
			}
		}
		else
		{

			$weblog = new \steam_weblog( (String)$GLOBALS[ "STEAM" ]->get_id(), $weblogId );
			//define( "OBJ_ID",	$weblogId );
			if ( ! $weblog->check_access_read( $user ) )
			{
				throw new \Exception( "No rights to view this.", E_USER_RIGHTS );
			}
		}
		$html_entry = \Weblog::getInstance()->loadTemplate("weblog_entry.template.html");
		//$html_entry = new HTML_TEMPLATE_IT();
		//$html_entry->loadTemplateFile( PATH_TEMPLATES . "weblog_entry.template.html" );

		$weblog_html_handler = new \lms_weblog( $weblog );

		if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "bookmark_rss" )
		{
			\lms_steam::user_add_rssfeed( $weblog->get_id(), PATH_URL . "services/feeds/weblog_public.php?id=" . $weblog->get_id(), "weblog", \lms_steam::get_link_to_root( $weblog ) );
			$_SESSION["confirmation"] = str_replace( "%NAME", h($weblog->get_name()), gettext( "You are keeping an eye on '%NAME' from now on." ) );
			header( "Location: " . PATH_URL . "weblog/" . $date->get_id() . "/" );
			exit;
		}

		if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "delete_bookmark" )
		{
			$user = \lms_steam::get_current_user();
			$id = (int)$_GET[ "unsubscribe" ];
			$feeds = $user->get_attribute("USER_RSS_FEEDS");
			if (!is_array($feeds)) $feeds = array();
			unset( $feeds[ $id ] );
			$user->set_attribute( "USER_RSS_FEEDS", $feeds );
			$_SESSION["confirmation"] = str_replace("%NAME", h($weblog->get_name()), gettext( "subscription of '%NAME' canceled." ));
			header( "Location: " . PATH_URL . "weblog/" . $date->get_id() . "/" );
			exit;
		}

		$weblog_html_handler->set_menu( "entry" );
		$weblog_html_handler->set_widget_categories();
		$weblog_html_handler->set_widget_archive( 5 );
		$date = $weblog;
		$entry = $date->get_attributes(
		array(
			"DATE_TITLE",
			"DATE_DESCRIPTION",
			"DATE_START_DATE",
			"DATE_CATEGORY",
			"OBJ_KEYWORDS"
			)
			);

			$creator = $date->get_creator();

			$html_entry->setVariable( "VALUE_ARTICLE_TEXT", get_formatted_output( h($date->get_attribute( "DATE_DESCRIPTION" )) ) );
			$html_entry->setVariable( "VALUE_POSTED_BY", str_replace( "%NAME", "<a href=\"" . PATH_URL . "user/" . $creator->get_name(). "/\">" . h($creator->get_attribute( "USER_FIRSTNAME" )) . " " . h($creator->get_attribute( "USER_FULLNAME" )) . "</a>", gettext( "Posted by %NAME" )) );
			$html_entry->setVariable( "VALUE_DATE_TIME", strftime( "%x %X", h($entry[ "DATE_START_DATE" ]) ) );
			$category = $entry[ "DATE_CATEGORY" ];
			if ( ! empty( $category ) )
			{
				$html_entry->setVariable( "LABEL_IN", gettext( "in" ) );
				$html_entry->setVariable( "VALUE_CATEGORY", "<a href=\"" . PATH_URL . "weblog/" . $category->get_id() . "/\">" . h($category->get_name()) . "</a>" );
			}
			else
			{
				$html_entry->setVariable( "VALUE_CATEGORY", gettext( "no category" ) );
			}

			$html_entry->setVariable( "POST_PERMALINK", PATH_URL . "weblog/" . $weblog->get_id() . "/#comment" . $date->get_id() );
			$html_entry->setVariable( "POST_PERMALINK_LABEL", gettext( "permalink" ) );

			$weblog_html_handler->set_main_html( $html_entry->get() . self::get_comment_html( $date, PATH_URL . "weblog/" . $date->get_id() ) );
			//TODO:RSS_FEED
			//$portal->set_rss_feed( PATH_URL . "services/feeds/weblog_public.php?id=" . OBJ_ID , gettext( "Feed" ), str_replace( "%l", (isset($login))?$login:'', gettext( "Subscribe to this forum's Newsfeed" ) ) );

			$rootlink = \lms_steam::get_link_to_root( $weblog );
			$headline = array(
			$rootlink[0],
			$rootlink[1],
			array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
			array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
			array( "link" => "", "name" => h($date->get_attribute( "DATE_TITLE" )))
			);

			/*$portal->set_page_main(
			 $headline,
			 $weblog_html_handler->get_html()
			 );
			 $portal->show_html();*/
			$frameResponseObject->setHeadline($headline);
			$widget = new \Widgets\RawHtml();
			$widget->setHtml($weblog_html_handler->get_html());
			$frameResponseObject->addWidget($widget);
			return $frameResponseObject;


	}
	private static function get_comment_html( $document, $url )
	{

		$cache = get_cache_function( $document->get_id(), 600 );

		$user = \lms_steam::get_current_user();
		$write_access = $document->check_access( SANCTION_ANNOTATE, $user );
		$template = \Weblog::getInstance()->loadTemplate("comments.template.html");
		//$template = new HTML_TEMPLATE_IT();
		//$template->loadTemplateFile( PATH_TEMPLATES . "comments.template.html" );
		$headline = gettext( "Add your comment" );

		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $write_access )
		{

			$values = $_POST[ "values" ];
			if ( ! empty( $values[ "preview_comment" ] ) )
			{
				$template->setCurrentBlock( "BLOCK_PREVIEW_COMMENT" );
				$template->setVariable( "TEXT_COMMENT", $values[ "comment" ] );

				$template->setVariable("PREVIEW", gettext("Preview"));
				$template->setVariable("POST_COMMENT", gettext("Post comment"));
					
				$template->setVariable( "LABEL_PREVIEW_YOUR_COMMENT", gettext( "Preview your comment") );
				$template->setVariable( "VALUE_PREVIEW_COMMENT", get_formatted_output( $values[ "comment" ] ) );
				$template->parse( "BLOCK_PREVIEW_COMMENT" );
				$headline = gettext( "Change it?" );
			}

			if ( ! empty( $values[ "submit_comment" ] ) && ! empty( $values[ "comment" ]) )
			{
				$new_comment = \steam_factory::create_textdoc(
				$GLOBALS[ "STEAM" ]->get_id(),
				$user->get_name() . "-" . time(),
				$values[ "comment" ]
				);
				$all_user = \steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
				$new_comment->set_acquire( $document );
				$new_comment->set_read_access( $all_user );
				$document->add_annotation( $new_comment );

				$cache->drop( "lms_steam::get_annotations", $document->get_id() );
			}
		}
		$comments = $cache->call( "lms_steam::get_annotations", $document->get_id() );

		if ( count( $comments ) > 0  )
		{
			$template->setVariable( "LABEL_COMMENTS", gettext( "comments" ));
		}

		$comments=array_reverse($comments);  //reverse comment order (oldest first)
		//var_dump($comments);die;
		foreach( $comments as $comment )
		{
			$obj_comment = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $comment[ "OBJ_ID" ] );
			$template->setCurrentBlock( "BLOCK_ANNOTATION" );
			$template->setVariable( "COMMENT_ID", $comment[ "OBJ_ID" ] );
			$template->setVariable( "AUTHOR_LINK", PATH_URL . "user/" . $comment[ "OBJ_CREATOR_LOGIN" ] . "/" );
			$template->setVariable( "AUTHOR_NAME", $comment[ "OBJ_CREATOR" ] );
			$template->setVariable( "IMAGE_LINK", PATH_URL . "get_document.php?id=" . $comment[ "OBJ_ICON" ] );
			$template->setVariable( "LABEL_SAYS", gettext( "says" ) );
			$template->setVariable( "ANNOTATION_COMMENT", get_formatted_output( $comment[ "CONTENT" ], 80, "\n" ) );
			$template->setVariable( "HOW_LONG_AGO", how_long_ago( $comment[ "OBJ_CREATION_TIME" ] ) );
			$template->setVariable( 'LINK_PERMALINK', $url . '/#comment' . $comment["OBJ_ID"] );
			$template->setVariable( "LABEL_PERMALINK", gettext( "permalink" ) );
			if ( $obj_comment->check_access_write( $user ) )
			{
				$template->setCurrentBlock( "BLOCK_OWN_COMMENT" );
				$template->setVariable( "LINK_DELETE", $url . "/deletecomment/" . $comment[ "OBJ_ID"] . "/" );
				$template->setVariable( "LABEL_DELETE", gettext( "delete" ) );
				$template->setVariable( "LINK_EDIT", $url . "/editcomment/" . $comment[ "OBJ_ID" ] . "/" );
				$template->setVariable( "LABEL_EDIT", gettext( "edit" ) );
				$template->parse( "BLOCK_OWN_COMMENT" );
			}
			$template->parse( "BLOCK_ANNOTATION" );
		}
		if ( $write_access )
		{
			$template->setCurrentBlock( "BLOCK_ADD_COMMENT" );
			$template->setVariable( "LABEL_ADD_YOUR_COMMENT", $headline );
			$template->setVariable( "LABEL_PREVIEW", gettext( "Preview" ) );
			$template->setVariable( "LABEL_OR", gettext( "or") );
			$template->setVariable( "LABEL_COMMENT", gettext( "Add comment") );

			$template->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
			$template->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
			$template->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
			$template->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
			$template->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
			$template->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
			$template->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
			$template->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
			$template->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
			$template->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
			$template->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
			$template->setVariable( "HINT_BB_URL", gettext( "web link" ) );
			$template->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
			$template->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );

			$template->parse( "BLOCK_ADD_COMMENT" );
		}
		return $template->get();
	}


}

?>

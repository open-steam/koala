<?php
namespace Messageboard\Commands;

class ViewDiscussion extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		
		if (isset($this->params[0])) {
			return true;
		} 
		else {
			return false;
		}
	}
	
	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		
	}
	
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
	
		$forum_id = $this->params[0];
		$portal = \lms_portal::get_instance();
		$user = \lms_steam::get_current_user();
		$rss_feeds = $user->get_attribute( "USER_RSS_FEEDS" );
		
		if ( ! $messageboard = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $forum_id ) )
		{
			include( "bad_link.php" );
			exit;
		}
		
		if ( $messageboard instanceof \steam_document )
		{
			$thread       = $messageboard;
			$messageboard = $thread->get_annotating();
		
			define( "OBJ_ID",	$thread->get_id() );
			if ( ! $thread->check_access_read( $user ) )
			{
				throw new \Exception( "No rights to view this.", E_USER_RIGHTS );
			}
		}
		else
		{
			define( "OBJ_ID",	$messageboard->get_id() );
			if ( ! $messageboard->check_access_read( $user ) )
			{
				throw new \Exception( "No rights to view this.", E_USER_RIGHTS);
			}
		}
		
		if ( ! $messageboard instanceof \steam_messageboard )
		{
			include( "bad_link.php" );
			exit;
		}
		
		$is_watching = FALSE;
		if (is_array($rss_feeds)) {
		  foreach(array_keys($rss_feeds) as $item) {
		    if ($item == $messageboard->get_id()) {
		      $is_watching=TRUE;
		    }
		  }
		}
		
		
		$content = \Messageboard::getInstance()->loadTemplate("forum_discussion.template.html");
		$content->setVariable( "REPLY_LABEL", gettext( "Reply to this topic?" ) );
		
		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $thread->check_access_annotate( \lms_steam::get_current_user() ) )
		{
			$values = $_POST[ "values" ];
			if ( empty( $values[ "body" ] ) ) $portal->set_problem_description(gettext("Please enter your message."));
		
			if ( ! empty( $values[ "save" ] ) && ! empty( $values[ "body" ] ) )
			{
				$new_comment = \steam_factory::create_textdoc(
					$GLOBALS[ "STEAM" ]->get_id(),
					"Re: " . $thread->get_name(),
					$values[ "body" ]
				);
		//		$all_user = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
		//		$new_comment->set_acquire( FALSE );
		//		$new_comment->set_read_access( $all_user, TRUE );
		//		$new_comment->set_write_access( $all_user, FALSE );
		//		$new_comment->set_annotate_access( $all_user, TRUE );
				$thread->add_annotation( $new_comment );
				$new_comment->set_acquire( $thread );
				$mbid = $messageboard->get_id();
		    // Handle Related Cache-Data
		    require_once( "Cache/Lite.php" );
		    $cache = new \Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
		    $cache->clean( $mbid );
		    // clean forumcache
		    $fcache = get_cache_function( $forum_id, 600 );
		    $fcache->drop( "lms_forum::get_discussions", $forum_id );
		    // clean forumcache
		    $fcache = get_cache_function( $mbid, 600 );
		    $fcache->drop( "lms_forum::get_discussions", $mbid );
		    // clean rsscache of the forum
			// TODO: Passt der link?
		    $feedlink = PATH_URL . "services/feeds/forum_public.php?id=" . OBJ_ID;
		    $rcache = get_cache_function( "rss", 600 );
		    $rcache->drop( "lms_rss::get_items", $feedlink );
			}
			else if ( empty( $values[ "save" ] ) && ! empty( $values[ "body" ] ) )
			{
				// PREVIEW
				$content->setCurrentBlock( "BLOCK_PREVIEW_COMMENT" );
				$content->setVariable( "LABEL_PREVIEW_COMMENT", gettext( "Preview the edit" ) );
				$content->setVariable( "VALUE_PREVIEW_COMMENT", get_formatted_output( $values["body"] ) );
				$content->setVariable( "TEXT_COMMENT", h($values[ "body" ]) );
				$content->parse( "BLOCK_PREVIEW_COMMENT" );
				$content->setVariable( "REPLY_LABEL", gettext( "Change it?" ) );
			}
		}
		
		if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "bookmark_rss" )
		{
			\lms_steam::user_add_rssfeed( $messageboard->get_id(), PATH_URL . "services/feeds/forum_public.php?id=" . $messageboard->get_id(), "discussion board", \lms_steam::get_link_to_root( $messageboard ) );
			$_SESSION["confirmation"] = ( str_replace( "%NAME", h($messageboard->get_name()), gettext( "You are keeping an eye on '%NAME' from now on." ) ) );
		  header( "Location: " . PATH_URL . "forums/" . $thread->get_id() . "/" );
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
		  $_SESSION["confirmation"] = str_replace("%NAME", h($messageboard->get_name()), gettext( "subscription of '%NAME' canceled." ));
		  header( "Location: " . PATH_URL . "forums/" . $thread->get_id() . "/" );
		  exit;
		}
		
		$content->setVariable( "CURRENT_DISCUSSIONS_LABEL", gettext( "Current Thread" ) );
		
		$cache = get_cache_function( $messageboard->get_id(), 600 );
		
		$discussions = $cache->call( "lms_forum::get_discussions",  $messageboard->get_id() );
		$max_discussions = 12;
		
		foreach( $discussions as $discussion )
		{
			$max_discussions--;
			if ( $max_discussions == 0 )
			{
				$content->setCurrentBlock( "BLOCK_TOPIC_INFO" );
				$content->setVariable( "TOPIC_LINK", PATH_URL . "forums/" . $messageboard->get_id() . "/" );
				$content->setVariable( "TOPIC_TITLE", gettext( "More..." ) );
				$content->parse( "BLOCK_TOPIC_INFO" );
				break;
			}
			$content->setCurrentBlock( "BLOCK_TOPIC_INFO" );
			if ( time() - $discussion[ "LATEST_POST_TS" ] > $_SESSION[ "last_login" ] )
			{
				$content->setCurrentBlock( "BLOCK_TOPIC_NEW" );
				$content->setVariable( "NEW_LABEL", gettext( "New" ) );
				$content->parse( "BLOCK_TOPIC_NEW" );
			}
			$content->setVariable( "TOPIC_LINK", PATH_URL . "messageboard/viewDiscussion/" . $discussion[ "OBJ_ID" ] . "/");
			$content->setVariable( "TOPIC_TITLE", h($discussion[ "OBJ_NAME" ]) );
			$content->setVariable( "TOPIC_LAST_ENTRY", gettext( "Latest:" ) . how_long_ago( $discussion[ "LATEST_POST_TS" ] ) );
			$content->parse( "BLOCK_TOPIC_INFO" );
		}
		
		$content->setVariable( "LABEL_TOPICS_POSTED", gettext( "Topics you've posted in" ) );
		$content->setVariable( "LINK_AUTHOR", PATH_URL . "forums/" . $messageboard->get_id() . "/?author=" . \lms_steam::get_current_user()->get_name() );
		$content->setVariable( "LABEL_POST_NEW", gettext( "Post a new topic" ) );
		$content->setVariable( "LINK_POST_NEW", PATH_URL . "messageboard/newDiscussion/" . $messageboard->get_id() );
		
		$content->setCurrentBlock("BLOCK_WATCH");
		if ($is_watching) {
		  $content->setVariable( "LABEL_BOOKMARK", gettext("End watching"));
		  $content->setVariable( "LINK_BOOKMARK", PATH_URL . "forums/" . $thread->get_id() . "/?action=delete_bookmark&unsubscribe=" . $messageboard->get_id() );
		}
		else {
		  $content->setVariable( "LABEL_BOOKMARK", gettext( "Watch this forum" ) );
		  $content->setVariable( "LINK_BOOKMARK", PATH_URL . "forums/" . $thread->get_id() . "/?action=bookmark_rss" );
		}
		$content->parse("BLOCK_WATCH");
		
		$content->setVariable( "DISCUSSION_SUBJECT", h($thread->get_name()) );
		$author = $thread->get_creator();
		$author_data = $author->get_attributes( array( "OBJ_NAME", "USER_FIRSTNAME", "USER_FULLNAME", "OBJ_ICON") );
		$content->setVariable( "AUTHOR_LINK", PATH_URL . "user/" . $author_data[ "OBJ_NAME" ] . "/" );
		
		$icon = $author_data[ "OBJ_ICON" ];
		if ( $icon instanceof \steam_object )
		{
			$icon_id = $icon->get_id();
		}
		else
		{
			$icon_id = 0;
		}
		$content->setVariable( "AUTHOR_IMAGE", PATH_URL . "cached/get_document.php?id=" . $icon_id . "&type=usericon&width=60&height=70");
		$content->setVariable( "NAME_SAYS_LABEL", str_replace( "%n", "<a href=\"" . PATH_URL . "user/" . $author_data[ "OBJ_NAME" ] . "/\">" . h($author_data[ "USER_FIRSTNAME" ]). " " . h($author_data[ "USER_FULLNAME" ]) . "</a>" , gettext( "%n says:" ) ) );
		$content->setVariable( "DISCUSSION_TEXT", get_formatted_output( $thread->get_content(), 65, "\n" ) );
		$ts = $thread->get_attribute( "OBJ_CREATION_TIME" );
		$content->setVariable( "DISCUSSION_STARTED_TS", gettext( "Posted at" ) . " " . strftime( "%H:%M", $ts) . " | " . strftime( "%d. %B %Y", $ts ) );
		
		$content->setVariable( "DISCUSSION_PERMALINK", PATH_URL . 'forums/' . $discussion['OBJ_ID'] . '/' );
		$content->setVariable( "DISCUSSION_PERMALINK_TEXT", gettext( "permalink" ));
		$steam_user  = \lms_steam::get_current_user();
		if ( $thread->check_access_write( $steam_user ) )
		{
			$content->setCurrentBlock( "BLOCK_OWN_DISCUSSION" );
			$content->setVariable( "DISCUSSION_LINK_DELETE", PATH_URL . "messageboard/deleteComment/" . $messageboard->get_id() ."/". $thread->get_id() );
			$content->setVariable( "DISCUSSION_LABEL_DELETE", gettext( "delete" ) );
			$content->setVariable( "DISCUSSION_LINK_EDIT", PATH_URL . "messageboard/editComment/" . $thread->get_id() . "/" . $thread->get_id() );
			$content->setVariable( "DISCUSSION_LABEL_EDIT", gettext( "edit" ) );
			$content->parse( "BLOCK_OWN_DISCUSSION" );
		}
		
		$annotations = \lms_steam::get_annotations( $thread->get_id() );
		
		$annotations = array_reverse( $annotations );
		
		$access_tnr = array();
		foreach ( $annotations as $annotation ) {
			$steam_obj = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $annotation[ "OBJ_ID" ], CLASS_OBJECT );
		  $access_tnr[$steam_obj->get_id()] = $steam_obj->check_access_write($steam_user, TRUE);
		}
		$access_result = $GLOBALS["STEAM"]->buffer_flush();
		
		
		foreach ( $annotations as $annotation ) {
			$content->setCurrentBlock( "REPLY" );
			$content->setVariable( 'REPLY_ID', $annotation['OBJ_ID'] );
			$content->setVariable( "REPLYER_LINK", PATH_URL . "user/" . $annotation["OBJ_CREATOR_LOGIN" ] . "/" );
			$content->setVariable( "REPLYER_IMAGE", PATH_URL . "cached/get_document.php?id=" . $annotation[ "OBJ_ICON" ] . "&type=usericon&width=60&height=70" );
			$content->setVariable( "REPLYER_SAYS_LABEL", str_replace( "%n", "<a href=\"" . PATH_URL . "user/" . $annotation[ "OBJ_CREATOR_LOGIN" ] . "/\">" . h($annotation[ "OBJ_CREATOR" ]) . "</a>" , gettext( "%n says:" ) ) );
			//$content->setVariable( "REPLYERS_SAYS_LABEL", str_replace );
			$content->setVariable( "REPLY_CONTENT", get_formatted_output( $annotation[ "CONTENT"], 60, "\n" ) );
			$content->setVariable( "REPLY_TS", how_long_ago( $annotation[ "OBJ_CREATION_TIME" ] ) );
			$content->setVariable( "REPLY_PERMALINK", PATH_URL . 'forums/' . $thread->get_id() . '/#comment' . $annotation[ 'OBJ_ID' ] );
			$content->setVariable( "REPLY_PERMALINK_TEXT", gettext( "permalink" ) );
			$steam_obj = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $annotation[ "OBJ_ID" ], CLASS_OBJECT );
			if ( $access_result[$access_tnr[$steam_obj->get_id()]] ) {
				$content->setCurrentBlock( "BLOCK_OWN_REPLY" );
				$content->setVariable( "REPLY_LINK_DELETE", PATH_URL . "messageboard/deleteComment/". $thread->get_id() . "/" . $annotation[ "OBJ_ID" ] . "/" );
				$content->setVariable( "REPLY_LABEL_DELETE", gettext( "delete" ) );
				$content->setVariable( "REPLY_LABEL_EDIT", gettext( "edit" ) );
				$content->setVariable( "REPLY_LINK_EDIT", PATH_URL . "messageboard/editComment/". $thread->get_id() . "/" . $annotation[ "OBJ_ID" ] . "/" );
				$content->parse( "BLOCK_OWN_REPLY" );
			}
			$content->parse( "REPLY" );
		}
		
		$content->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
		$content->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
		$content->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
		$content->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
		$content->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
		$content->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
		$content->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
		$content->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
		$content->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
		$content->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
		$content->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
		$content->setVariable( "HINT_BB_URL", gettext( "web link" ) );
		$content->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
		$content->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );
		
		$content->setVariable( "LABEL_PREVIEW", gettext( "Preview" ) );
		$content->setVariable( "LABEL_OR", gettext( "or" ) );
		$content->setVariable( "LABEL_POST_NOW", gettext( "Post now" ) );
		
		$portal->set_rss_feed( PATH_URL . "services/feeds/discussion_public.php?id=" . OBJ_ID, gettext( "Feed" ), gettext( "Subscribe to this forum's Newsfeed" ));
		
		// TODO: Passt der link?
		$rootlink = \lms_steam::get_link_to_root( $messageboard );
		$headline = array(
						$rootlink[0],
						$rootlink[1],
						array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
						array( "link" => PATH_URL . "forums/" . $messageboard->get_id() . "/", "name" => $messageboard->get_name() ),
						array( "link" => "", "name" => gettext( "Discussion" ) )
					);
			
			
		$frameResponseObject->setTitle("Messageboard");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}

?>
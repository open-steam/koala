<?php
namespace Messageboard\Commands;

class Index extends \AbstractCommand implements \IFrameCommand {
	
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
		$author_id = isset($this->params[1]) ? $this->params[1]:null;
		
		
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
		
		
		$content = \Messageboard::getInstance()->loadTemplate("forum_all_topics.template.html");
		
		$dsc = $messageboard->get_attribute( "OBJ_DESC" );	
		if ( ! empty( $dsc ) )
		{
		  $content->setCurrentBlock( "BLOCK_DESCRIPTION" );
		  $content->setVariable( "FORUM_DESCRIPTION", get_formatted_output( $dsc ) );
		  $content->parse( "BLOCK_DESCRIPTION" );
		}
		
		$grp = $messageboard->get_environment()->get_creator();
		if ($grp->get_name() == "learners" && $grp->get_attribute(OBJ_TYPE) == "course_learners") {
		  $grp = $grp->get_parent_group();
		}
		
		$content->setVariable( "CURRENT_DISCUSSIONS_LABEL", gettext( "Current Thread" ) );
		$content->setVariable( "LABEL_SEARCH", gettext( "Search" ) );
		
		if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "bookmark_rss" )
		{
						\lms_steam::user_add_rssfeed( $messageboard->get_id(), PATH_URL . "services/feeds/forum_public.php?id=" . $messageboard->get_id(), "discussion board", \lms_steam::get_link_to_root( $messageboard ) );
						$_SESSION["confirmation"] = ( str_replace( "%NAME", h($messageboard->get_name()), gettext( "You are keeping an eye on '%NAME' from now on." ) ) );
		        header( "Location: " . PATH_URL . "forums/" . $messageboard->get_id() . "/" );
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
		  header( "Location: " . PATH_URL . "forums/" . $messageboard->get_id() . "/" );
		  exit;
		}
		
		if( empty( $_GET[ "pattern" ] ) && !isset($author_id) )
		{
						$cache = get_cache_function( OBJ_ID, 300 );
						$discussions = $cache->call( "lms_forum::get_discussions",  OBJ_ID );
		}
		elseif( isset($author_id) )
		{
						$cache = get_cache_function( \lms_steam::get_current_user()->get_name(), 300 );
						$discussions = $cache->call( "lms_forum::search_user_posts", $messageboard->get_id(), $author_id  );
		}
		else
		{
						$cache = get_cache_function( \lms_steam::get_current_user()->get_name(), 300 );
						$discussions = $cache->call( "lms_forum::search_pattern", $messageboard->get_id(), $_GET[ "pattern" ] );
		}
		
		$content->setVariable( "LABEL_ALL_TOPICS", gettext( "All Threads" ) );
		
		if ( $messageboard->check_access_annotate( \lms_steam::get_current_user() ) )
		{
						$content->setCurrentBlock( "BLOCK_WRITE_ACCESS" );
		        if (isset($author_id)) {
		          $content->setVariable( "LABEL_THREADS_POSTED_IN", gettext( "All Threads" ) );
		          // TODO: Passt der link?
		          $content->setVariable( "LINK_AUTHOR", PATH_URL . "forums/" . $messageboard->get_id() . "/" );
		        } else {
		          $content->setVariable( "LABEL_THREADS_POSTED_IN", gettext( "Threads you've posted in" ) );
		          // TODO: Passt der link?
		          $content->setVariable( "LINK_AUTHOR", PATH_URL . "forums/" . $messageboard->get_id() . "/?author=" . \lms_steam::get_current_user()->get_name() );
		        }
		        		// TODO: Passt der link?
						$content->setVariable( "LINK_POST_NEW", PATH_URL . "messageboard/newDiscussion/" . $messageboard->get_id() );
						$content->setVariable( "LABEL_POST_NEW_THREAD", gettext( "Post a new thread" ) );
						$content->parse( "BLOCK_WRITE_ACCESS" );
		}
		if ( $messageboard->check_access_write( \lms_steam::get_current_user() ) )
		{
		  $content->setCurrentBlock("BLOCK_ADMIN");
		  $content->setVariable( "LINK_EDIT", PATH_URL . "messageboard/editMessageboard/" . $messageboard->get_id() );
		  $content->setVariable( "LABEL_EDIT", gettext( "Preferences" ) );
		  $content->setVariable( "LINK_DELETE", PATH_URL . "messageboard/deleteMessageboard/" . $messageboard->get_id());
		  $content->setVariable( "LABEL_DELETE", gettext( "Delete forum" ) );
		  $content->parse( "BLOCK_ADMIN" );
		}
		
		$content->setCurrentBlock("BLOCK_WATCH");
		if ($is_watching) {
		  $content->setVariable( "LABEL_BOOKMARK", gettext("End watching"));
		  // TODO: Passt der link?
		  $content->setVariable( "LINK_BOOKMARK", PATH_URL . "forums/" . $messageboard->get_id() . "/?action=delete_bookmark&unsubscribe=" . $messageboard->get_id() );
		}
		else {
		  $content->setVariable( "LABEL_BOOKMARK", gettext( "Watch this forum" ) );
		  // TODO: Passt der link?     
		  $content->setVariable( "LINK_BOOKMARK", PATH_URL . "forums/" . $messageboard->get_id() . "/?action=bookmark_rss" );
		}
		$content->parse("BLOCK_WATCH");
		
		// ACCESS
		$access_descriptions = \lms_forum::get_access_descriptions( $grp );
		$access_descriptions = $access_descriptions[$messageboard->get_attribute(KOALA_ACCESS)];
		$access = $access_descriptions["summary_short"] . ": " . $access_descriptions["label"];
		$content->setCurrentBlock("BLOCK_ACCESS");
		$content->setVariable("TITLE_ACCESS", gettext("Access"));
		$content->setVariable("LABEL_ACCESS", $access);
		$content->parse("BLOCK_ACCESS");
		
		$content->setVariable( "LABEL_TITLE", gettext( "Title" ) );
		$content->setVariable( "LABEL_AUTHOR", gettext( "Author" ) );
		$content->setVariable( "LABEL_REPLIES", gettext( "Replies" ) );
		$content->setVariable( "LABEL_LATEST_POST", gettext( "Latest Post" ) );
		
		// PAGE SETZEN
		$no_discussions = count( $discussions );
		$paginator = \lms_portal::get_paginator(20, $no_discussions, gettext( "(%TOTAL discussions in forum)" ) );
		$start = $paginator["startIndex"];
				
		//$start = $portal->set_paginator( $content, 20, $no_discussions, gettext( "(%TOTAL discussions in forum)" ) );
		
		$end = ( $start + 20 > $no_discussions ) ? $no_discussions : $start + 20;
		
		for( $i = $start; $i < $end; $i++ )
		{
		
						$discussion = $discussions[ $i ];
						$content->setVariable("PAGINATOR", $paginator["html"]);
						$content->setCurrentBlock( "BLOCK_THREAD" );
						if ( time() - $discussion[ "LATEST_POST_TS" ] > $_SESSION[ "last_login" ] )
						{
										$content->setCurrentBlock( "BLOCK_THREAD_NEW" );
										$content->setVariable( "NEW_LABEL", gettext( "New" ) );
										$content->parse( "BLOCK_THREAD_NEW" );
						}
						$content->setVariable( "THREAD_LINK", PATH_URL . "messageboard/viewDiscussion/" . $discussion[ "OBJ_ID" ] . "/" );
						$content->setVariable( "THREAD_SUBJECT", h($discussion[ "OBJ_NAME" ]) );
						$content->setVariable( "THREAD_LAST_ENTRY",  /*h($discussion[ "LATEST_POST_AUTHOR" ]) . ", " .*/ how_long_ago( $discussion[ "LATEST_POST_TS" ] ) );
						// TODO: Passt der link?
						$content->setVariable( "AUTHOR_LINK", PATH_URL . "user/" . $discussion[ "AUTHOR_LOGIN" ] . "/" );
						$content->setVariable( "AUTHOR_IMAGE", PATH_URL . "cached/get_document.php?id=" . $discussion[ "OBJ_ICON" ] . "&type=usericon&width=30&height=40");
						$title = ( ! empty( $discussion[ "USER_ACADEMIC_TITLE" ] ) ) ? $discussion[ "USER_ACADEMIC_TITLE" ] . " " : "";
						$content->setVariable( "AUTHOR_NAME", h($title . $discussion[ "USER_FIRSTNAME" ] . " " . $discussion[ "USER_FULLNAME" ]) );
						$content->setVariable( "THREAD_REPLIES", h($discussion[ "REPLIES" ]) );
						$content->parse( "BLOCK_THREAD" );
		}
		
		$portal->set_rss_feed( PATH_URL . "services/feeds/forum_public.php?id=" . OBJ_ID , gettext( "Feed" ), str_replace( "%l", isset($login)?$login:"", gettext( "Subscribe to this forum's Newsfeed" ) ) );
		// TODO: Passt der link?
		$rootlink = \lms_steam::get_link_to_root( $messageboard );
		$headline = array( $rootlink[0], $rootlink[1], array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication"))  ,  array( "link" => "", "name" =>  h($messageboard->get_name())) );
		
		$frameResponseObject->setTitle("Messageboard");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}

?>
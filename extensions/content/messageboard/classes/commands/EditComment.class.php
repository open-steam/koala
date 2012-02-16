<?php
namespace Messageboard\Commands;

class EditComment extends \AbstractCommand implements \IFrameCommand {
	
	private $params;
	
	public function validateData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		
		if (isset($this->params[0]) && isset($this->params[1])) {
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
		$comment_id = $this->params[1];
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
		
		
		$comment = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $comment_id );

		$content = \Messageboard::getInstance()->loadTemplate("comment_edit.template.html");
		
		if ( $_SERVER[ "REQUEST_METHOD" ] == "GET" )
		{
			$content->setVariable( "LABEL_HERE_IT_IS", "" );
			$content->setVariable( "TEXT_COMMENT", h($comment->get_content()) );
			$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
		}
		else
		{
			$values = $_POST[ "values" ];
			if ( ! empty( $values[ "save" ] ) )
			{
				$comment->set_content( $values[ "message" ] );
				require_once( "Cache/Lite.php" );
				// Handle Related Cache-Data (for the case that the subject may be editable in the future)
				require_once( "Cache/Lite.php" );
				$cache = new \Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
				$cache->clean( OBJ_ID );
		    // clean forumcache
		    $fcache = get_cache_function(  OBJ_ID, 600 );
		    $fcache->drop( "lms_forum::get_discussions", OBJ_ID );
		    // clean cache for Weblog RSS Feed for the Comments
		    $cache = get_cache_function( OBJ_ID, 600 );
		    $discussions = $cache->drop( "lms_steam::get_annotations", OBJ_ID );
		    // clean rsscache
		    $rcache = get_cache_function( "rss", 600 );
		    // TODO: Passt der link?
		    $feedlink = PATH_URL . "services/feeds/forum_public.php?id=" . OBJ_ID;
				$rcache->drop( "lms_rss::get_items", $feedlink );
				// TODO: Passt der link?
		    $feedlink = PATH_URL . "services/feeds/weblog_public.php?id=" . OBJ_ID;
				$rcache->drop( "lms_rss::get_items", $feedlink );
				header( "Location: " . $values[ "return_to" ] );
				exit;
			}
			else
			{
				// PREVIEW
				$content->setCurrentBlock( "BLOCK_PREVIEW" );
				$content->setVariable( "LABEL_PREVIEW_EDIT", gettext( "Preview the edit" ) );
				$content->setVariable( "PREVIEW_EDIT", get_formatted_output( $values[ "message" ] ) );
				$content->parse( "BLOCK_PREVIEW" );
				$content->setVariable( "LABEL_HERE_IT_IS", gettext( "Change it?" ) );
				$content->setVariable( "TEXT_COMMENT", h($values[ "message" ]) );
				$content->setVariable( "BACK_LINK", $values[ "return_to" ] );
			}
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
		$content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Save changes" ) );
		$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
		// TODO: Passt der link?
		$rootlink = \lms_steam::get_link_to_root( $messageboard );
		$headline = array(
						$rootlink[0],
						$rootlink[1],
						array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
						array( "link" => PATH_URL . "forums/" . $messageboard->get_id() . "/", "name" => $messageboard->get_name() ),
						array( "link" => "", "name" => gettext( "Edit a comment" ) )
					);
			
			
		$frameResponseObject->setTitle("Messageboard");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}

?>
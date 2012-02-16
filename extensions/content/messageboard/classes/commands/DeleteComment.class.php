<?php
namespace Messageboard\Commands;

class DeleteMessageboard extends \AbstractCommand implements \IFrameCommand {
	
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
		
		$object = $messageboard;
		$user    = \lms_steam::get_current_user();
		
		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $object->check_access_write( $user ) )
		{
			$values = $_POST[ "values" ];
			if ( $values[ "delete" ] )
			{
		    $_SESSION[ "confirmation" ] = str_replace("%NAME", h($object->get_name()), gettext( "The forum '%NAME' has been deleted." ));
		    $workroom = $object->get_environment();
		    \lms_steam::delete( $object );
		    // Clean Cache for the deleted Forum
				require_once( "Cache/Lite.php" );
				$cache = new \Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
				$cache = get_cache_function( OBJ_ID, 600 );
				$cache->drop( "lms_steam::get_annotations", OBJ_ID );
				// Handle Related Cache-Data
				require_once( "Cache/Lite.php" );
				$cache = new \Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
				$cache->clean( OBJ_ID );
				// clean forum cache
		    $fcache = get_cache_function( OBJ_ID, 600 );
		    $fcache->drop( "lms_forum::get_discussions",  OBJ_ID );
		    // clean cache for forum RSS Feed for the Comments
		    $fcache->drop( "lms_steam::get_annotations", OBJ_ID ); 
		    // clean rsscache
		    $rcache = get_cache_function( "rss", 600 );
		    // TODO: Passt der link?
		    $feedlink = PATH_URL . "services/feeds/forum_public.php?id=" . OBJ_ID;
				$rcache->drop( "lms_rss::get_items", $feedlink );
		    // Clean communication summary cache für the group/course
		    if (is_object($workroom)) {
		      $cache = get_cache_function( \lms_steam::get_current_user()->get_name(), 600 );
		      $cache->drop( "lms_steam::get_inventory_recursive", $workroom->get_id(), CLASS_MESSAGEBOARD, array( "FORUM_LANGUAGE" ) );
		      $cache->drop( "lms_steam::get_group_communication_objects", $workroom->get_id(), CLASS_MESSAGEBOARD | CLASS_CALENDAR | CLASS_CONTAINER | CLASS_ROOM );
		    }
		    
				header( "Location: " . PATH_URL . "group/index" );
				exit;
			}
			
		}
		$content = \Messageboard::getInstance()->loadTemplate("object_delete.template.html");
		
		if ( $object->check_access_write( $user ) )
		{
			$content->setVariable( "LABEL_ARE_YOU_SURE", str_replace("%NAME", h($object->get_name()), gettext( "Are you sure you want to delete the forum '%NAME' ?" )) );
		  // TODO: Passt der link?
		  $rootlink = \lms_steam::get_link_to_root( $object );
		  $content->setVariable( "DELETE_BACK_LINK", $rootlink[1]["link"] . "communication/");
		
			$content->setCurrentBlock( "BLOCK_DELETE" );
			$content->setVariable( "FORM_ACTION", $_SERVER[ "REQUEST_URI" ] );
			$content->setVariable( "LABEL_DELETE_IT", gettext( "yes, delete it" ) );
			$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
			$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
			$content->parse( "BLOCK_DELETE" );
		}
		else
		{
			$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "You have no rights to delete this forum!" ) );
		}
		$content->setVariable( "TEXT_INFORMATION", gettext("The forum and all its entries be deleted.") );
		$creator = $object->get_creator();
		$creator_data = $creator->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "OBJ_ICON" ) );
		$content->setVariable( "LABEL_FROM_AND_AGO", str_replace( "%N", "<a href=\"" . PATH_URL . "/user/" . $creator->get_name() . "/\">" . h($creator_data[ "USER_FIRSTNAME" ]) . " " . h($creator_data[ "USER_FULLNAME" ]) . "</a>", gettext( "by %N" ) ) . "," . how_long_ago( $object->get_attribute( "OBJ_CREATION_TIME" ) )  );
		
		$icon = $creator_data[ "OBJ_ICON" ];
		if ( $icon instanceof \steam_object )
		{
			$icon_id = $icon->get_id();
		}
		else
		{
			$icon_id = 0;
		}
		
		$content->setVariable( "ICON_SRC", PATH_URL . "get_document.php?id=" . $icon_id );
		// TODO: Passt der Link?
		$rootlink = \lms_steam::get_link_to_root( $object );
		$headline = array( $rootlink[0], $rootlink[1], array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication"))  ,  array( "link" => "", "name" =>  h($object->get_name()), "link" => PATH_URL . "forums/" . $object->get_id() . "/"), array("name" => gettext("Delete forum")) );
		
			
			
		$frameResponseObject->setTitle("Messageboard");
		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		
		return $frameResponseObject;
	}
}

?>
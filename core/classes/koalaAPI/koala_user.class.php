<?php

class koala_user extends koala_object
{
	/**
	 * Set up a koala_user for a steam_user.
	 *
	 * @param Object $steam_user the steam_user that shall be
	 *   represented by the koala_user
	 */
	public function __construct( $steam_user )
	{
		if ( !is_object( $steam_user ) || !($steam_user instanceof steam_user) )
			throw new Exception( "No valid steam_user provided", E_PARAMETER );
		parent::__construct( $steam_user );
	}

	public function get_url()
	{
		if ( lms_steam::get_current_user()->get_id() == $this->get_id() )
			return PATH_URL . "desktop/";
		else
			return PATH_URL . "user/" . $this->get_name() . "/";
	}
	
	public function get_display_name()
	{
		return h( $this->steam_object->get_full_name() );
	}

	protected function get_link_path_internal( $top_object )
	{
		return array( -1 => $this->get_link() );
	}

	public function get_html_handler()
	{
		return new koala_html_user( $this );
	}

	/**
	 * Returns the user's workroom. The user's documents folder is contained in
	 * the workroom and can be retrieved with get_documents_folder() instead.
	 * 
	 * @see get_documents_folder
	 *
	 * @return Object the user's workroom
	 */
	public function get_workroom()
	{
		return $this->steam_object->get_workroom();
	}

	/**
	 * Returns the user's subscribed news feeds.
	 *
	 * @param int $offset (optional) offset in the feeds (for pagination)
	 * @param int $length (optional) number of items to return (e.g. for pagination)
	 * @param boolean $return_pagination_info (optional) if TRUE, then an array('feeds'=>array(...),'total'=>nr,'start'=>nr,'page'=>nr,'length'=>nr) is returned instead of just a result array.
	 * @return array of feeds, where each feed is an array('type','title','name','url','author','date','obj','feed_obj') with the corresponding values, or a mapping with pagination info if $return_pagination_info was TRUE
	 */
   public function get_news_feeds( $offset = 0, $length = 0, $return_pagination_info = FALSE ) {
     return koala_user::get_news_feeds_static( $offset, $length, $return_pagination_info, $this );
   }
  
	/**
	 * Returns the user's subscribed news feeds.
	 *
	 * @param int $offset (optional) offset in the feeds (for pagination)
	 * @param int $length (optional) number of items to return (e.g. for pagination)
	 * @param boolean $return_pagination_info (optional) if TRUE, then an array('feeds'=>array(...),'total'=>nr,'start'=>nr,'page'=>nr,'length'=>nr) is returned instead of just a result array.
	 * @return array of feeds, where each feed is an array('type','title','name','url','author','date','obj','feed_obj') with the corresponding values, or a mapping with pagination info if $return_pagination_info was TRUE
	 */
	public static function get_news_feeds_static( $offset = 0, $length = 0, $return_pagination_info = FALSE, $user = FALSE )
	{
		if ( !is_object( $user ) )
			throw new Exception( "No valid steam_user provided", E_PARAMETER );

		$rss_feeds = $user->get_attribute( 'USER_RSS_FEEDS' );
		if ( !is_array( $rss_feeds ) || count( $rss_feeds ) < 1 ) {
			if ( $return_pagination_info )
				return array( 'objects'=>array(), 'total'=>0, 'start'=>0, 'length'=>0, 'page'=>0 );
			else
				return array();
		}
		
    $feeds = array();
    $feedobjects = array();
    
    foreach ( $rss_feeds as $obj_id => $rss_feed ) {
			try {
				$obj = steam_factory::get_object( $GLOBALS['STEAM']->get_id(), $obj_id );
			} catch ( Exception $e ) { 
				unset($rss_feeds[$obj_id]);
				continue; 
			}
			if ($obj instanceof steam_object) {
				$feedobjects[$obj_id] = $obj;
			} else {
				unset($rss_feeds[$obj_id]);
			}
    }
    // Check for Read-Access
    $read_tnr = array();
    foreach ( $feedobjects as $object ) {
			 $read_tnr[$object->get_id()] = $object->check_access_read($user, TRUE);
    }
    $read_result = $GLOBALS["STEAM"]->buffer_flush();

		foreach ( $rss_feeds as $obj_id => $rss_feed ) {
  		$obj = $feedobjects[$obj_id];
			if ( !is_object( $obj ) ) continue;
      if ( !$read_result[$read_tnr[$obj_id]] ) continue;
			$feed = array( 'obj' => $obj, 'feed_obj' => $obj );
			foreach ( $rss_feed as $key => $value ) $feed[ $key ] = $value;
			if ( $feed['type'] === 'weblog' ) {
					$dates = $obj->get_date_objects();
					if ( ! is_array( $dates ) ) $dates = array();
					$feed['items'] = $dates;
			}
			$feeds[] = $feed;
		}
		$nr_feeds = count( $feeds );
		for ( $i=0; $i<$nr_feeds; $i++ ) {
			$obj = $feeds[$i]['obj'];
			switch ( $feeds[$i]['type'] ) {
				case 'weblog':
					$feeds[$i]['weblog_items'] = array();
					foreach ( $feeds[$i]['items'] as $date ) {
						$feeds[$i]['weblog_items'][] = $date->get_annotations_filtered( array( array( '+', 'class', CLASS_OBJECT ) ), array( array( '>', 'attribute', array( 'DOC_LAST_MODIFIED', 'OBJ_CREATION_TIME' ) ) ), 0, 0, 0, TRUE );
					}
					break;
				default:
					$feeds[$i]['items'] = $obj->get_annotations_filtered( array( array( '+', 'class', CLASS_OBJECT ) ), array( array( '>', 'attribute', array( 'DOC_LAST_MODIFIED', 'OBJ_CREATION_TIME' ) ) ), 0, 0, 0, TRUE );
					break;
			}
		}
		$results = $GLOBALS['STEAM']->buffer_flush();
		// get all feed items:
		$item_to_feed = array();
		$items = array();
		for ( $i=0; $i<$nr_feeds; $i++ ) {
			switch ( $feeds[$i]['type'] ) {
				case 'weblog':
					$weblog_items = $feeds[$i]['weblog_items'];
					unset( $feeds[$i]['weblog_items'] );
					for ( $j=0; $j<count($weblog_items); $j++ ) {
						$feed = array();
						foreach ( $feeds[$i] as $key => $value ) $feed[ $key ] = $value;
						$feed['feed_obj'] = $feeds[$i]['items'][$j];
						$feed['items'] = $results[ $weblog_items[$j] ];
						$feeds[] = $feed;
					}
					//$feeds[$i]['items'] = array_merge( $feeds[$i]['items'], $weblog_items );
					break;
				default:
					$feeds[$i]['items'] = $results[ $feeds[$i]['items'] ];
					break;
			}
		}
		$nr_feeds = count( $feeds );
		for ( $i=0; $i<$nr_feeds; $i++ ) {
			foreach ( $feeds[$i]['items'] as $item ) {
				$item_to_feed[ $item->get_id() ] = $feeds[$i];
				$items[] = $item;
			}
		}
		// sort all items and limit them to length and offset:
		$pagination_info = $GLOBALS['STEAM']->predefined_command( $GLOBALS['STEAM']->get_module('searching'), 'paginate_object_array', array( $items, FALSE, array( array( '>', 'attribute', array( 'DOC_LAST_MODIFIED', 'OBJ_CREATION_TIME' ) ) ), $offset, $length ), FALSE );
		$items = $pagination_info['objects'];
		$data = array();
		// fetch additional information for the result set:
		foreach ( $items as $item ) {
			$item_data = array();
			switch ( $item_to_feed[ $item->get_id() ]['type'] ) {
				case 'weblog':
					$item_data['attributes'] = $item->get_attributes( array( 'DATE_TITLE', DOC_LAST_MODIFIED, DOC_USER_MODIFIED, OBJ_CREATION_TIME ), TRUE );
					$item_data['creator'] = $item->get_creator( TRUE );
					break;
				case 'discussion board':
					$item_data['attributes'] = $item->get_attributes( array( OBJ_NAME, DOC_LAST_MODIFIED, DOC_USER_MODIFIED, OBJ_CREATION_TIME ), TRUE );
					$item_data['creator'] = $item->get_creator( TRUE );
					$item_data['annotating'] = $item->get_annotating( TRUE );
					break;
				case 'document':
					$item_data['attributes'] = $item->get_attributes( array( DOC_LAST_MODIFIED, DOC_USER_MODIFIED, OBJ_CREATION_TIME ), TRUE );
					$item_data['creator'] = $item->get_creator( TRUE );
					break;
			}
			$data[ $item->get_id() ] = $item_data;
		}
		$results = $GLOBALS['STEAM']->buffer_flush();
		// return results:
		$feeds = array();
    $authors = array();
		foreach ( $items as $item ) {
			$feed = array();
			foreach ( $item_to_feed[ $item->get_id() ] as $key => $value )
				$feed[ $key ] = $value;
			$item_data = $data[ $item->get_id() ];
			$attributes = $results[ $item_data['attributes'] ];
			switch ( $feed['type'] ) {
				case 'weblog':
					$feed['url'] = PATH_URL . 'weblog/' . $feed['feed_obj']->get_id() . '/';
					$title = $attributes['DATE_TITLE'];
					if ( ! is_string( $title ) ) $title = $feed['feed_obj']->get_attribute( 'DATE_TITLE' );
					$feed['title'] = $title;
					break;
				case 'discussion board':
					$annotating = $results[ $item_data['annotating'] ];
					// item is a discussion thread:
					if ( is_object($annotating) && $annotating->get_id() == $feed['feed_obj']->get_id() )
						$feed['url'] = PATH_URL . 'forums/' . $item->get_id() . '/';
					// item is a post in a discussion thread:
					else
						$feed['url'] = PATH_URL . 'forums/' . $annotating->get_id() . '/';
					$feed['title'] = $attributes[OBJ_NAME];
					break;
				case 'document':
					$feed['url'] = PATH_URL . 'doc/' . $feed['obj']->get_id() . '/';
					$feed['title'] = $feed['obj']->get_name();
					break;
			}
			if ( $feed['feed_obj']->get_id() != $item->get_id() ) $feed['url'] .= '#comment' . $item->get_id();
			$feed[ 'obj' ] = $item;
			$feed[ 'date' ] = $attributes[ DOC_LAST_MODIFIED ];
			if ( $feed['date'] == 0 ) $feed['date'] = $attributes[ OBJ_CREATION_TIME ];
			$feed[ 'author' ] = $attributes[ DOC_USER_MODIFIED ];
			if ( ! is_object( $feed['author'] ) ) $feed['author'] = $results[ $item_data['creator'] ];
      $authors[] = $feed['author'];
			$url = $feed['url'];
			if ( $feed['feed_obj']->get_id() != $item->get_id() ) $url .= '#comment' . $item->get_id();
			$feeds[] = $feed;
		}
    // Pre-Load Author-Data
    steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), $authors, array(USER_FIRSTNAME, USER_FULLNAME));
    
		if ( $return_pagination_info ) {
			$pagination_info[ 'feeds' ] = $feeds;
			return $pagination_info;
		}
		else
			return $feeds;
	}
}
?>

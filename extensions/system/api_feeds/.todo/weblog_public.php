<?php
require_once( "../../../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

//lms_steam::connect(); //http_auth function is used instead
require_once( PATH_LIB . "http_auth_handling.inc.php" );

if( http_auth() )
{
	if ( ! $object = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "id" ] ) )
	{
		include( PATH_PUBLIC . "bad_link.php" );
		exit;
	}
	$forum = $object;
	if (is_object($object) && !($object instanceof steam_container) ) {
	  // Generate the RSS from an entry of a weblog
	  $rss_channel = new RssChannel(
			$forum->get_attribute("DATE_TITLE"),
			PATH_URL . "weblog/" . $_GET[ "id" ] . "/",
			""
		);
	  $rss_channel->generate_http_header();
	  print $rss_channel->generate_xml_header();
	  $max_items = 20;

	  $discussion = lms_weblog::get_item_data( $object );
	  print $rss_channel->generate_item(
			$discussion[ "OBJ_NAME" ] . " " . gettext("by") . " " . $discussion[ "AUTHOR" ],
			"",
			BBCode($discussion[ "CONTENT" ]),
			$discussion[ "AUTHOR" ],
			$discussion[ "DATE_START_DATE" ],
			"",
			PATH_URL . "weblog/" . $_GET[ "id" ] . "/#comment" . $discussion[ "OBJ_ID" ]
		);

	  $cache = get_cache_function( $_GET[ "id" ], 600 );
	  $discussions = $cache->call( "lms_steam::get_annotations", $_GET[ "id" ] );

	  while( ( list( $id, $discussion ) = each( $discussions ) ) && $max_items > 0 )
	  {
	    print $rss_channel->generate_item(
	      gettext("Comment by ") . $discussion[ "OBJ_CREATOR" ],
	      "",
	      BBCode($discussion[ "CONTENT" ]),
	      $discussion[ "OBJ_CREATOR" ],
	      $discussion[ "OBJ_CREATION_TIME" ],
	      "",
	      PATH_URL . "weblog/" . $_GET[ "id" ] . "/#comment" . $forum->get_id()
	    );
	    $max_items--;
	  }
	  print $rss_channel->generate_xml_footer();
	} else {
	  // Generate RSS for the Weblog container
	  $rss_channel = new RssChannel(
			$forum->get_name(),
			PATH_URL . "weblog/" . $_GET[ "id" ] . "/",
			""
		);
	  $rss_channel->generate_http_header();
	  print $rss_channel->generate_xml_header();

	  $cache = get_cache_function( $_GET[ "id" ], 600 );
	  $discussions = $cache->call( "lms_weblog::get_items", $_GET[ "id" ] );

	  $max_items = 20;
	  while( ( list( $id, $discussion ) = each( $discussions ) ) && $max_items > 0 )
	  {
	    print $rss_channel->generate_item(
	      $discussion[ "OBJ_NAME" ],
	      "",
	      BBCode($discussion[ "CONTENT" ]),
	      $discussion[ "AUTHOR" ],
	      $discussion[ "DATE_START_DATE" ],
	      "",
	      PATH_URL . "weblog/" . $discussion[ "OBJ_ID" ] . "/"
	    );
	    $max_items--;
	  }

	  print $rss_channel->generate_xml_footer();
	}
} else {
  $rss_channel = new PodcastRSS(
		"Access denied",
		PATH_SERVER . $_SERVER["REQUEST_URI"],
		"You are not allowed to access this RSS Feed. Please check your username and password"
	);
  $rss_channel->generate_http_header();
  $rss_channel->generate_xml_header(TRUE);
  exit;
}
?>
<?php
require_once( "../../../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );


//lms_steam::connect(); //http_auth function is used instead
require_once( PATH_LIB . "http_auth_handling.inc.php" );

if( http_auth() )
{

	if ( ! $forum = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "id" ] ) )
	{
		include( PATH_PUBLIC . "bad_link.php" );
		exit;
	}

	$rss_channel = new RssChannel(
			$forum->get_name(),
			PATH_URL . "/forums/" . $_GET[ "id" ] . "/",
			""
		);

	$rss_channel->generate_http_header();
	print $rss_channel->generate_xml_header();
	$cache = get_cache_function( $_GET[ "id" ], 600 );
	$discussions = $cache->call( "lms_forum::get_discussions", $_GET[ "id" ] );
	$max_items = 20;
	while( ( list( $id, $discussion ) = each( $discussions ) ) && $max_items > 0 )
	{
		print $rss_channel->generate_item(
			$discussion[ "OBJ_NAME" ],
			"",
			BBCode($discussion[ "CONTENT" ]),
			$discussion[ "LATEST_POST_AUTHOR" ],
			$discussion[ "LATEST_POST_TS" ],
			"",
			PATH_URL . "forums/" . $discussion[ "OBJ_ID" ] . "/"
		);
		$max_items--;
	}

	print $rss_channel->generate_xml_footer();
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

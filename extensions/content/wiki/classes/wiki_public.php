<?php
require_once( "../../../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "wiki_handling.inc.php" );

//lms_steam::connect(); //http_auth function is used instead
require_once( PATH_LIB . "http_auth_handling.inc.php" );

if( http_auth() )
{

	if ( !$wiki = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "id" ] ) )
	{
		include( PATH_PUBLIC . "bad_link.php" );
		exit;
	}
	
	$rss_channel = new RssChannel(
			$wiki->get_name(),
			PATH_URL . "/wiki/" . $_GET[ "id" ] . "/",
			""
		);

	$rss_channel->generate_http_header();
	print $rss_channel->generate_xml_header();
	$cache = get_cache_function( $_GET[ "id" ], 600 );
	$entries = $cache->call( "koala_wiki::get_items", $_GET[ "id" ] );
	$max_entries = 20;

	while( ( list( $id, $entry ) = each( $entries ) ) && $max_entries > 0 )
	{
		$object = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $entry[ "OBJ_ID" ] );
		$content = wiki_to_html_plain( $object );
		
		print $rss_channel->generate_item(
			str_replace( ".wiki", "", $entry[ "OBJ_NAME" ] ),
			"",
			$content,
			$entry[ "DOC_USER_MOFIFIED" ],
			$entry[ "DOC_LAST_MODIFIED" ],
			"",
			PATH_URL . "wiki/" . $entry[ "OBJ_ID" ] . "/"
		);
		
		$max_entries--;
	}

	print $rss_channel->generate_xml_footer();
}
else {
	$podcast = new PodcastRSS(
					"Access denied",
					PATH_SERVER . $_SERVER["REQUEST_URI"],
					"You are not allowed to access this RSS Feed. Please check your username and password"
				   );
	
	$podcast->generate_http_header();
	$podcast->generate_xml_header( TRUE );
	exit;
}
?>
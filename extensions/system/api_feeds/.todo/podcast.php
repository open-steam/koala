<?php
require_once( "../../../etc/koala.conf.php" );

include( PATH_PUBLIC . "bad_link.php" );
exit;

//not in use
/*
lms_steam::connect();

if ( ! $weblog = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "id" ] ) )
{
	include( PATH_PUBLIC . "bad_link.php" );
	exit;
}

if ( ! $weblog instanceof steam_calendar )
{
	include( PATH_PUBLIC . "bad_link.php" );
	exit;
}

$podcast = new PodcastRSS(
		$weblog->get_name(),
		PATH_URL . "weblog/" . $weblog->get_id() . "/",
		$weblog->get_attribute( "OBJ_DESC" )
	);

$podcast->generate_http_header();
$podcast->generate_xml_header();

$cache = get_cache_function( $_GET[ "id" ], 6000 );
$items = $cache->call( "lms_weblog::get_items", $_GET[ "id" ] );


foreach( $items as $item ) {
	if ( ( $item[ "DATE_PODCAST" ] == 0 ) || ( empty( $item[ "DATE_PODCAST" ] ) ) )
	{
		continue;
	}
	{

	}
	$enclosure = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $item[ "DATE_PODCAST" ] );
	$podcast->generate_item(
		array(
			"title"  => $item[ "DATE_TITLE" ],
			"itunes:author" => $item[ "AUTHOR" ],
			"description" => "eine kleine Beschreibung",
			"pubDate" => date( "c", $item[ "DATE_START_DATE" ] ),
			"link" => PATH_URL . "stream/" . $enclosure->get_id() . ".mp3",
			"guid" => PATH_URL . "stream/" . $enclosure->get_id() . ".mp3",
			"itunes:explicit" => "No",
			"itunes:block" => "No",
			"itunes:keywords" => implode( ", ", $item[ "OBJ_KEYWORDS" ] )
		),
		PATH_URL . "stream/" . $enclosure->get_id() . ".mp3",
		$enclosure->get_content_size(),
		$enclosure->get_attribute( "DOC_MIME_TYPE" )
	);
}

$podcast->generate_xml_footer();
*/
?>
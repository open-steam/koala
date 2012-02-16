<?php
require_once( "../../../etc/koala.conf.php" );

function generate_podcast_block() {
  $podcast = new PodcastRSS(
		"Podcast offline",
		PATH_SERVER . $_SERVER["REQUEST_URI"],
		"This Podcast is unavailable"
	);
  $podcast->generate_http_header();
  $podcast->generate_xml_header(TRUE);
  exit;
}

//lms_steam::connect(); //http_auth function is used instead
require_once( PATH_LIB . "http_auth_handling.inc.php" );

if( http_auth() )
{

	if ( ! $container = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "id" ] ) )
	{
	  generate_podcast_block();
	}

	if ( ! $container instanceof steam_container )
	{
	  generate_podcast_block();
	}

	$world_users = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), "Everyone" );

	$readall = $container->check_access_read($world_users, TRUE);
	$is_podcast = $container->get_attribute("KOALA_CONTAINER_TYPE", TRUE);
	$result = $GLOBALS["STEAM"]->buffer_flush();
	$readall = $result[$readall];
	$is_podcast = $result[$is_podcast] === "container_podcast_koala";

	if ( !$readall || !$is_podcast) {
	  generate_podcast_block();
	}

	$koala_container = koala_object::get_koala_object( $container );

	$author_data = $container->get_creator()->get_attributes(array(USER_FIRSTNAME, USER_FULLNAME));
	$author_name = $author_data[USER_FIRSTNAME] . " " . $author_data[USER_FULLNAME];
	$pubdate = $container->get_attribute(OBJ_CREATION_TIME);
	$desc = $container->get_attribute(OBJ_DESC);

	$podcast = new PodcastRSS(
			h($koala_container->get_display_name()),
			PATH_SERVER . $_SERVER["REQUEST_URI"],
			h($desc),
	    $pubdate,
	    h($author_name)
		);

	$podcast->generate_http_header();
	$podcast->generate_xml_header();

	// Cache Podcast for 30 Minutes
	$cache = get_cache_function( $_GET[ "id" ], 1800 );
	$items = $cache->call( "koala_container::get_items", $_GET["id"] );
	//$items = koala_container::get_items( $_GET["id"] );

	foreach( $items as $item ) {
	  if (($item["CLASSTYPE"] & CLASS_DOCUMENT) > 0) {
	    $use_enclosure = TRUE;
	    //$path = PATH_URL . "cached/get_document.php?id=" . $item["OBJ_ID"];
	    $path = PATH_URL . "podload/" . urlencode(h($item[OBJ_NAME])) . "?id=" . $item["OBJ_ID"];
	    $podcast->generate_item(
	      array(
	        "title"  => h($item[ OBJ_NAME ]),
	        "itunes:author" => h($item[ "AUTHOR" ]),
	        "description" => h($item[OBJ_DESC]),
	        "pubDate" => strftime("%a, %d %b %Y %H:%M:%S GMT", $item[OBJ_CREATION_TIME]),
	        "link" => $path,
	        "guid" => $path,
	        "itunes:explicit" => "No",
	        "itunes:block" => "No",
	        "itunes:keywords" => is_array($item[ OBJ_KEYWORDS ] )?implode( ", ", $item[ OBJ_KEYWORDS ] ):""
	      ),
	      $use_enclosure,
	      $use_enclosure?$path:"",
	      $use_enclosure?$item["CONTENTSIZE"]:"",
	      $use_enclosure?$item[DOC_MIME_TYPE]:"" );
	  } else if (false && ($item["CLASSTYPE"] & CLASS_CONTAINER) > 0) {
	    // DISABLED at the moment
	    $use_enclosure = FALSE;
	    $path = PATH_URL . "cached/get_document.php?id=" . $item["OBJ_ID"];
	    $podcast->generate_item(
	      array(
	        "title"  => h($item[ OBJ_NAME ]),
	        "itunes:author" => h($item[ "AUTHOR" ]),
	        "description" => h($item[OBJ_DESC]),
	        "pubDate" => strftime("%a, %d %b %Y %H:%M:%S GMT", $item[OBJ_CREATION_TIME]),
	        "link" => $path,
	        "guid" => $path,
	        "itunes:explicit" => "No",
	        "itunes:block" => "No",
	        "itunes:keywords" => is_array($item[ OBJ_KEYWORDS ] )?implode( ", ", $item[ OBJ_KEYWORDS ] ):""
	      ),
	      $use_enclosure,
	      $use_enclosure?$path:"",
	      $use_enclosure?$item["CONTENTSIZE"]:"",
	      $use_enclosure?$item[DOC_MIME_TYPE]:"" );
	  } else if (false && ($item["CLASSTYPE"] & CLASS_SDOCEXTERN) > 0) {
	    // DISABLED because Links cannot be displayed with enclosure in general => That leads into validation error of the podcast and therefore malfunction of the whole Cast
	    // TODO: Implement Option "Use in Podcast" and require a file as destination in attribute DOC_EXTERN_URL
	    $use_enclosure = TRUE;
	    $path = $item["DOC_EXTERN_URL"];
	    $podcast->generate_item(
	      array(
	        "title"  => h($item[ OBJ_NAME ]),
	        "itunes:author" => h($item[ "AUTHOR" ]),
	        "description" => h($item[OBJ_DESC]),
	        "pubDate" => strftime("%a, %d %b %Y %H:%M:%S GMT", $item[OBJ_CREATION_TIME]),
	        "link" => $path,
	        "guid" => $path,
	        "itunes:explicit" => "No",
	        "itunes:block" => "No",
	        "itunes:keywords" => is_array($item[ OBJ_KEYWORDS ] )?implode( ", ", $item[ OBJ_KEYWORDS ] ):""
	      ),
	      $use_enclosure,
	      $use_enclosure?$path:"",
	      $use_enclosure?0:"",
	      $use_enclosure?$item[DOC_MIME_TYPE]:"" );
	  }
	}
	$podcast->generate_xml_footer();
} else {
  $podcast = new PodcastRSS(
		"Access denied",
		PATH_SERVER . $_SERVER["REQUEST_URI"],
		"You are not allowed to access this RSS Feed. Please check your username and password"
	);
  $podcast->generate_http_header();
  $podcast->generate_xml_header(TRUE);
  exit;
}
?>

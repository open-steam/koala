<?php
require_once( "../../../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

//lms_steam::connect(); //http_auth function is used instead
require_once( PATH_LIB . "http_auth_handling.inc.php" );

if( http_auth() )
{
	if ( ! $discussion = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "id" ] ) )
	{
		include( PATH_PUBLIC . "bad_link.php" );
		exit;
	}

	$rss_channel = new RssChannel(
			$discussion->get_name(),
			PATH_URL . "doc/" . $_GET[ "id" ] . "/",
			""
		);

	$rss_channel->generate_http_header();
	print $rss_channel->generate_xml_header();

	$object = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $_GET[ "id" ]);
	if ( is_object($object) ) {
	    $data_tnr["ATTRIBUTES"] = $object->get_attributes(array(OBJ_NAME, OBJ_CREATION_TIME, OBJ_DESC), TRUE);
	    $data_tnr["CREATOR"] = $object->get_creator(TRUE);
	    $data_result = $GLOBALS["STEAM"]->buffer_flush();
	    $creator = $data_result[$data_tnr["CREATOR"]];
	    steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), array($creator), array(USER_FULLNAME, USER_FIRSTNAME));

	  	print $rss_channel->generate_item(
			$data_result[$data_tnr["ATTRIBUTES"]][ OBJ_NAME ] . " " . gettext("by") . " " . $creator->get_attribute(USER_FIRSTNAME) . " " . $creator->get_attribute(USER_FULLNAME),
			"",
			BBCode($data_result[$data_tnr["ATTRIBUTES"]][ OBJ_DESC ]),
			$creator->get_attribute(USER_FIRSTNAME) . " " . $creator->get_attribute(USER_FULLNAME),
			$data_result[$data_tnr["ATTRIBUTES"]][ OBJ_CREATION_TIME ],
			"",
			PATH_URL . "doc/" . $_GET[ "id" ] . "/#comment" . $object->get_id()
		);
	}

	$cache = get_cache_function( $_GET[ "id" ], 600 );
	$discussions = $cache->call( "lms_steam::get_annotations", $_GET[ "id" ] );

	foreach( $discussions as $discussion )
	{
		print $rss_channel->generate_item(
			gettext("Post by ") . $discussion[ "OBJ_CREATOR" ],
			"",
			BBCode($discussion[ "CONTENT" ]),
			$discussion[ "OBJ_CREATOR" ],
			$discussion[ "OBJ_CREATION_TIME" ],
			"",
			PATH_URL . "doc/" . $_GET[ "id" ] . "/#comment" . $discussion[ "OBJ_ID" ]
		);
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
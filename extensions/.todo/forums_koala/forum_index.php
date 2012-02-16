<?php

include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "url_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$user = lms_steam::get_current_user();
$rss_feeds = $user->get_attribute( "USER_RSS_FEEDS" );

$path = url_parse_rewrite_path( $_GET[ "path" ] );

if ( ! $messageboard = steam_factory::get_object( $STEAM->get_id(), $_GET[ "id" ] ) )
{
	include( "bad_link.php" );
	exit;
}

if ( $messageboard instanceof steam_document )
{
	$thread       = $messageboard;
	$messageboard = $thread->get_annotating();

	define( "OBJ_ID",	$thread->get_id() );
	if ( ! $thread->check_access_read( $user ) )
	{
		throw new Exception( "No rights to view this.", E_USER_RIGHTS );
	}
}
else
{
	define( "OBJ_ID",	$messageboard->get_id() );
	if ( ! $messageboard->check_access_read( $user ) )
	{
		throw new Exception( "No rights to view this.", E_USER_RIGHTS);
	}
}

if ( ! $messageboard instanceof steam_messageboard )
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

switch( TRUE)
{
	case ( ! ( stripos( $path[ 0 ], "deletecomment" ) === FALSE  ) ):
		$comment_id = substr( $path[ 0 ], 13 );
	include( "comment_delete.php" );
	break;
	case ( ! ( stripos( $path[ 0 ], "editcomment" ) === FALSE ) ):
		$comment_id = substr( $path[ 0 ], 11 );
	include( "comment_edit.php" );
	break;

	case ( isset($thread) && $thread ):
	include( "forum_discussion.php" );
	break;
	case ( $path[ 0 ] == "new"  ):
		include( "forum_post.php" );
	break;
	case ( $path[ 0 ] == "delete"  ):
		include( "forum_delete.php" );
	break;
	case ( $path[ 0 ] == "edit"  ):
		include( "forum_edit.php" );
	break;
	default:
		include( "forum_all_topics.php" );
	break;
}

?>

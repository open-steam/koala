<?php
// no direct call
if (!defined('_VALID_KOALA')) {
	header("location:/");
	exit;
}
$comment = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $comment_id );
$user    = lms_steam::get_current_user();

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $comment->check_access_write( $user ) )
{
	$values = $_POST[ "values" ];
	if ( $values[ "delete" ] )
	{
    $_SESSION[ "confirmation" ] = str_replace("%NAME", h($comment->get_name()), gettext( "The comment '%NAME' has been deleted." ));
    
	$annotating = $comment->get_annotating();
	$annotating->remove_annotation( $comment );
    lms_steam::delete( $comment );

		require_once( "Cache/Lite.php" );
		$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
		$cache = get_cache_function( OBJ_ID, 600 );
		$cache->drop( "lms_steam::get_annotations", OBJ_ID );
		// Handle Related Cache-Data
		require_once( "Cache/Lite.php" );
		$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
		$cache->clean( OBJ_ID );
		// clean forumcache
    $fcache = get_cache_function( OBJ_ID, 600 );
    $fcache->drop( "lms_forum::get_discussions",  OBJ_ID );
    // clean cache for Weblog RSS Feed for the Comments
    $fcache->drop( "lms_steam::get_annotations", OBJ_ID );
    // clean rsscache
    $rcache = get_cache_function( "rss", 600 );
    $feedlink = PATH_URL . "services/feeds/forum_public.php?id=" . OBJ_ID;
		$rcache->drop( "lms_rss::get_items", $feedlink );
    $feedlink = PATH_URL . "services/feeds/weblog_public.php?id=" . OBJ_ID;
		$rcache->drop( "lms_rss::get_items", $feedlink );
    
		header( "Location: " . $values[ "return_to" ] );
		exit;
	}
	
}
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "comment_delete.template.html" );
if ( $comment->check_access_write( $user ) )
{
	$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "Are you sure you want to delete this comment?" ) );
  if (isset($document)) {
    $content->setVariable( "DELETE_BACK_LINK", PATH_URL . 
			       "doc/" . OBJ_ID . "/");
  } else if (isset($weblog)) {
    $content->setVariable( "DELETE_BACK_LINK", PATH_URL . 
			       "weblog/" . OBJ_ID . "/");
  } else {
    $content->setVariable( "DELETE_BACK_LINK", PATH_URL . 
			       "forums/" . OBJ_ID . "/");
  }
	$content->setCurrentBlock( "BLOCK_DELETE" );
	$content->setVariable( "FORM_ACTION", $_SERVER[ "REQUEST_URI" ] );
	$content->setVariable( "LABEL_DELETE_IT", gettext( "yes, delete it" ) );
	$content->setVariable( "BACK_LINK", $_SERVER[ "HTTP_REFERER" ] );
	$content->setVariable( "LABEL_RETURN", gettext( "back" ) );
	$content->parse( "BLOCK_DELETE" );
}
else
{
	$content->setVariable( "LABEL_ARE_YOU_SURE", gettext( "You have no rights to delete this comment!" ) );
}
$content->setVariable( "TEXT_COMMENT", get_formatted_output( $comment->get_content() ) );
$creator = $comment->get_creator();
$creator_data = $creator->get_attributes( array( "USER_FULLNAME", "USER_FIRSTNAME", "OBJ_ICON" ) );
$content->setVariable( "LABEL_FROM_AND_AGO", str_replace( "%N", "<a href=\"" . PATH_URL . "/user/" . $creator->get_name() . "/\">" . h($creator_data[ "USER_FIRSTNAME" ]) . " " . h($creator_data[ "USER_FULLNAME" ]) . "</a>", gettext( "by %N" ) ) . "," . how_long_ago( $comment->get_attribute( "OBJ_CREATION_TIME" ) )  );

$icon = $creator_data[ "OBJ_ICON" ];
if ( $icon instanceof steam_object )
{
	$icon_id = $icon->get_id();
}
else
{
	$icon_id = 0;
}

$content->setVariable( "ICON_SRC", PATH_URL . "get_document.php?id=" . $icon_id );

$rootlink = lms_steam::get_link_to_root( $messageboard );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "link" => PATH_URL . "forums/" . $messageboard->get_id() . "/", "name" => $messageboard->get_name() ),
				array( "link" => "", "name" => gettext("Delete comment") )
			);
			
$portal->set_page_main(
	$headline,
	$content->get(),
	""
);
$portal->show_html();

?>

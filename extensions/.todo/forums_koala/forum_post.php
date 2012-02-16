<?php
include_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "forum_post.template.html" );
$headline = gettext( "Post a new topic" );

$backlink = ( empty( $_POST["values"]["backlink"] ) ) ? $_SERVER[ "HTTP_REFERER" ] : $_POST[ "values" ][ "backlink" ];

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = $_POST[ "values" ];
	$problems = "";

	if ( empty( $values[ "title" ] ) ) $problems = gettext("Please enter a subject for your message.");
	if ( empty( $values[ "body" ] ) ) $problems .= (empty($problems)) ? gettext("Please enter your message.") : "<br>" . gettext("Please enter your message.");

	if ( get_magic_quotes_gpc() )
	{
		if ( !empty( $values['title'] ) ) $values['title'] = stripslashes( $values['title'] );
		if ( !empty( $values['body'] ) ) $values['body'] = stripslashes( $values['body'] );
	}

	if (!empty($problems)) $portal->set_problem_description($problems);

	if ( ! empty( $values[ "preview_comment" ] ) )
	{
		$content->setCurrentBlock( "BLOCK_PREVIEW_COMMENT" );
		$content->setVariable( "TEXT_COMMENT", get_formatted_output( $values[ "body" ] )  );
		$content->setVariable( "LABEL_PREVIEW_YOUR_COMMENT", gettext( "Preview your comment" ) );
		$template->parse( "BLOCK_PREVIEW_COMMENT" );
		$headline = gettext( "Change it?" );
	}

	if ( ! empty( $values[ "save" ] ) && empty( $problems ) )
	{
    	if ( !strpos($values[ "title" ], "/" ))
    	{
      		$new_thread = $messageboard->add_thread( $values[ "title" ], $values[ "body" ] );
//      		$all_user = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), STEAM_ALL_USER );
//      		$new_thread->set_acquire( FALSE );
//      		$new_thread->set_read_access( $all_user, TRUE );
//      		$new_thread->set_write_access( $all_user, FALSE );
//      		$new_thread->set_annotate_access( $all_user, TRUE );
      		// Handle Related Cache-Data
      		require_once( "Cache/Lite.php" );
      		$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
      		$cache->clean( OBJ_ID );
      		// clean forumcache
      		$fcache = get_cache_function( $_GET[ "id" ], 600 );
      		$fcache->drop( "lms_forum::get_discussions", $_GET[ "id" ] );
      		// clean rsscache of the forum
      		$feedlink = PATH_URL . "services/feeds/forum_public.php?id=" . $_GET["id"];
      		$rcache = get_cache_function( "rss", 600 );
      		$rcache->drop( "lms_rss::get_items", $feedlink );

      		header( "Location: " . PATH_URL . "forums/" . $new_thread->get_id() . "/" );
      		exit;
    	}
    	else
    	{
      		$portal->set_problem_description(gettext("Please don't use the \"/\"-char in the title."));
    	}
	}

	if (!empty($values[ "preview" ]) && !empty( $values['body'] ))
	{
		// PREVIEW

		$content->setCurrentBlock( "BLOCK_PREVIEW" );
		$content->setVariable( "LABEL_PREVIEW_EDIT", gettext( "Preview the edit" ) );
		$content->setVariable( "PREVIEW_EDIT", get_formatted_output( $values[ "body" ] ) );
		$content->parse( "BLOCK_PREVIEW" );
		$headline =  gettext( "Change it?" );
	}
}

$subject = (isset($values[ "title" ])?htmlentities( $values[ "title" ], ENT_NOQUOTES, "utf-8" ):"");
$text = (isset($values[ "body" ])?htmlentities( $values[ "body" ], ENT_NOQUOTES, "utf-8" ):"");

$content->setVariable( "INFO_TEXT", $headline );
$content->setVariable( "LABEL_TOPIC", gettext( "Topic" ) );
$content->setVariable( "LABEL_YOUR_POST", gettext( "Your Post") );
$content->setVariable( "TEXT_COMMENT", $text );
$content->setVariable( "TITLE_COMMENT", $subject );
$content->setVariable( "LABEL_PREVIEW", gettext( "Preview" ) );
$content->setVariable( "LABEL_SAVE_CHANGES", gettext( "Create entry" ) );
$content->setVariable( "VALUE_BACKLINK", $backlink );
$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );

$content->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
$content->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
$content->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
$content->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
$content->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
$content->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
$content->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
$content->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
$content->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
$content->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
$content->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
$content->setVariable( "HINT_BB_URL", gettext( "web link" ) );
$content->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
$content->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );

$portal->set_rss_feed( PATH_URL . "services/feeds/forum_public.php?id=$id", gettext( "Feed" ), str_replace( "%l", $login, gettext( "Subscribe to this forum's Newsfeed" ) ) );

$rootlink = lms_steam::get_link_to_root( $messageboard );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "link" => PATH_URL . "forums/" . $messageboard->get_id() . "/", "name" => $messageboard->get_name() ),
				array( "link" => "", "name" => gettext( "New Thread"))
			);

$portal->set_page_main(
		$headline,
		$content->get()
		);
$portal->show_html();
?>
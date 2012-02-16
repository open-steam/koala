<?php
require_once( PATH_LIB . "format_handling.inc.php" );
require_once( PATH_LIB . "comments_handling.inc.php" );
$html_entry = new HTML_TEMPLATE_IT();
$html_entry->loadTemplateFile( PATH_TEMPLATES . "weblog_entry.template.html" );

$weblog_html_handler = new lms_weblog( $weblog );

if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "bookmark_rss" )
{
  lms_steam::user_add_rssfeed( $weblog->get_id(), PATH_URL . "services/feeds/weblog_public.php?id=" . $weblog->get_id(), "weblog", lms_steam::get_link_to_root( $weblog ) );
  $_SESSION["confirmation"] = str_replace( "%NAME", h($weblog->get_name()), gettext( "You are keeping an eye on '%NAME' from now on." ) );
  header( "Location: " . PATH_URL . "weblog/" . $date->get_id() . "/" );
  exit;
}

if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "delete_bookmark" )
{
  $user = lms_steam::get_current_user();
	$id = (int)$_GET[ "unsubscribe" ];
  $feeds = $user->get_attribute("USER_RSS_FEEDS");
  if (!is_array($feeds)) $feeds = array();
	unset( $feeds[ $id ] );
	$user->set_attribute( "USER_RSS_FEEDS", $feeds );
  $_SESSION["confirmation"] = str_replace("%NAME", h($weblog->get_name()), gettext( "subscription of '%NAME' canceled." ));
  header( "Location: " . PATH_URL . "weblog/" . $date->get_id() . "/" );
  exit;
}

$weblog_html_handler->set_menu( "entry" );
$weblog_html_handler->set_widget_categories();
$weblog_html_handler->set_widget_archive( 5 );

$entry = $date->get_attributes(
		array(
			"DATE_TITLE",
			"DATE_DESCRIPTION",
			"DATE_START_DATE",
			"DATE_CATEGORY",
			"OBJ_KEYWORDS"
		)
	);

$creator = $date->get_creator();

$html_entry->setVariable( "VALUE_ARTICLE_TEXT", get_formatted_output( h($date->get_attribute( "DATE_DESCRIPTION" )) ) );
$html_entry->setVariable( "VALUE_POSTED_BY", str_replace( "%NAME", "<a href=\"" . PATH_URL . "user/" . $creator->get_name(). "/\">" . h($creator->get_attribute( "USER_FIRSTNAME" )) . " " . h($creator->get_attribute( "USER_FULLNAME" )) . "</a>", gettext( "Posted by %NAME" )) );
$html_entry->setVariable( "VALUE_DATE_TIME", strftime( "%x %X", h($entry[ "DATE_START_DATE" ]) ) );
$category = $entry[ "DATE_CATEGORY" ];
if ( ! empty( $category ) )
{
	$html_entry->setVariable( "LABEL_IN", gettext( "in" ) );
	$html_entry->setVariable( "VALUE_CATEGORY", "<a href=\"" . PATH_URL . "weblog/" . $category->get_id() . "/\">" . h($category->get_name()) . "</a>" );
}
else
{
	$html_entry->setVariable( "VALUE_CATEGORY", gettext( "no category" ) );
}

$html_entry->setVariable( "POST_PERMALINK", PATH_URL . "weblog/" . $weblog->get_id() . "/#comment" . $date->get_id() );
$html_entry->setVariable( "POST_PERMALINK_LABEL", gettext( "permalink" ) );

$weblog_html_handler->set_main_html( $html_entry->get() . get_comment_html( $date, PATH_URL . "weblog/" . $date->get_id() ) );

$portal->set_rss_feed( PATH_URL . "services/feeds/weblog_public.php?id=" . OBJ_ID , gettext( "Feed" ), str_replace( "%l", (isset($login))?$login:'', gettext( "Subscribe to this forum's Newsfeed" ) ) );

$rootlink = lms_steam::get_link_to_root( $weblog );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
				array( "link" => "", "name" => h($date->get_attribute( "DATE_TITLE" )))
			);

$portal->set_page_main(
		$headline,
		$weblog_html_handler->get_html()
	);
$portal->show_html();
?>

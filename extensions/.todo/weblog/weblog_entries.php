<?php
$weblog_html_handler = new lms_weblog( $weblog );
$weblog_html_handler->set_menu( "entries" );

$grp = $weblog->get_environment()->get_creator();
steam_factory::load_attributes($GLOBALS[ "STEAM" ]->get_id(),  array($grp), array(OBJ_NAME, OBJ_TYPE));
if ($grp->get_name() == "learners" && $grp->get_attribute(OBJ_TYPE) == "course_learners") {
  $grp = $grp->get_parent_group();
}
$weblog_html_handler->set_widget_categories();
$all_date_objects = $weblog->get_date_objects( );
usort( $all_date_objects, "sort_dates" );   //sort_dates defined in steam_calendar.class
$weblog_html_handler->set_widget_archive( 5 );
$weblog_html_handler->set_widget_blogroll();
$weblog_html_handler->set_widget_access( $grp );

if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "bookmark_rss" )
{
  lms_steam::user_add_rssfeed( $weblog->get_id(), PATH_URL . "services/feeds/weblog_public.php?id=" . $weblog->get_id(), "weblog", lms_steam::get_link_to_root( $weblog ) );
  $_SESSION["confirmation"] = str_replace( "%NAME", h($weblog->get_name()), gettext( "You are keeping an eye on '%NAME' from now on." ) );
  header( "Location: " . PATH_URL . "weblog/" . $weblog->get_id() . "/" );
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
  header( "Location: " . PATH_URL . "weblog/" . $weblog->get_id() . "/" );
  exit;
}

//TODO what is the reason for this structure?
switch( TRUE )
{
  case ( isset($date) && $date ):
    $weblog_html_handler->print_entries( array( $date ), FALSE );
    break;

  default:
    $weblog_html_handler->print_entries( $all_date_objects );
    break;
}
$weblog_html_handler->set_podcast_link();
$portal->set_rss_feed( PATH_URL . "services/feeds/weblog_public.php?id=" . OBJ_ID , gettext( "Feed" ), str_replace( "%l", isset($login)?$login:'', gettext( "Subscribe to this forum's Newsfeed" ) ) );
$rootlink = lms_steam::get_link_to_root( $weblog );
$headline = array( $rootlink[0], $rootlink[1], array("link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication"))  ,  array( "link" => "", "name" =>  h($weblog->get_name()) ) );

$portal->set_page_main(
								$headline,
								$weblog_html_handler->get_html()
								);
$portal->show_html();
?>

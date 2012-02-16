<?php

$feeds = $user->get_attribute( "USER_RSS_FEEDS" );

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST"  )
{
	$unsubscribe = $_POST[ "unsubscribe" ];
	$ids = array_keys( $unsubscribe );
	foreach( $ids as $id )
	{
		unset( $feeds[ $id ] );
	}
	$user->set_attribute( "USER_RSS_FEEDS", $feeds );

	if ( count( $ids ) > 1 )
	{
		$portal->set_confirmation( str_replace( "%NO", count( $ids ), gettext( "%NO feed subscriptions cancelt." ) ) );
	}
	else
	{
		$portal->set_confirmation( gettext( "1 feed subscription canceled." ) );
	}
	 
}

$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
$content->loadTemplateFile( "rss_feeds_subscr.template.html" );
$content->setVariable( "INFO_SUBSCRIBED_FEEDS", gettext( "Here are the feeds you subscribed to." ) . " " . gettext( "Feel free to cancel these subscriptions, if they become uninteresting for your daily work." ) );
$content->setVariable( "LABEL_NAME", gettext( "Feed" ) );
$content->setVariable( "LABEL_TYPE", gettext( "Type") );
$content->setVariable( "LABEL_CONTEXT" , gettext( "Context" ) );
$content->setVariable( "LABEL_ACTION", gettext( "Action") );
if (!is_array($feeds) || count($feeds) == 0) {
    $content->setCurrentBlock( "BLOCK_NOFEED" );
    $content->setVariable( "LABEL_NOFEED", gettext( "You have no subscriptions.") );
    $content->parse( "BLOCK_NOFEED" );
} else {
  while( list( $id, $feed ) = each( $feeds ) )
  {
    $content->setCurrentBlock( "BLOCK_FEED" );
    $content->setVariable( "FEED_LINK", $feed[ "link" ] );
    $content->setVariable( "FEED_NAME", $feed[ "name" ] );
    $content->setVariable( "FEED_TYPE", secure_gettext( $feed[ "type"] ) );
    $content->setVariable( "FEED_CONTEXT_NAME", $feed[ "context_name" ] );
    $content->setVariable( "FEED_CONTEXT_LINK", $feed[ "context_link" ] );
    $content->setVariable( "FEED_ID", $id );
    $content->parse( "BLOCK_FEED" );
  }
  $content->setCurrentBlock( "BLOCK_FEEDACTION" );
  $content->setVariable( "LABEL_UNSUBSCRIBE", gettext( "Unsubscribe") );
  $content->parse( "BLOCK_FEEDACTION" );
}
$portal->set_page_title( "News Subscriptions" );
$portal->set_page_main( array( array( "link" => PATH_URL . "desktop/news/", "name" => gettext( "News" ) ), array( "link" => "", "name" => gettext( "Your Subscriptions" ) ) ), $content->get() );
$portal->show_html();

?>

<?php
require_once( "../etc/koala.conf.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

$nr_show = 10;
$start = $portal->get_paginator_start( $nr_show );

$koala_user = new koala_user( lms_steam::get_current_user() );

// Cache for 7 Minutes
$cache = get_cache_function( $user->get_name(), 420 );
$pagination_info = $cache->call( "koala_user::get_news_feeds_static", $start, $nr_show, TRUE, $user );
//$pagination_info = $koala_user->get_news_feeds( $start, $nr_show, TRUE );
if(isset($pagination_info[ 'feeds' ])){
	$news_items = $pagination_info[ 'feeds' ];
	$no_items = count( $news_items );
}else{
	$no_items = 0;
}

$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
$content->loadTemplateFile( "rss_feeds.template.html" );
$content->setVariable( "INFO_SUBSCRIBED_FEEDS", gettext( "Here are the newest entries of news feeds, discussion boards and weblogs you have subscribed." ) );

if (ADD_MEETINGS) {
	$content->setCurrentBlock( 'BLOCK_MANAGE_FEEDS' );
	$content->setVariable( "LINK_MANAGE_FEEDS", PATH_URL . "desktop/news/subscr/" );
	$content->setVariable( "LABEL_MANAGE_FEEDS", gettext( "Your Subscriptions" ) );
	$content->parse( 'BLOCK_MANAGE_FEEDS' );
}

//$no_items = count( $news_items );
if ( $no_items > 0 )
{
	$start = $portal->set_paginator( $content, $nr_show, $pagination_info['total'], '(' . gettext( '%TOTAL feeds total' ) . ')' );
	$end   = ( $start + $nr_show > $no_items ) ? $no_items : $start + $nr_show;

	//$content->setVariable( 'LABEL_NEWS', str_replace( array( '%a', '%z', '%s' ), array( $start + 1, $end, $no_items ), gettext( 'News (%a-%z out of %s)' ) ) );
	$content->setCurrentBlock( 'BLOCK_NEWS_AVAILABLE' );

	$content->setVariable( 'LABEL_AUTHOR', gettext( 'Author' ) );
	$content->setVariable( 'LABEL_SUBJECT', gettext( 'Subject' ) );
	$content->setVariable( 'LABEL_SOURCE', gettext( 'Source' ) );
	$content->setVariable( 'LABEL_CONTEXT', gettext( 'Context' ) );
	$content->setVariable( 'LABEL_POSTED', gettext( 'Posted' ) );
	$content->setVariable( 'LABEL_XML', gettext( 'RSS' ) );

	foreach ( $news_items as $feed )
	{
		$content->setCurrentBlock( 'BLOCK_NEWS' );
		$ts = $feed[ 'date' ];
		if ( $ts > $_SESSION[ 'last_login' ] )
		{
			$content->setCurrentBlock( 'BLOCK_NEW' );
			$content->setVariable( 'NEW_LABEL', gettext( 'New' ) );
			$content->parse( 'BLOCK_NEW' );
		}
		$content->setVariable( 'VALUE_AUTHOR', h($feed[ 'author' ]->get_full_name()) );
		$content->setVariable( 'VALUE_SUBJECT', h($feed[ 'title' ]) );
		$content->setVariable( 'VALUE_BODY', h($feed[ 'name' ]) );  //TODO: body?
		$content->setVariable( 'LINK_MESSAGE', h($feed[ 'url' ]) );
		$content->setVariable( 'VALUE_SOURCE', h($feed[ 'feed_obj' ]->get_name()) );
		$content->setVariable( 'VALUE_CONTEXT_LINK', h($feed[ 'context_link' ]) );
		$content->setVariable( 'VALUE_CONTEXT_NAME', h($feed[ 'context_name' ]) );
		$content->setVariable( 'VALUE_HOW_LONG_AGO', how_long_ago( $ts ) );
		$content->setVariable( 'LINK_RSS', h($feed[ 'link' ]) );
		$content->setVariable( 'RSS_SYMBOL', PATH_STYLE . 'images/feed-icon-16x16.png' );
		$content->parse( 'BLOCK_NEWS' );
	}

	$content->parse( 'BLOCK_NEWS_AVAILABLE' );
}
else
{
	$content->setVariable( 'LABEL_NEWS', gettext( 'No news available.' ) );
}

$portal->set_page_title( gettext( 'News' ) );
$portal->set_page_main( 
	array( array( 'link' => '', 'name' => gettext( 'News' ) ) ),
	$content->get()
);
$portal->show_html();
?>

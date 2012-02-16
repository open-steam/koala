<?php
require_once( PATH_LIB . "format_handling.inc.php" );

$weblog_html_handler = new lms_weblog( $weblog );

$weblog_html_handler->set_menu( "category" );
$weblog_html_handler->set_widget_categories();
$weblog_html_handler->set_widget_archive( 5 );

		$link_objects = $category->get_inventory( CLASS_LINK );

		$no_entries = count( $link_objects );
		$end = $no_entries;
		$start = 0;

		$date_objects = array();
		for( $i = $start; $i < $end ; $i++ )
		{
			$date_objects[ ] = $link_objects[ $i ]->get_source_object(); 
		}
    
    usort( $date_objects, "sort_dates" );   //sort_dates defined in steam_calendar.class
    
		$weblog_html_handler->print_entries( $date_objects );

$portal->set_rss_feed( PATH_URL . "services/feeds/weblog_public.php?id=" . OBJ_ID , gettext( "Feed" ), str_replace( "%l", $login, gettext( "Subscribe to this forum's Newsfeed" ) ) );

$rootlink = lms_steam::get_link_to_root( $weblog );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
				array( "link" => "", "name" => h( $category->get_name() ) )
			);




$portal->set_page_main(
		$headline,
		$weblog_html_handler->get_html()
	);
$portal->show_html();
?>

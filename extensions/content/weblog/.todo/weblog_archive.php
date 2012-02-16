<?php

require_once( PATH_LIB . "format_handling.inc.php" );

$weblog_html_handler = new lms_weblog( $weblog );

$weblog_html_handler->set_widget_categories();
$weblog_html_handler->set_widget_archive( );

$first_of_month = $timestamp;
$last_of_month = strtotime( "-1 day", strtotime( "+1 month", $first_of_month ) );

$date_objects = $weblog->get_date_objects( (int) $first_of_month, (int) $last_of_month );
// $date_objects = $weblog->get_date_objects( );
$no_entries = count( $date_objects );
$start = $portal->set_paginator( 5, $no_entries, gettext( "%TOTAL entries in this archive." ) );
$end = ( $start + 5 > $no_entries ) ? $no_entries : $start + 5;

$weblog_html_handler->print_entries( $date_objects, TRUE);

$portal->set_rss_feed( PATH_URL . "services/feeds/weblog_public.php?id=" . OBJ_ID , gettext( "Feed" ), str_replace( "%l", (isset($login))?$login:'', gettext( "Subscribe to this forum's Newsfeed" ) ) );

$rootlink = lms_steam::get_link_to_root( $weblog );
$headline = array(
				$rootlink[0],
				$rootlink[1],
				array( "link" => $rootlink[1]["link"] . "communication/", "name" => gettext("Communication")),
				array( "name" =>  h($weblog->get_name()), "link" => PATH_URL . "weblog/" . $weblog->get_id() . "/"),
				array( "link" => "", "name" => strftime( "%B %G", $timestamp ) )
			);

$portal->set_page_main(
		$headline,
		$weblog_html_handler->get_html()
		);
$portal->show_html();
?>
<?php

function get_calendar_html( $steam_calendar, $link, $timestamp = "" )
{
	if ( empty( $timestamp ) )
	{
		$timestamp = time();
	}

	$html = new HTML_TEMPLATE_IT();
	$html->loadTemplateFile( PATH_TEMPLATES . "widget_calendar.template.html" );
	$html->setVariable( "VALUE_MONTH", strftime( "%b %G", $timestamp ) );
	
	$html->setVariable( "ABBR_SUNDAY", "S" );
	$html->setVariable( "ABBR_MONDAY", "M" );
	$html->setVariable( "ABBR_TUESDAY", "T" );
	$html->setVariable( "ABBR_WEDNESDAY", "W" );
	$html->setVariable( "ABBR_THURSDAY", "T" );
	$html->setVariable( "ABBR_FRIDAY", "F" );
	$html->setVariable( "ABBR_SATURDAY", "S" );

	$month = date( "m", $timestamp );
	$year  = date( "Y", $timestamp );
	$next_month = ( $month == 12 ) ? 1 : $month + 1;
	$next_months_year = ( $month == 12 ) ? $year + 1: $year;
	$first_day_of_month = strtotime( "$year-$month-01" );
	$last_day_of_month  = strtotime( "-1 day", strtotime( "$next_months_year-$next_month-01" ) );

	$calendar_entries = $steam_calendar->get_entries( $first_day_of_month, $last_day_of_month );

	$weekday_offset = strftime( "%w", $first_day_of_month );
	$current_day    = $first_day_of_month;
	
	for( $w = 1; $w <= 6; $w++ ) // weeks
	{
		if ( $current_day > $last_day_of_month )
		{
			break;
		}
		$html->setCurrentBlock( "BLOCK_WEEK" );
		for( $wd = 0; $wd <= 6; $wd++ ) // weekdays
		{
			if ( $weekday_offset > 0 )
			{
				$html->setVariable( "WD$wd", "&nbsp;" );
				$weekday_offset--;
				continue;
			}
			if ( $current_day > $last_day_of_month )
			{
				$html->setVariable( "WD$wd", "&nbsp;" );
				$current_day = strtotime( "+1 day", $current_day );
				continue;
			}
			$wdstr     = strftime( "%e", $current_day );
			$wdentries = $calendar_entries[ $current_day ];
			$wdlabel = ( $wdentries > 0 ) ? "<b>$wdstr</b>" : $wdstr;
			$html->setVariable( "WD$wd", $wdlabel );
			$current_day = strtotime( "+1 day", $current_day );
		}
		
		$html->parse( "BLOCK_WEEK" );
	}
	
	return $html->get();	
}

?>

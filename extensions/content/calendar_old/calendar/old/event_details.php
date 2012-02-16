<?php

// $event has to be set
// $calendar has to be set
require_once( PATH_LIB . "format_handling.inc.php" );


function setting_variable( $template, $key, $value )
{
	$t = $GLOBALS[ $template ];

	if ( empty( $value ) )
	{
		$t->setVariable( $key, "- - -" );
	}
	else
	{
		$t->setVariable( $key, $value );
	}
}


if ( isset($event) && is_object( $event ) )
{
	$values = $event->get_attributes( array( "DATE_TITLE", "DATE_LOCATION", "DATE_START_DATE", "DATE_END_DATE", "DATE_DESCRIPTION", "DATE_URL" ) );
	$values[ "OBJ_ID" ] = $event->get_id();
}


$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
$content->loadTemplateFile( "event_details.template.html" );

$content->setVariable( "LABEL_TITLE", gettext( "Title" ) );
$content->setVariable( "LABEL_LOCATION", gettext( "Location" ) );
$content->setVariable( "LABEL_BEGIN", gettext( "Begin" ) );
$content->setVariable( "LABEL_END", gettext( "End" ) );
$content->setVariable( "LABEL_DATE_FORMAT", gettext( "YYYY-MM-DD" ) );
$content->setVariable( "LONG_DSC_LABEL", gettext( "Description" ) );
$content->setVariable( "LABEL_URL", gettext( "Website" ) );

if (isset($group) && is_object($group) && $group instanceof koala_group_default) // group calendar   test for $group instanceof koala_group_course for courses
{
	$content->setVariable( "LABEL_GOTO_CALENDAR", " <a href=\"" . $backlink[ "link" ] . "calendar/\">" . gettext( "back to your calendar" ) . "</a>" );
}
else
{
	$content->setVariable( "LABEL_GOTO_CALENDAR", " <a href=\"" . $backlink[ "link" ] . "\">" . gettext( "back to your calendar" ) . "</a>" );
}

setting_variable( "content", "VALUE_TITLE", h($values[ "DATE_TITLE" ]) );
setting_variable( "content", "VALUE_LOCATION", h($values[ "DATE_LOCATION" ]) );
setting_variable( "content", "VALUE_BEGIN", strftime("%A, %d.%m.%Y ", $values[ "DATE_START_DATE" ] ) );
setting_variable( "content", "VALUE_END", strftime("%A, %d.%m.%Y ", $values[ "DATE_END_DATE" ] ) );
$values[ "begin_time" ] = strftime( "%H:%M", $values[ "DATE_START_DATE" ] );
$values[ "end_time" ] = strftime( "%H:%M", $values[ "DATE_END_DATE" ] );
setting_variable( "content", "VALUE_BEGIN_TIME", $values[ "begin_time" ] );
setting_variable( "content", "VALUE_END_TIME", $values[ "end_time" ] );
setting_variable( "content", "VALUE_DESC", get_formatted_output($values[ "DATE_DESCRIPTION" ],ENT_QUOTES, "UTF-8") );

//setting_variable( "content", "VALUE_URL", h($values[ "DATE_URL" ]) );
if ( !empty( $values[ "DATE_URL" ] ) )
{
	$content->setVariable( "VALUE_URL", ("<a target=\"new\" href=\"".$values[ "DATE_URL" ]."\">".htmlentities($values[ "DATE_URL" ],ENT_QUOTES, "UTF-8")."</a>") );
}
else
{
	$content->setVariable( "VALUE_URL", "- - -" );
}

$headline = array( "link" => "", "name" => gettext( "Event details" ) );


$portal->set_page_main( array( $backlink, $headline ), $content->get() );
$portal->show_html();
?>
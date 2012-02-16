<?php

// $event has to be set
// $calendar has to be set
require_once( PATH_LIB . "format_handling.inc.php" );

if ( isset($event) && is_object( $event ) )
{
	$values = $event->get_attributes( array( "DATE_TITLE", "DATE_LOCATION", "DATE_START_DATE", "DATE_END_DATE", "DATE_DESCRIPTION", "DATE_URL" ) );
	$values[ "OBJ_ID" ] = $event->get_id();
}

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	/* not used here anymore
	if ( ! empty( $_POST[ "delete" ] ) )
	{
		$title = $event->get_attribute( "DATE_TITLE" );
    	lms_steam::delete( $event );
		$_SESSION[ "confirmation" ] = str_replace( "%TITLE", htmlentities($title,ENT_QUOTES, "UTF-8"), gettext( "Event %TITLE deleted." ) );
		header( "Location: " . $backlink[ "link" ] );
		exit;
	}
	*/

	$values = $_POST[ "values" ];
	$problems = "";
	$hints = "";

	$bDate = $values["BEGIN_YEAR"] . "-" . $values["BEGIN_MONTH"] . "-" . $values["BEGIN_DAY"];
	$bTime = $values["BEGIN_HOUR"] . ":" . $values["BEGIN_MINUTE"];
	$eDate = $values["END_YEAR"] . "-" . $values["END_MONTH"] . "-" . $values["END_DAY"];
	$eTime = $values["END_HOUR"] . ":" . $values["END_MINUTE"];
	
	if ( empty( $values[ "DATE_TITLE" ] ) )
	{
		$problems .= gettext( "The title for this event is missing." ) . " ";
		$hints    .= gettext( "Please enter a self-explanatory title for this event." ) . " ";
	}
	if ( empty( $bDate ) || empty( $eDate ) )
	{
		$problems .= gettext( "The length of time is not clearly specified."  ) . " ";
		$hints    .= gettext( "Please specify the beginning and the end of this event." );
	}
	if ( str_to_timestamp( $eDate, $eTime ) < str_to_timestamp( $bDate, $bTime ) )
	{
		$problems .= gettext( "The date ends before it starts!"  ) . " ";
		$hints    .= gettext( "Please correct start and end time so that start time is before end time." );
	}
	if ( empty( $problems ) )
	{
		$values[ "DATE_START_DATE" ]	= str_to_timestamp( $bDate, $bTime );
		if ( !empty($values["DATE_URL"]) && substr($values["DATE_URL"], 0, 7) != "http://" ) $values["DATE_URL"] = "http://" . $values["DATE_URL"];

		$date = array(
			"DATE_TITLE"			=>  $values[ "DATE_TITLE" ],
			"DATE_START_DATE"	=>  str_to_timestamp( $bDate, $bTime ),
			"DATE_END_DATE"		=>  str_to_timestamp( $eDate, $eTime ),
			"DATE_LOCATION"		=>  $values[ "DATE_LOCATION" ],
			"DATE_DESCRIPTION"=>	$values[ "DATE_DESCRIPTION" ],
			"DATE_URL"				=>  $values[ "DATE_URL" ]
		);
		if ( is_object( $event ) )
		{
			$event->set_attributes( $date );
			$_SESSION[ "confirmation" ] = str_replace( "%TITLE", htmlentities($values[ "DATE_TITLE" ],ENT_QUOTES, "UTF-8"), gettext( "Event %TITLE altered." ) );
		}
		else
		{
			$owner = $calendar->get_attribute( "CALENDAR_OWNER" );
			if ( $owner instanceof steam_group )
			{
				$subgroups = $owner->get_subgroups();
				$calendar->add_entry_recursive( $date, TRUE, $subgroups );
			}
			else
			{
				$calendar->add_entry( $date, TRUE );
			}
			$_SESSION[ "confirmation" ] = str_replace( "%TITLE", htmlentities($values[ "DATE_TITLE" ],ENT_QUOTES, "UTF-8"), gettext( "Event %TITLE added to calendar." ) );
		}

    if (isset($group) && is_object($group) && $group instanceof koala_group_default) // group calendar   test for $group instanceof koala_group_course for courses
		{
			header( "Location: " . $backlink[ "link" ] . "calendar/?date=" . str_to_timestamp( $bDate, $bTime ) );
		}
		else
		{
			header( "Location: " . $backlink[ "link" ] . "?date=" . str_to_timestamp( $bDate, $bTime ) );
		}
		exit;
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}
}

$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
$content->loadTemplateFile( "event_edit.template.html" );

$content->setVariable( "INFO_TEXT", "" );  //TODO: hier kommt spaeter eine Monatsuebersicht hin (?)

$content->setVariable( "LABEL_TITLE", gettext( "Title" ) );
$content->setVariable( "LABEL_LOCATION", gettext( "Location" ) );
$content->setVariable( "LABEL_BEGIN", gettext( "Begin* (day, month, year) (hour, minute)" ) );
$content->setVariable( "LABEL_END", gettext( "End* (day, month, year) (hour, minute)" ) );
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

if ( ! empty( $values ) )
{
	$content->setVariable( "VALUE_TITLE", h($values[ "DATE_TITLE" ]) );
	$content->setVariable( "VALUE_LOCATION", h($values[ "DATE_LOCATION" ]) );
	$content->setVariable( "VALUE_DESC", h($values[ "DATE_DESCRIPTION" ]) );
	$content->setVariable( "VALUE_URL", h($values[ "DATE_URL" ]) );
	
	if ( empty( $values[ "begin_date" ] ) && empty( $values[ "end_date" ] ) )
	{
		$content->setVariable( "BEGIN_DAY", strftime( "%d", $values[ "DATE_START_DATE" ] ) );
		$content->setVariable( "BEGIN_MONTH", strftime( "%m", $values[ "DATE_START_DATE" ] ) );
		$content->setVariable( "BEGIN_YEAR", strftime( "%Y", $values[ "DATE_START_DATE" ] ) );
		$content->setVariable( "BEGIN_HOUR", strftime( "%H", $values[ "DATE_START_DATE" ] ) );
		$content->setVariable( "BEGIN_MINUTE", strftime( "%M", $values[ "DATE_START_DATE" ] ) );

		$content->setVariable( "END_DAY", strftime( "%d", $values[ "DATE_END_DATE" ] ) );
		$content->setVariable( "END_MONTH", strftime( "%m", $values[ "DATE_END_DATE" ] ) );
		$content->setVariable( "END_YEAR", strftime( "%Y", $values[ "DATE_END_DATE" ] ) );
		$content->setVariable( "END_HOUR", strftime( "%H", $values[ "DATE_END_DATE" ] ) );
		$content->setVariable( "END_MINUTE", strftime( "%M", $values[ "DATE_END_DATE" ] ) );
		
		$values[ "begin_time" ] = strftime( "%H:%M", $values[ "DATE_START_DATE" ] );
		$values[ "end_time" ] = strftime( "%H:%M", $values[ "DATE_END_DATE" ] );
	}
	else
	{
		$content->setVariable( "BEGIN_DAY", h($values[ "BEGIN_DAY" ]) );
		$content->setVariable( "BEGIN_MONTH", h($values[ "BEGIN_MONTH" ]) );
		$content->setVariable( "BEGIN_YEAR", h($values[ "BEGIN_YEAR" ]) );
		$content->setVariable( "BEGIN_HOUR", h($values[ "BEGIN_HOUR" ]) );
		$content->setVariable( "BEGIN_MINUTE", h($values[ "BEGIN_MINUTE" ]) );
		
		$content->setVariable( "END_DAY", h($values[ "END_DAY" ]) );
		$content->setVariable( "END_MONTH", h($values[ "END_MONTH" ]) );
		$content->setVariable( "END_YEAR", h($values[ "END_YEAR" ]) );
		$content->setVariable( "END_HOUR", h($values[ "END_HOUR" ]) );
		$content->setVariable( "END_MINUTE", h($values[ "END_MINUTE" ]) );
	}
}
else
{
	$content->setVariable( "BEGIN_DAY", strftime( "%d", time() ) );
	$content->setVariable( "BEGIN_MONTH", strftime( "%m", time() ) );
	$content->setVariable( "BEGIN_YEAR", strftime( "%Y", time() ) );
	$content->setVariable( "BEGIN_HOUR", strftime( "%H", time() ) );
	$content->setVariable( "BEGIN_MINUTE", strftime( "%M", time() ) );
	
	$content->setVariable( "END_DAY", strftime( "%d", time() ) );
	$content->setVariable( "END_MONTH", strftime( "%m", time() ) );
	$content->setVariable( "END_YEAR", strftime( "%Y", time() ) );
	$content->setVariable( "END_HOUR", strftime( "%H", time() ) );
	$content->setVariable( "END_MINUTE", strftime( "%M", time() ) );
}

if ( isset($event) && $event )
{
	$content->setVariable( "LABEL_CREATE_ALTER", gettext( "Save changes" ) );
	/* not used here anymore
	$content->setCurrentBlock( "BLOCK_DELETE" );
	$content->setVariable( "EVENT_ID", $event->get_id() );
	$content->setVariable( "CONFIRMATION_MESSAGE", gettext( "Are you sure you want to delete this event?" ) );
	$content->setVariable( "LABEL_OR", gettext( "or") );
	$content->setVariable( "LABEL_DELETE", gettext( "Delete" ) );
	$content->parse( "BLOCK_DELETE" );
	*/
	$headline = array( "link" => "", "name" => gettext( "Alter event" ) );
}
else
{
	$content->setVariable( "LABEL_CREATE_ALTER", gettext( "Create event" ) );
	$headline = array( "link" => "", "name" => gettext( "Create event") );
}

$portal->set_page_main( array( $backlink, $headline ), $content->get() );
$portal->show_html();
?>
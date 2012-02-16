<?php
// no direct call
if (!defined('_VALID_KOALA')) {
	header("location:/");
	exit;
}
include_once( PATH_LIB . "format_handling.inc.php" );

$portal->set_page_title( gettext( "Calendar" ) );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "calendar.template.html" );

if ( isset( $_GET[ "date" ] ) )
{
	$date = $calendar->get_last_monday( $_GET[ "date" ] );
}
else
{
	$date = $calendar->get_last_monday();
}
$d    = $date;
$year = date( "Y", $date );
$cw   = strftime( "%V", $date )+1-1;
$events = $calendar->get_entries_calendar_week( $cw, $year );

$content->setVariable( "LABEL_TODAY", gettext( "Go to today" ) );
$content->setVariable( "LINK_TODAY", "?date=" . time() );
if(ADD_MEETINGS){
	$content->setCurrentBlock( "BLOCK_CREATE_NEW" );
	$content->setVariable( "LABEL_CREATE_NEW", gettext( "Create new event" ) );
	$content->setVariable( "LINK_CREATE_NEW", "new/" );
	$content->parse( "BLOCK_CREATE_NEW" );
}
$content->setVariable( "VALUE_WEEK", str_replace( "%CW", $cw, gettext( "Week %CW" ) ) );
$content->setVariable( "LINK_PREV_WEEK", "?date=" . strtotime( "-1 week", $date  ) );
$content->setVariable( "LINK_NEXT_WEEK", "?date=" . strtotime( "+1 week", $date  ) );
$content->setVariable( "LABEL_PREV", gettext( "Previous week" ));
$content->setVariable( "LABEL_NEXT", gettext( "Next week" ));

for( $weekday = 0; $weekday <= 6; $weekday++ )
{
  $content->setCurrentBlock( "BLOCK_WEEKDAY" );
  $content->setVariable( "LABEL_WEEKDAY", strftime( "%A, %d. %B", $d  ) );
  if ( count( $events[ $weekday ] ) > 0 )
  {
    $date_objects = $events[ $weekday ];
    while( list( $id, $date_obj ) = each( $date_objects ) )
    {
      $content->setCurrentBlock( "BLOCK_EVENT" );
      $content->setVariable( "VALUE_EVENT_TIME", strftime( "%H:%M", $date_obj->get_attribute( "DATE_START_DATE" ) ) . " - " . strftime( "%H:%M", $date_obj->get_attribute( "DATE_END_DATE" ) ) );
      $content->setVariable( "VALUE_EVENT_NAME", htmlentities($date_obj->get_attribute( "DATE_TITLE" ),ENT_QUOTES, "UTF-8") );
      $content->setVariable( "VALUE_EVENT_LOCATION", ($date_obj->get_attribute( "DATE_LOCATION" ))?'('.htmlentities($date_obj->get_attribute( "DATE_LOCATION" ),ENT_QUOTES, "UTF-8").')':'' );
      $content->setVariable( "VALUE_EVENT_DSC", get_formatted_output( $date_obj->get_attribute( "DATE_DESCRIPTION" ),ENT_QUOTES, "UTF-8") );
      $content->setVariable( "VALUE_EVENT_URL", ($date_obj->get_attribute( "DATE_URL" ))?'(<a href="'.$date_obj->get_attribute( "DATE_URL" ).'">'.htmlentities($date_obj->get_attribute( "DATE_URL" ),ENT_QUOTES, "UTF-8").'</a>)':'' );
      if ( $date_obj->check_access_write( $user ) )
      {
        $content->setVariable( "DETAILS_LINK", "<a href=\"" . $date_obj->get_id() . "/details/\">" . gettext( "Details" ) . "</a> |" );
        $content->setVariable( "EDIT_LINK", "<a href=\"" . $date_obj->get_id() . "/edit/\">" . gettext( "Edit" ) . "</a> |" );
        $content->setVariable( "DELETE_LINK", "<a onclick=\"return window.confirm('" . gettext( "Are you sure you want to delete this event?" ) . "');\" href=\"" . $date_obj->get_id() . "/delete/\">" . gettext( "Delete" ) . "</a>" );
      }
      $content->parse( "BLOCK_EVENT" );
    }
  }
  $content->parse( "BLOCK_WEEKDAY" );
  $d = strtotime( "+1 day", $d );
}

$portal->set_page_main(
		array( array( "link" => "", "name" => str_replace( "%YEAR", strftime( "%G", $date ), gettext( "Calendar %YEAR" ) ) . " / " . str_replace( "%CW", $cw, gettext( "Week %CW" ) ) ) ),
		$content->get(),
		""
		);
$portal->show_html();
?>

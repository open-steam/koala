<?php
/**
 * steam_calendar
 *
 * Class definition for the use of the access of the user and group_calendars
 * in sTeam
 *
 * PHP versions 5
 *
 * @package     PHPsTeam
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author      Alexander Roth <aroth@it-roth.de>
 */

/**
 * function timestamp2iso_time:
 * 
 * @param $pTimestamp
 * 
 * @return 
 */
function timestamp2iso_time( $pTimestamp )
{
	return strftime( "%R", $pTimestamp );
}

/**
 * function timestamp2iso_date:
 * 
 * @param $pTimestamp
 * 
 * @return 
 */
function timestamp2iso_date( $pTimestamp )
{
	return strftime( "%d.%m.%G", $pTimestamp );
}

/**
 * function sort_dates:
 * 
 * @param $pDateA
 * @param $pDateB
 * 
 * @return boolean
 */
function sort_dates( $pDateA, $pDateB )
{
	if ( $pDateA->get_attribute( "DATE_START_DATE" ) == 0 && $pDateB->get_attribute( "DATE_START_DATE" ) == 0 )
	{
		return TRUE;
	}
	if ( $pDateA->get_attribute( "DATE_START_DATE" ) == 0 )
	{
		return TRUE;
	}
	if ( $pDateA->get_attribute( "DATE_START_DATE" ) >= $pDateB->get_attribute( "DATE_START_DATE" ) )
	{
		return FALSE;
	}
	else
	{
		return TRUE;
	}
}

function sort_dates_ascending( $pDateA, $pDateB )
{
	if ( $pDateA->get_attribute( "DATE_START_DATE") == $pDateB->get_attribute( "DATE_START_DATE" ) )
		return 0;
	else
	return ( $pDateA->get_attribute( "DATE_START_DATE" ) > $pDateB->get_attribute( "DATE_START_DATE" ) );
}

/**
 * @package     PHPsTeam
 */
class steam_calendar extends steam_room
{
	
	public function get_type() {
		return CLASS_CALENDAR | CLASS_ROOM | CLASS_OBJECT;
	}
	
	/**
	 * function get_last_monday:
	 * 
	 * Returns the monday of the week determined by the date
	 * @param date $pDate 
	 */
	public function get_last_monday( $pDate = "" )
	{
		$pDate = ( empty( $pDate ) ) ? time() : $pDate ;
		if ( strftime( "%u", $pDate ) == 1 )
		{
			// $pDate is a monday
			return $pDate;
		}
		return strtotime( "Last Monday", $pDate );
	}

	/**
 	* function get_monday_first_calendar_week:
 	* 
 	* @param $pYear
 	* 
 	* @return
 	*/
	public function get_monday_first_calendar_week( $pYear )
	{
		$first_january = mktime( 0, 0, 0, 1, 1, $pYear );
		$weekday = date( "w", $first_january );
		if ( $weekday <= 4 ) // see ISO8601
		{
			return mktime( 0, 0, 0, 1, 1 - ( $weekday - 1 ), $pYear );
		}
		else
		{
			return mktime( 0, 0, 0, 1, 1 + ( 7 - $weekday + 1 ), $pYear );
		}
	}

	/**
	* function get_monday_calendar_week:
	* 
	* @param $pCalendarWeek
	* @param $pYear
	* 
	* @return 
	*/
	public function get_monday_calendar_week( $pCalendarWeek, $pYear )
	{
		$first_monday = $this->get_monday_first_calendar_week( $pYear );
		$mon_month = date( "m", $first_monday );
		$mon_year  = date( "Y", $first_monday );
		$mon_days  = date( "d", $first_monday );

		$days = ( $pCalendarWeek - 1 ) * 7;
		return mktime( 0, 0, 0, $mon_month, $mon_days + $days, $mon_year);
	}

	/**
	* function get_date_objects:
	* 
	* @param $pStart
	* @param $pEnd
	* @param $pType
	* 
	* @return 
 	*/
	public function get_date_objects( $pStart = 0, $pEnd = 0, $pType = 0 )
	{
		if ( $pStart == 0 && $pEnd == 0 )
		{
			$pEnd = mktime( 0, 0, 0, 1, 1, 2050 );
		}
		$date_objects = $this->steam_command(
				$this,
				"get_all_entries",
				array(
					(int) $pStart,
					(int) $pEnd,
					$pType
				     ),
          0
				);
        
    steam_factory::load_attributes( $this->steam_connectorID, $date_objects, array("DATE_START_DATE") );
		usort( $date_objects, "sort_dates" );
		return $date_objects;
	}

	/**
 	* function get_entries
 	* 
 	* @param $pStart
 	* @param $pEnd 
 	* @param pAttributes
 	* 
 	* @return
 	*/
	public function get_entries( $pStart = 0, $pEnd = 0, $pAttributes = array() )
	{
		$date_objects = $this->get_date_objects( $pStart, $pEnd );
		$attributes = array(
			"DATE_START_DATE" => "",
			"DATE_END_DATE" => "",
			"DATE_TITLE" => "",
			"DATE_DESCRIPTION" => "",
			"DATE_LOCATION" => ""
		);
		$attributes = array_merge( $attributes, $pAttributes );

		foreach( $date_objects as $date )
		{
			$date->get_attributes( $attributes, TRUE, TRUE );
		}
		$this->steam_buffer_flush();
		usort( $date_objects, "sort_dates_ascending" );
		$result = array();
		foreach( $date_objects as $date )
		{
			$date_start = $date->get_attribute( "DATE_START_DATE" );
			$date_end   = $date->get_attribute( "DATE_END_DATE" );

			$d = ( $date_start < $pStart ) ? strtotime( strftime( "%D", $pStart ) ) : strtotime( strftime( "%D", $date_start ) );
			$c = strtotime( strftime( "%D", $date_end ) );

			while( $d <= $c )
			{
				$result[ $d ][ $date->get_id() ] = $date;
				$d = strtotime( "+1 Day", $d );
			}
		}
		return $result;
	}
	
	/**
	 * function get_entries_calendar_week:
	 * 
	 * This function delivers all date objects for one calendar week
	 *
	 * <code>
	 * $user         = $steam->get_current_steam_user();
	 * $calendar     = $user->get_calendar();
	 * $appointments = $calendar->get_entries_calendar_week();
	 * for( $weekday = 0; $weekday <= 6; $weekday++ )
	 * {
	 *	$date_objects = $appointments[ $weekday ];
	 *	usort( $date_objects, "sort_dates" );
	 *	while( list( $id, $date ) = each( $dates )  )
	 *	{
	 *		print( $date->get_attriubte( "DATE_START_DATE" ) );
	 *		print( $date->get_attriubte( "DATE_END_DATE" ) );
	 *		print( $date->get_attriubte( "DATE_DESCRIPTION" ) );
	 *	}
	 * }
	 * </code>
	 * @param Integer $pCalendarWeek Number of Calendar week
	 * @param Date    $pYear	Timestamp of the year
	 * @param mixed   $pAttributes Array of attributes which should be preloaded for the steam_date objects
	 * @return mixed Array of steam_date objects, index is the weekday
	 */
	public function get_entries_calendar_week( $pCalendarWeek = "", $pYear = "", $pAttributes = array() )
	{
		$pCalendarWeek = ( empty( $pCalendarWeek ) ) ? date( "W" ) : $pCalendarWeek;
		$pYear = ( empty( $pYear ) ) ? date( "Y") : $pYear;
		$ts_monday = $this->get_monday_calendar_week( $pCalendarWeek, $pYear );
		$ts_sunday = strtotime( "+1 week", $ts_monday );
		$entries  = $this->get_entries( $ts_monday, $ts_sunday, $pAttributes );
		$result = array();
		for ( $weekday = 0; $weekday <= 6; $weekday++ )
		{
			$checkTime=strtotime( "+$weekday Days", $ts_monday );
			$result[ $weekday ] = ( isset($entries[$checkTime]) && count ( $entries[$checkTime] ) > 0 ) ? $entries[$checkTime] : array();
		}
		return $result;
	}

	/**
 	* function check_conflicts:
 	* 
 	* @param $pObject
 	* @param $pStartDate
 	* @param $pEndDate
 	* @param $pStartTime
 	* @param $EndTime
 	* @param $pBuffer
 	* 
 	* @return
 	*/
	public function check_conflicts( $pObject, $pStartDate, $pEndDate, $pStartTime, $pEndTime, $pBuffer = 0 )
	{
		return $this->steam_command(
				$pObject,
				"check_conflicts",
				array( $pStartDate, $pEndDate, $pStartTime, $pEndTime ),
				$pBuffer
				);
	}

	/**
 	* fuction get_calendar_data
 	* 
 	* @param $pBuffer
 	* 
 	* @return 
 	*/
	public function get_calendar_data( $pBuffer = 0 )
	{
		return $this->get_attributes(
				array(
					"CALENDAR_TIMETABLE_START",
					"CALENDAR_TIMETABLE_END",
					"CALENDAR_TIMETABLE_ROTATION",
					"CALENDAR_DATE_TYPE",
					"CALENDAR_TRASH",
					"CALENDAR_OWNER",
					"OBJ_NAME",
					"OBJ_LAST_CHANGED",
					"OBJ_CREATION_TIME"
				     ), $pBuffer 
				);
	}

	/**
 	* function add_entry:
 	* 
 	* @param $pData
 	* @param $pSetLinkForGroupmembers
 	* @param $pBuffer
 	* 
 	* @return 
 	*/
	private function add_entry_impl( $pData, $pSetLinkForGroupmembers = FALSE, $pGroups = array(), $recursive = FALSE,  $pBuffer = 0 )
	{
		if(!isset($pData["DATE_ACCEPTED"]     )) $pData["DATE_ACCEPTED"]     = array();
		if(!isset($pData["DATE_ATTACHMENT"]   )) $pData["DATE_ATTACHMENT"]   = "";
		if(!isset($pData["DATE_CANCELLED"]    )) $pData["DATE_CANCELLED"]    = array();
		if(!isset($pData["DATE_DESCRIPTION"]  )) $pData["DATE_DESCRIPTION"]  = "";
		if(!isset($pData["DATE_END_DATE"]     )) $pData["DATE_END_DATE"]     = "0";
		if(!isset($pData["DATE_END_TIME"]     )) $pData["DATE_END_TIME"]     = "0";
		if(!isset($pData["DATE_INTERVAL"]     )) $pData["DATE_INTERVAL"]     = "";
		if(!isset($pData["DATE_IS_SERIAL"]    )) $pData["DATE_IS_SERIAL"]    = "0";
		if(!isset($pData["DATE_KIND_OF_ENTRY"])) $pData["DATE_KIND_OF_ENTRY"]= "0";
		if(!isset($pData["DATE_LOCATION"]     )) $pData["DATE_LOCATION"]     = "";
		if(!isset($pData["DATE_NOTICE"]       )) $pData["DATE_NOTICE"]       = "";
		if(!isset($pData["DATE_ORGANIZERS"]   )) $pData["DATE_ORGANIZERS"]   = array();
		if(!isset($pData["DATE_PARTICIPANTS"] )) $pData["DATE_PARTICIPANTS"] = array();
		if(!isset($pData["DATE_PRIORITY"]     )) $pData["DATE_PRIORITY"]     = "0";
		if(!isset($pData["DATE_START_DATE"]   )) $pData["DATE_START_DATE"]   = "0";
		if(!isset($pData["DATE_START_TIME"]   )) $pData["DATE_START_TIME"]   = "0";
		if(!isset($pData["DATE_TITLE"]        )) $pData["DATE_TITLE"]        = "";
		if(!isset($pData["DATE_TYPE"]         )) $pData["DATE_TYPE"]         = 0;
		if(!isset($pData["DATE_WEBSITE"]      )) $pData["DATE_WEBSITE"]      = "";
    if ($recursive) {
      return $this->steam_command(
        $this,
        "add_entry_recursive",
        array( 
          array( "name" => "date" . time(), "attributes" => $pData ),
          $pSetLinkForGroupmembers,
          $pGroups
        ),
        $pBuffer
      );
    } else {
      return $this->steam_command(
        $this,
        "add_entry",
        array( 
          array( "name" => "date" . time(), "attributes" => $pData ),
          $pSetLinkForGroupmembers,
          $pGroups
        ),
        $pBuffer
      );
    }
	}

	/**
 	* function add_entry:
 	* 
 	* @param $pData
 	* @param $pSetLinkForGroupmembers
 	* @param $pBuffer
 	* 
 	* @return 
 	*/
	public function add_entry_recursive( $pData, $pSetLinkForGroupmembers = FALSE, $pGroups = array(), $pBuffer = 0 )
	{
		return $this->add_entry_impl( $pData, $pSetLinkForGroupmembers, $pGroups, TRUE, $pBuffer);
	}
  
	/**
 	* function add_entry:
 	* 
 	* @param $pData
 	* @param $pSetLinkForGroupmembers
 	* @param $pBuffer
 	* 
 	* @return 
 	*/
	public function add_entry( $pData, $pSetLinkForGroupmembers = FALSE, $pGroups = array(), $pBuffer = 0 )
	{
		return $this->add_entry_impl( $pData, $pSetLinkForGroupmembers, $pGroups, FALSE, $pBuffer);
	}
}
?>
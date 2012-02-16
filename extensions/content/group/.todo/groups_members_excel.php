<?php

if ( !$group->is_staff( lms_steam::get_current_user()) && !lms_steam::is_koala_admin( lms_steam::get_current_user()) ) {
  throw new Exception( "No rights to view this.", E_USER_RIGHTS  );
}

require_once( "Spreadsheet/Excel/Writer.php" );
$cache = get_cache_function( $group->get_id(), CACHE_LIFETIME_STATIC );

switch( get_class( $group ) )
{
	case( "koala_group_course" ):
		$group_name = $group->get_course_id() . " - " . $group->get_name();
		if(isset($list_staff) && $list_staff) {
			$members = $cache->call( "lms_steam::group_get_members", $group->steam_group_staff->get_id(), TRUE );
		} else {
			$members = $cache->call( "lms_steam::group_get_members", $group->steam_group_learners->get_id(), TRUE );
		}
	break;

	default:
		$members = $cache->call( "lms_steam::group_get_members", $group->get_id() );
	break;
}

// INITIALIZATION

$course_id    = $group->get_course_id();
$course_name  = $group->get_course_name();
$semester     = $group->get_semester();

$excel        = new Spreadsheet_Excel_Writer();
$excel->setTempDir(PATH_TEMP);

$format_table_header =& $excel->addFormat(array(  'Size' => 12,
								      'Align' => 'left',
								      'Bold'  => 1 ));

$format_table_header_faded =& $excel->addFormat(array(  'Size' => 12,
									  'Align' => 'left',
									  'Color' => 'grey' ));

$format_cell =& $excel->addFormat(array(  'Size' => 9,
									  'Align' => 'left',
									  'Bold'  => 1 ));
$format_cell->setAlign( 'vcenter' );
$format_cell->setTextWrap( 1 );


switch ( $path[ 3 ] )
{
	case "attendance":
		$excel->send( $course_id . "_" . $semester->get_name() );
		$sheet        =& $excel->addWorksheet( gettext( "participants" ) );

		// WRITE EXCEL SHEET
		$sheet->setLandscape();
		$sheet->fitToPages( 1, 0 );
		$sheet->setHeader( "&C &16 Anwesenheitsliste " . $course_name . " im " . $semester->get_name() );
		$sheet->setFooter( "&C &16 Seite &P von &N");
		$sheet->setColumn( 0, 1, 12);
		$sheet->setColumn( 2, 6, 18);
		$sheet->repeatRows( 0, 1 );

		// WRITE EXCEL SHEET
		$sheet->writeString( 0, 0, gettext( "surname" ), $format_table_header );
		$sheet->writeString( 0, 1, gettext( "forename" ), $format_table_header );
		$sheet->writeString( 0, 2, "Termin", $format_table_header_faded );
		$sheet->writeString( 0, 3, "Termin", $format_table_header_faded );
		$sheet->writeString( 0, 4, "Termin", $format_table_header_faded );
		$sheet->writeString( 0, 5, "Termin", $format_table_header_faded );
		$sheet->writeString( 0, 6, "Termin", $format_table_header_faded );

		$sheet->freezePanes( array( 2, 0 ) );

		$no_members = count( $members );
		if ( $no_members > 0 )
		{
			$row = 2;
			foreach( $members as $member )
			{
				$sheet->setRow( $row, 25 );
				$sheet->writeString( $row, 0, $member[ "USER_FULLNAME" ], $format_cell );
				$sheet->writeString( $row, 1, $member[ "USER_FIRSTNAME" ], $format_cell );
				$row++;
			}
		}

	break;

	default:
		$excel->send( $course_id . "_" . $semester->get_name() );
		if(isset($list_staff) && $list_staff) {
			$sheet        =& $excel->addWorksheet( gettext( "staff" ) );
		}
		else {
			$sheet        =& $excel->addWorksheet( gettext( "participants" ) );
		}

		// WRITE EXCEL SHEET
		$sheet->writeString( 0, 0, $course_id . " - " . $course_name );
		$sheet->writeString(  1, 0, $semester->get_name() );
		if(isset($list_staff) && $list_staff)
		{
			$sheet->writeString( 3, 0, gettext( "forename" ) );
			$sheet->writeString( 3, 1, gettext( "surname" ) );
			$sheet->writeString( 3, 2, gettext( "e-mail" ) );
		}
		else
		{
			$sheet->writeString( 3, 0, gettext( "student id" ) );
			$sheet->writeString( 3, 1, gettext( "forename" ) );
			$sheet->writeString( 3, 2, gettext( "surname" ) );
			$sheet->writeString( 3, 3, gettext( "e-mail" ) );
		}

		$no_members = count( $members );
		if ( $no_members > 0 )
		{
			$row = 5;
			foreach( $members as $member )
			{
				if(isset($list_staff) && $list_staff)
				{
					$sheet->writeString( $row, 0, $member[ "USER_FIRSTNAME" ] );
					$sheet->writeString( $row, 1, $member[ "USER_FULLNAME" ] );
					$sheet->writeString( $row, 2, $member[ "USER_EMAIL" ] );
					$sheet->writeString( $row, 3, $member[ "USER_PROFILE_FACULTY" ] );
				}
				else
				{
					$sheet->writeString( $row, 0, ($member[ "ldap:USER_MATRICULATION_NUMBER" ] != 0) ? $member[ "ldap:USER_MATRICULATION_NUMBER" ] : $member[ "OBJ_NAME" ]);
					$sheet->writeString( $row, 1, $member[ "USER_FIRSTNAME" ] );
					$sheet->writeString( $row, 2, $member[ "USER_FULLNAME" ] );
					$sheet->writeString( $row, 3, $member[ "USER_EMAIL" ] );
					$sheet->writeString( $row, 4, $member[ "USER_PROFILE_FACULTY" ] );
				}
				$row++;
			}
		}

		break;
}

$excel->close();

?>

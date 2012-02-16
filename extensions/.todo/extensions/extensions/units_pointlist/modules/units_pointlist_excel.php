<?php
if ( !$course->is_staff( lms_steam::get_current_user()) && !lms_steam::is_koala_admin( lms_steam::get_current_user()) ) {
  throw new Exception( "No rights to view this.", E_USER_RIGHTS  );
}

require_once( "Spreadsheet/Excel/Writer.php" );
$cache = get_cache_function( $unit->get_id(), CACHE_LIFETIME_STATIC );

$participant_group = $unit->get_attribute("UNIT_POINTLIST_PARTICIPANTS");
$proxy = $unit->get_attribute("UNIT_POINTLIST_PROXY");

if (defined("LOG_DEBUGLOG")) {
  $time1 = microtime(TRUE);
  $login = lms_steam::get_current_user()->get_name();
  logging::write_log( LOG_DEBUGLOG, "units_pointlist_excel:\t " . $login . "\t" . $unit->get_display_name() . "\t" .  $participant_group->get_identifier() . "\t" . $participant_group->count_members() . "\t" . date( "d.m.y G:i:s", time() ) . "... " );
}

$members = $cache->call( "lms_steam::group_get_members", $participant_group->get_id(), TRUE );

// INITIALIZATION
$course_id    = $course->get_course_id();
$course_name  = $course->get_course_name();
$semester     = $course->get_semester();

$proxy_data = $proxy->get_all_attributes();

$points = units_pointlist::extract_pointlist( $proxy_data );
$maxpoints = $proxy_data["UNIT_POINTLIST_MAXPOINTS"];
$count = $unit->get_attribute("UNIT_POINTLIST_COUNT");
$bonus_1 = $unit->get_attribute("UNIT_POINTLIST_BONUS_1");
$bonus_2 = $unit->get_attribute("UNIT_POINTLIST_BONUS_2");

$unit_name = $unit->get_display_name();

$excel        = new Spreadsheet_Excel_Writer();
$excel->setTempDir(PATH_TEMP);

$format_table_header =& $excel->addFormat(array(  'Size' => 12,
								      'Align' => 'left',
								      'Bold'  => 1 ));
$format_table_header_number =& $excel->addFormat(array(  'Size' => 12,
								      'Align' => 'right',
								      'Bold'  => 1 ));
$sheet_table_header =& $excel->addFormat(array(  'Size' => 14,
								      'Align' => 'left'));
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

$excel->send( $unit_name . "_" . str_replace("." , "-", $course_id) . "_" . $semester->get_name() . ".xls" );
$sheet =& $excel->addWorksheet( gettext( "Pointlist" ) );

// WRITE EXCEL SHEET
$sheet->writeString( 0, 0, gettext("Course") . ":" , $sheet_table_header );
$sheet->writeString( 0, 1, $course_id . " - " . $course_name, $sheet_table_header );
$sheet->writeString( 1, 0, gettext("Semester") . ":", $sheet_table_header  );
$sheet->writeString(  1, 1, $semester->get_name(), $sheet_table_header );
$sheet->writeString( 2, 0, gettext("Pointlist") . ":", $sheet_table_header  );
$sheet->writeString(  2, 1, $unit_name, $sheet_table_header );
// BONUS
$bonusoffset = 0;
if ($bonus_1 != 0 && $bonus_2 != 0) $bonusoffset = 2;
else if ($bonus_1 == 0 && $bonus_2 != 0) {
  $bonusoffset = 1;
  $bonus_1 = $bonus_2;
  $bonus_2 = 0;
}
else if ($bonus_2 == 0 && $bonus_1 != 0) $bonusoffset = 1;


$sheet->writeString( 3, 0, gettext("Bonus") . ":", $sheet_table_header  );
$sheet->write(  3, 1, 0, $sheet_table_header );
if ($bonusoffset>0)  $sheet->write(  4, 1, $bonus_1, $sheet_table_header );
if ($bonusoffset>1)  $sheet->write(  5, 1, $bonus_2, $sheet_table_header );
$sheet->write(  3, 2, gettext("None"), $sheet_table_header );
$sheet->write(  4, 2, gettext("One step"), $sheet_table_header );
$sheet->write(  5, 2, gettext("Two steps"), $sheet_table_header );


$headeroff = 7; // Row Offset
$sheet->writeString( $headeroff, 0, gettext( "student id" ), $format_table_header );
$sheet->writeString( $headeroff, 1, gettext( "login" ), $format_table_header );
$sheet->writeString( $headeroff, 2, gettext( "forename" ), $format_table_header );
$sheet->writeString( $headeroff, 3, gettext( "surname" ), $format_table_header );
$sheet->writeString( $headeroff, 4, gettext( "e-mail" ), $format_table_header );
$sheet->writeString( $headeroff+1, 0, gettext( "maximum points" ) );
$maxsum = 0;
for ($i = 1; $i <= $count; $i++) {
  $sheet->write( $headeroff, 4+$i, $i, $format_table_header_number);
  $sheet->write( $headeroff+1, 4+$i, $maxpoints[$i]);
  $maxsum += $maxpoints[$i];
}
$theA = 65; // AscII Code for "A"
$offset = 4; // Column Offset
$sheet->writeString( $headeroff, $offset+$count+1, gettext( "Sum" ), $format_table_header );
$sheet->writeFormula( $headeroff+1, $offset+$count+1, "=SUM(" . chr($theA+$offset+1) . ($headeroff+2) . ":" . chr($theA+$offset+$count) . ($headeroff+2) .")", $format_table_header );
$sheet->writeString( $headeroff, $offset+$count+2, gettext( "Bonus" ), $format_table_header );
$bonus_head = ($bonus_1?$bonus_1:"") . (($bonus_1 && $bonus_2)?"/":"") . ($bonus_2?$bonus_2:"");
$sheet->writeString( $headeroff+1, $offset+$count+2, $bonus_head );

$no_members = count( $members );
if ( $no_members > 0 ) {
  $row = $headeroff +2;
  foreach( $members as $member )
  {
    $sheet->write( $row, 0, $member[ "ldap:USER_MATRICULATION_NUMBER" ] );
    $sheet->writeString( $row, 1, $member[ "OBJ_NAME" ] );
    $sheet->writeString( $row, 2, $member[ "USER_FIRSTNAME" ] );
    $sheet->writeString( $row, 3, $member[ "USER_FULLNAME" ] );
    $sheet->writeString( $row, 4, $member[ "USER_EMAIL" ] );

    $mp = $points[$member["OBJ_ID"]];
    if (!is_array($mp)) $mp = array();
    $sum = 0;
    $keys = array_keys($mp);
    foreach($keys as $key) {
      $sheet->write( $row, $offset+$key, str_replace(",", ".", $mp[$key]) );
      $sum += $mp[$key];
    }
    // SUM
    $sheet->writeFormula( $row, $offset+$count+1, "=SUM(" . chr($theA+$offset+1) . ($row+1) . ":". chr($theA+$offset+$count) . ($row+1) .")");
    // BONUS
    // Note: I didnt append "$" chars to the matrix definition here because of a bug in the Spreadsheet Writer Module of PHP PEAR (http://pear.php.net/bugs/bug.php?id=6318). Not to provide the "$" was a workaround for this issue
    $bonusformula = "=VLOOKUP(" . chr($theA+$offset+$count+1) . ($row+1) . ";" . chr($theA+1) . "" . 4 . ":" . chr($theA+2) . "" . (4+$bonusoffset) . ";2)";
    $sheet->writeFormula( $row, $offset+$count+2, $bonusformula);
    $row++;
  }
}

if (defined("LOG_DEBUGLOG")) {
  logging::append_log( LOG_DEBUGLOG, " \t" . round((microtime(TRUE) - $time1) * 1000 ) . " ms");
}

$excel->close();

?>

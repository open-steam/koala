<?php
/** Fuktion zur Berechnung der zugeh�rigen L�sungen f�r eine 
 * �bergebene Aufgabenstellung und f�r den �bergebenen Benutzer
 * 
 * @param $item_task Aufgabenstellung f�r die die L�sungen zur�ckgegeben werden
 * @param $uuser Benutzer dessen L�sungen zur�ckgegeben werden
 * @param $iinventory Der Container, der durchsucht werden soll
 * @return gibt ein Array mit den zur Aufgabenstellung und Benutzer geh�rigen L�sungen
 */
function get_subitem($iinventory,$item_task,$uuser){
	$inven = $iinventory;
	foreach($inven as $sub_item)
		if($sub_item->get_attribute(HOMEWORK_TYPE)=="solution" && $sub_item->check_access_read($uuser)){
			if($sub_item->get_attribute(HOMEWORK_TASK)==$item_task){
				$sub_inv[]=$sub_item;
			}
		}
	return $sub_inv;	
}

require_once( PATH_LIB . "url_handling.inc.php");
require_once( PATH_CLASSES."/PEAR/Spreadsheet/Excel/Writer.php" );

$group_staff=$owner->get_group_staff();
//Testen ob User die Berechtigung zum "Liste erstellen" hat
if ( !$group_staff->is_member( lms_steam::get_current_user() ) ) {
	$portal->set_problem_description( gettext( "You are not permitted to view this folder.'" ) );
	$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html(), "");
	$portal->show_html();
	exit;
}

$group=$owner->get_group_learners();
$cache = get_cache_function( $group->get_id(), CACHE_LIFETIME_STATIC );
$members=lms_steam::group_get_members($group->get_id());

$container = $koala_container->get_steam_object();
$path = url_parse_rewrite_path( $_GET[ "path" ] );
$id = $path[2];
$env = steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $id);
$invent=$container->get_inventory();

$course_id    = $owner->get_course_id();
$course_name  = $owner->get_course_name();
$semester     = $owner->get_semester();
// INITIALIZATION
$excel        = new Spreadsheet_Excel_Writer();
$excel->send( $course_id . "_" . $semester->get_name() );
$sheet        =& $excel->addWorksheet( gettext( "participants" ) );

//Spaltenbreite setzen
$sheet->setColumn(0,0,30);// erste Spalte f�r den Namen breiter wie der Rest
$sheet->setColumn(1,30,14);

$format_table_header =& $excel->addFormat(array(  'Size' => 12,
								      'Align' => 'left',
								      'Bold'  => 1));
								       
$format_names =& $excel->addFormat(array(  'Size' => 12,
								      'Align' => 'left',
								      'Bold'  => 1));

$format_table_head=& $excel->addFormat(array(  'Size' => 14,
									  'Align' => 'left',
									  'Color' => 'grey' ));
								
$format_cell =& $excel->addFormat(array(  'Size' => 10,
									  'Align' => 'left'));
									  
$format_cell->setAlign( 'vcenter' );
$format_cell->setTextWrap( 1 );
$sheet->setColumn(0,20,$longest_string+6);//Zeilen Breite einstellen
// WRITE EXCEL SHEET
$sheet->writeString(  0, 0, $course_name , $format_table_head );
$sheet->writeString( 1, 3, gettext("Table of Points"), $format_table_head );
$sheet->writeString(  1, 0, $semester->get_name(), $format_table_head);

$col=1;
foreach($invent as $inven){//Kopfzeie setzen
	if($inven->get_attribute(HOMEWORK_TYPE)=="task"){
		$sheet->writeString( 3, $col, gettext("Task").": ".$col, $format_table_header);
		$col++;
	}	
}
$col=1;
$row = 5;
$max_count=0;
$punkt_sum=0;
foreach( $members as $member ){	
	$sheet->writeString( $row, 0, $member[ "USER_FIRSTNAME" ]." ".$member[ "USER_FULLNAME" ], $format_names);
	foreach($invent as $inven){
		if($inven->get_attribute(HOMEWORK_TYPE)=="task"){
			$u=steam_factory::get_user($GLOBALS["STEAM"]->get_id(), $member[ "OBJ_NAME" ]);
			$solution=get_subitem($invent,$inven->get_id(),$u);
			$counter=0;
			if (count($solution)>0){
				foreach($solution as $solu){
					$sol_point=$solu->get_attribute(HOMEWORK_POINTS);
					$sheet->writeString($row, $col, /*$solu->get_name()." ".*/$sol_point,$format_cell);
					if (count($solution)>1){
						$row++;
						$counter++;
					}
					$punkt_sum+=$sol_point;
				}
				if($counter>=$max_count){//max_count setzen f�r den Sprung zum n�chsten User mit richigem Abstand
					$max_count=$counter;
				}
				$row-=$counter;
			}
			else{
				$sheet->writeString($row, $col, "--------",$format_cell);
			}
		$col++;
		}
	}
	$sheet->writeString($row, $col, gettext("Sum").": ".$punkt_sum);
	$punkt_sum=0;
	$row=$row+$max_count;
	$col=1;
	$max_count=0;
}



/*
$no_members = count( $members );
if ( $no_members > 0 )
{
	$row = 5;
	foreach( $members as $member )
	{	
		//$sheet->writeString( $row, 0, $member[ "ldap:USER_MATRICULATION_NUMBER" ] );
		$sheet->writeString( $row, 1, $member[ "USER_FIRSTNAME" ] );
		$sheet->writeString( $row, 2, $member[ "USER_FULLNAME" ] );	
		$sheet->writeString( $row, 3, $member[ "OBJ_NAME" ] );		
		$sheet->writeString( $row, 4, $member[ "USER_EMAIL" ] );			
		$sheet->writeString( $row, 5, $member[ "USER_PROFILE_FACULTY" ] );	
		$row++;
	}
}*/

$excel->close();

?>

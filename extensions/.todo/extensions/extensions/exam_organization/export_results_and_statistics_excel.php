<?php
/*
 * generate excel file with exam results
 * 
 * @author Dominik LÃ¼ke
 */

error_reporting(E_ALL);

$eoDatabase = exam_organization_database::getInstance();
$eoDatabase->calculateExamResults($course);
$participants = $eoDatabase->getParticipantsForTerm($examTerm);
$notPassed = 0;

$examObject = exam_organization_exam_object_data::getInstance($course);
$examObject->markForDataDeletion($examTerm); //mark exam data for deletion

require_once PATH_CLASSES . 'phpexcel/Classes/PHPExcel.php';
require_once PATH_CLASSES . 'phpexcel/Classes/PHPExcel/IOFactory.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator(gettext("koaLA exam organization"))
							 ->setLastModifiedBy(gettext("koaLA exam organization"))
							 ->setTitle(gettext("exam results"))
							 ->setSubject(gettext("exam results"))
							 ->setDescription(gettext("exam results"))
							 ->setKeywords(gettext("exam result"))
							 ->setCategory(gettext("exam results"));

// fetch information from DB
$exam_name = $course->get_attribute("OBJ_DESC");

$semester = $course->get_semester()->get_name(); // returns e.g. WS0910
$semester = substr($semester, 0, -2) . "/" . substr($semester, -2);
$semester = str_replace("WS", gettext("winter") . " ", $semester);
$semester = str_replace("SS", gettext("summer") . " ", $semester);

$day = $course->get_attribute("EXAM" . $examTerm . "_exam_date_day");
$month = $course->get_attribute("EXAM" . $examTerm . "_exam_date_month");
$year = $course->get_attribute("EXAM" . $examTerm . "_exam_date_year");
$date = sprintf("%02d.%02d.%04d", $day, $month, $year);

$start_hour = $course->get_attribute("EXAM" . $examTerm . "_exam_time_start_hour");
$start_minute = $course->get_attribute("EXAM" . $examTerm . "_exam_time_start_minute");
$end_hour = $course->get_attribute("EXAM" . $examTerm . "_exam_time_end_hour");
$end_minute = $course->get_attribute("EXAM" . $examTerm . "_exam_time_end_minute");
$start_time = $start_hour . ":" . $start_minute;
$end_time = $end_hour . ":" . $end_minute;

//get exam key
$examObject = exam_organization_exam_object_data::getInstance($course);
$examKey = $examObject->getExamKey($examTerm);
$maxPoints = $examObject->getExamKeyMaxPoints($examTerm);

$summary = array(
					"10" => array("mark" => "1,0", "countBonus" => 0, "countNoBonus" => 0, "from" => $examKey["10"], "to" => $maxPoints),
					"13" => array("mark" => "1,3", "countBonus" => 0, "countNoBonus" => 0, "from" => $examKey["13"], "to" => $examKey["10"]-1),
					"17" => array("mark" => "1,7", "countBonus" => 0, "countNoBonus" => 0, "from" => $examKey["17"], "to" => $examKey["13"]-1),
					"20" => array("mark" => "2,0", "countBonus" => 0, "countNoBonus" => 0, "from" => $examKey["20"], "to" => $examKey["17"]-1),
					"23" => array("mark" => "2,3", "countBonus" => 0, "countNoBonus" => 0, "from" => $examKey["23"], "to" => $examKey["20"]-1),
					"27" => array("mark" => "2,7", "countBonus" => 0, "countNoBonus" => 0, "from" => $examKey["27"], "to" => $examKey["23"]-1),
					"30" => array("mark" => "3,0", "countBonus" => 0, "countNoBonus" => 0, "from" => $examKey["30"], "to" => $examKey["27"]-1),
					"33" => array("mark" => "3,3", "countBonus" => 0, "countNoBonus" => 0, "from" => $examKey["33"], "to" => $examKey["30"]-1),
					"37" => array("mark" => "3,7", "countBonus" => 0, "countNoBonus" => 0, "from" => $examKey["37"], "to" => $examKey["33"]-1),
					"40" => array("mark" => "4,0", "countBonus" => 0, "countNoBonus" => 0, "from" => $examKey["40"], "to" => $examKey["37"]-1),
					"50" => array("mark" => "5,0", "countBonus" => 0, "countNoBonus" => 0, "from" => 0, "to" => $examKey["40"]-1),
					"NT" => array("mark" => "NT", "countBonus" => 0, "countNoBonus" => 0, "from" => 0, "to" => 0),
					"BV" => array("mark" => "BV", "countBonus" => 0, "countNoBonus" => 0, "from" => 0, "to" => 0),
					"SICK" => array("mark" => "SICK", "countBonus" => 0, "countNoBonus" => 0, "from" => 0, "to" => 0));

foreach($participants as $participant)
{
	if ($participant["isNT"] == "NT")
	{
		$summary["NT"]["countBonus"]++;
		$summary["NT"]["countNoBonus"]++;
	}
	else if ($participant["isNT"] == "BV")
	{
		$summary["BV"]["countBonus"]++;
		$summary["BV"]["countNoBonus"]++;
	}
	else if ($participant["isNT"] == "SICK")
	{
		$summary["SICK"]["countBonus"]++;
		$summary["SICK"]["countNoBonus"]++;
	}
	else
	{
		$resultWithoutBonus = $eoDatabase->getExamResultWithoutBonus($examTerm,$participant["imtLogin"]);
		
		switch ($resultWithoutBonus)
		{
			case "1": $summary["10"]["countNoBonus"]++; break;
			case "1.3": $summary["13"]["countNoBonus"]++; break;
			case "1.7": $summary["17"]["countNoBonus"]++; break;
			case "2": $summary["20"]["countNoBonus"]++; break;
			case "2.3": $summary["23"]["countNoBonus"]++; break;
			case "2.7": $summary["27"]["countNoBonus"]++; break;
			case "3": $summary["30"]["countNoBonus"]++; break;
			case "3.3": $summary["33"]["countNoBonus"]++; break;
			case "3.7": $summary["37"]["countNoBonus"]++; break;
			case "4": $summary["40"]["countNoBonus"]++; break;
			case "5": $summary["50"]["countNoBonus"]++; $notPassed++; break;
		}
		
		$resultWithBonus = $eoDatabase->getExamResultWithBonus($examTerm,$participant["imtLogin"]);
		
		switch ($resultWithBonus)
		{
			case "1": $summary["10"]["countBonus"]++; break;
			case "1.3": $summary["13"]["countBonus"]++; break;
			case "1.7": $summary["17"]["countBonus"]++; break;
			case "2": $summary["20"]["countBonus"]++; break;
			case "2.3": $summary["23"]["countBonus"]++; break;
			case "2.7": $summary["27"]["countBonus"]++; break;
			case "3": $summary["30"]["countBonus"]++; break;
			case "3.3": $summary["33"]["countBonus"]++; break;
			case "3.7": $summary["37"]["countBonus"]++; break;
			case "4": $summary["40"]["countBonus"]++; break;
			case "5": $summary["50"]["countBonus"]++; break;
		}
	}
}

$usedRooms = $eoDatabase->getUsedRooms($course->get_name(), $examTerm);
$rooms = "";

foreach ($usedRooms as $id => $subarray)
{
	$rooms .= $subarray["room"] . ", ";
}

$rooms = substr($rooms, 0, -2);

////////////////////////////////////////
////////// SHEET 1 - Overview //////////
////////////////////////////////////////

// formatting

$boldStyle = array(
	'font' => array(
		'bold' => true,
	),
	'alignment' => array(
		'center' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	),
);

$headerStyle = array(
	'font' => array(
		'bold' => true,
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	),
	'borders' => array(
		'bottom' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('argb' => '00000000'),
		),
	),
);

$objPHPExcel->setActiveSheetIndex(0)->getStyle('B27:C32')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A9:D22')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:A5')->applyFromArray($boldStyle);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A8:D8')->applyFromArray($headerStyle);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A26:C26')->applyFromArray($headerStyle);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A26:A32')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A27:A32')->getFont()->setBold(true);

$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(11);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(11);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(11);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(11);


// adding data
$objPHPExcel->setActiveSheetIndex(0)->setTitle(gettext('overview'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', gettext('exam'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', gettext('date'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A3', gettext('start time'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A4', gettext('end time'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A5', gettext('rooms'));

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', $exam_name);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', $date);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3', $start_time);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B4', $end_time);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B5', $rooms);

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A8', gettext('mark'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B8', gettext('points'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C8', gettext('# no bonus'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D8', gettext('# bonus'));

$rowCounter = 9;

foreach($summary as $s)
{
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $rowCounter, $s["mark"]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B' . $rowCounter, $s["from"] . " - " . $s["to"]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C' . $rowCounter, $s["countNoBonus"]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D' . $rowCounter, $s["countBonus"]);
	$rowCounter++;
}

$passed = count($participants) - $notPassed - $summary["NT"]["countBonus"] - $summary["BV"]["countBonus"] - $summary["SICK"]["countBonus"];
$passedPercent = number_format($passed / count($participants) * 100,2);
$notPassedPercent = number_format($notPassed / count($participants) * 100 ,2);
$NTpercent = number_format($summary["NT"]["countBonus"] / count($participants) * 100 ,2);
$BVpercent = number_format($summary["BV"]["countBonus"] / count($participants) * 100 ,2);
$SICKpercent = number_format($summary["SICK"]["countBonus"] / count($participants) * 100 ,2);

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B26', gettext('number'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C26', gettext('in %'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A27', gettext('participants'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B27', count($participants));

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A28', gettext('passed'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B28', $passed);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C28', $passedPercent);

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A29', gettext('not passed'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B29', $notPassed);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C29', $notPassedPercent);

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A30', gettext('NT'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B30', $summary["NT"]["countBonus"]);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C30', $NTpercent);

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A31', gettext('BV'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B31', $summary["BV"]["countBonus"]);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C31', $BVpercent);

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A32', gettext('SICK'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B32', $summary["SICK"]["countBonus"]);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C32', $SICKpercent);

///////////////////////////////////////////
////////// SHEET 2 - assignments //////////
///////////////////////////////////////////

$objPHPExcel->createSheet();
$objPHPExcel->setActiveSheetIndex(1)->setTitle(gettext('assignments'));
$assignments = $examObject->getAssignmentMaxPoints($examTerm);
$numberOfAssignments = $course->get_attribute("EXAM" . $examTerm . "_number_of_assignments");

// fortmatting
$objPHPExcel->setActiveSheetIndex(1)->getStyle('A1:B1')->applyFromArray($headerStyle);
$range = "A2:B" . ($numberOfAssignments + 1);
$objPHPExcel->setActiveSheetIndex(1)->getStyle($range)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('A')->setWidth(13);
$objPHPExcel->setActiveSheetIndex(1)->getColumnDimension('B')->setWidth(10);

$objPHPExcel->setActiveSheetIndex(1)->getStyle("A1:A" . ($numberOfAssignments + 1))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);


// data
$objPHPExcel->setActiveSheetIndex(1)->setCellValue('A1', gettext('assignment'));
$objPHPExcel->setActiveSheetIndex(1)->setCellValue('B1', gettext('max points'));
$n = 1;

foreach ($assignments as $assignment)
{
	if ($n > $numberOfAssignments) break;
	
	$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(0 , $n + 1, $n);
	$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1 , $n + 1, $assignment);
	$n++;
}

////////////////////////////////////////
////////// SHEET 3 - details ///////////
////////////////////////////////////////

$objPHPExcel->createSheet();
$objPHPExcel->setActiveSheetIndex(2)->setTitle(gettext('details'));

// formatting

// cell width
$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension('A')->setWidth(10);
$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension('B')->setWidth(20);
$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension('C')->setWidth(16);
$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension('D')->setWidth(9);
$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension('E')->setWidth(14);

for ($n = 1 ; $n <= $numberOfAssignments ; $n++)
{
	$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension(calculateCellAddress(5 + $n))->setWidth(4);
}

$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension(calculateCellAddress(5 + $n))->setWidth(6);
$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension(calculateCellAddress(6 + $n))->setWidth(8);
$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension(calculateCellAddress(7 + $n))->setWidth(7);
$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension(calculateCellAddress(8 + $n))->setWidth(14);
$objPHPExcel->setActiveSheetIndex(2)->getColumnDimension(calculateCellAddress(9 + $n))->setWidth(7);

// header and centered
$range = "A1:" . calculateCellAddress(9 + $n) . "1";
$objPHPExcel->setActiveSheetIndex(2)->getStyle($range)->applyFromArray($headerStyle);
$range = "A2:" . calculateCellAddress(9 + $n) . ( count($participants) + 1 );
$objPHPExcel->setActiveSheetIndex(2)->getStyle($range)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// border lines
$objPHPExcel->setActiveSheetIndex(2)->getStyle('C1:C' . (count($participants) + 1))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->setActiveSheetIndex(2)->getStyle('E1:E' . (count($participants) + 1))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->setActiveSheetIndex(2)->getStyle(calculateCellAddress(5 + $n) . '1:' . calculateCellAddress(5 + $n) . (count($participants) + 1))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->setActiveSheetIndex(2)->getStyle(calculateCellAddress(5 + $n) . '1:' . calculateCellAddress(5 + $n) . (count($participants) + 1))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

// data
$objPHPExcel->setActiveSheetIndex(2)->setCellValue('A1', gettext('Mat. no.'));
$objPHPExcel->setActiveSheetIndex(2)->setCellValue('B1', gettext('Last name'));
$objPHPExcel->setActiveSheetIndex(2)->setCellValue('C1', gettext('First name'));
$objPHPExcel->setActiveSheetIndex(2)->setCellValue('D1', gettext('room'));
$objPHPExcel->setActiveSheetIndex(2)->setCellValue('E1', gettext('place'));

for ($n = 1 ; $n <= $numberOfAssignments ; $n++)
{
	$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(4 + $n, 1, 'A' . $n);
}

$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(4 + $n, 1, gettext('points'));
$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(5 + $n, 1, gettext('result'));
$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(6 + $n, 1, gettext('bonus'));
$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(7 + $n, 1, gettext('final result'));
$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(8 + $n, 1, gettext('NT'));

$rowCounter=2;

foreach($participants as $participant)
{
	$objPHPExcel->setActiveSheetIndex(2)->setCellValue("A$rowCounter", $participant["matriculationNumber"]);
	$objPHPExcel->setActiveSheetIndex(2)->setCellValue("B$rowCounter", $participant["name"]);
	$objPHPExcel->setActiveSheetIndex(2)->setCellValue("C$rowCounter", $participant["forename"]);
	$objPHPExcel->setActiveSheetIndex(2)->setCellValue("D$rowCounter", validate($participant["room"]));
	$objPHPExcel->setActiveSheetIndex(2)->setCellValue("E$rowCounter", validate($participant["place"]));
	
	$NTvalue = "";
	$resultWithBonus = $eoDatabase->getExamResultWithBonus($examTerm,$participant["imtLogin"]);
	$resultWithoutBonus = $eoDatabase->getExamResultWithoutBonus($examTerm,$participant["imtLogin"]);
	$bonus = $eoDatabase->getBonus($participant["imtLogin"]);
	
	if ($participant["isNT"] == "NT") $NTvalue = "NT";
	else if ($participant["isNT"] == "BV") $NTvalue = "fraud";
	else if ($participant["isNT"] == "SICK") $NTvalue = "sick";
	
	$totalPoints = 0;
	
	for ($n = 1 ; $n <= $numberOfAssignments ; $n++)
	{
		$value = $eoDatabase->getExamPointsForAssignment($course->get_name(), $examTerm, $participant["imtLogin"], $n);
		$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(4 + $n, $rowCounter, $value);
		$totalPoints += $value;
	}
	
	$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(4 + $n, $rowCounter, $totalPoints);
	$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(5 + $n, $rowCounter, $resultWithoutBonus);
	$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(6 + $n, $rowCounter, $bonus);
	$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(7 + $n, $rowCounter, $resultWithBonus);
	$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(8 + $n, $rowCounter, $NTvalue);
	$rowCounter++;
}

////////////////////////////////////
////////// END OF SHEET 3 //////////
////////////////////////////////////

$objPHPExcel->setActiveSheetIndex(0);

//generate filename without umlaute
$umlaute = Array("/Š/","/š/","/Ÿ/","/€/","/…/","/†/","/§/");
$replace = Array("ae","oe","ue","Ae","Oe","Ue","ss");
$filenameUmlaute = gettext('exam results').'.xls';
$filename = preg_replace($umlaute, $replace, $filenameUmlaute);

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output'); 
exit();

function validate($string)
{
	return ($string == "not set") ? "---" : $string;
}

function calculateCellAddress($n)
{
	if ($n <= 26) return chr(64 + $n);
	else if ($n <= 52) return "A" . calculateCellAddress($n - 26);
	else if ($n <= 78) return "B" . calculateCellAddress($n - 52);
	else if ($n <= 104) return "C" . calculateCellAddress($n - 78);
	else if ($n <= 130) return "D" . calculateCellAddress($n - 104);
	else if ($n <= 156) return "E" . calculateCellAddress($n - 130);
	else if ($n <= 192) return "F" . calculateCellAddress($n - 156);
	else if ($n <= 218) return "G" . calculateCellAddress($n - 192);
	else if ($n <= 244) return "H" . calculateCellAddress($n - 218);
	else if ($n <= 270) return "I" . calculateCellAddress($n - 244);
}
?>
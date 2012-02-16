<?php
/*
 * generate excel file for exporting exam results
 * 
 * @author Marcel Jakoblew
 */

//error_reporting(0);
$examOffice = exam_office_file_handling::getInstance();
$eoDatabase = exam_organization_database::getInstance();
$eoDatabase->calculateExamResults($course);
$participants = $eoDatabase->getParticipantsForTerm($examTerm);

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

// Add some data
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'startHISsheet');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('O1', 'endHISsheet');

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', 'mtknr');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', 'sortname');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', 'abschl');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2', 'stg');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2', 'pversion');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2', 'pnr');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G2', 'Modul');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('H2', 'pversuch');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2', 'bewertung');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('J2', 'psem');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('K2', 'ptermin');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L2', 'pdatum');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('M2', 'pbeginn');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('N2', 'labnr');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('O2', 'pordnr');



$rowCounter=3;
$pnumber = 0; //get the p number

foreach($participants as $participant){
	$participantDataString = $eoDatabase->loadExcelRow($examTerm, $participant["matriculationNumber"]);
	$participantDataArray = $examOffice->string2RowArray($participantDataString);
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$rowCounter", $participant["matriculationNumber"]);
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B$rowCounter", $participantDataArray[1]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C$rowCounter", $participantDataArray[2]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D$rowCounter", $participantDataArray[3]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E$rowCounter", $participantDataArray[4]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F$rowCounter", $participantDataArray[5]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G$rowCounter", $participantDataArray[6]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H$rowCounter", $participantDataArray[7]);
	//$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I$rowCounter", $participantDataArray[3]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J$rowCounter", $participantDataArray[9]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K$rowCounter", $participantDataArray[10]);
	//$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L$rowCounter", $participantDataArray[3]);
	//$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M$rowCounter", $participantDataArray[3]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N$rowCounter", $participantDataArray[13]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("O$rowCounter", $participantDataArray[14]);
	$pnumber = $participantDataArray[5];
	
	//calculate result
	$resultWithBonus = $eoDatabase->getExamResultWithBonus($examTerm,$participant["imtLogin"]);
	if($participant["result"]=="5.0") $resultWithBonus="500";
	if($participant["result"]=="4.0") $resultWithBonus="400";
	if($participant["result"]=="3.7") $resultWithBonus="370";
	if($participant["result"]=="3.3") $resultWithBonus="330";
	if($participant["result"]=="3.0") $resultWithBonus="300";
	if($participant["result"]=="2.7") $resultWithBonus="270";
	if($participant["result"]=="2.3") $resultWithBonus="230";
	if($participant["result"]=="2.0") $resultWithBonus="200";
	if($participant["result"]=="1.7") $resultWithBonus="170";
	if($participant["result"]=="1.3") $resultWithBonus="130";
	if($participant["result"]=="1.0") $resultWithBonus="100";
	if($participant["isNT"]==1) $resultWithBonus="500"; //TODO: get number from stefan finke
	if($participant["isNT"]=="BV") $resultWithBonus="500";  //TODO: get number from stefan finke
	if($participant["isNT"]=="SICK") $resultWithBonus="500";  //TODO: get number from stefan finke
	if($participant["isNT"]=="NT") $resultWithBonus="500";  //TODO: get number from stefan finke
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I$rowCounter", $resultWithBonus); //results
	$rowCounter++;
}



$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$rowCounter", 'endHISsheet');

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="prf'.$pnumber.'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output'); 
exit();
?>
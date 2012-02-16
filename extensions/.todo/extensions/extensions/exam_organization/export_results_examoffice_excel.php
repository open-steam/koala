<?php
/*
 * generate excel file for exporting exam results
 * 
 * @author Marcel Jakoblew
 */

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
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', gettext('Matriculation number'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', gettext('Last name'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1', gettext('First name'));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1', gettext('Exam result'));
$rowCounter=2;

foreach($participants as $participant){
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A$rowCounter", $participant["matriculationNumber"]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B$rowCounter", $participant["name"]);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C$rowCounter", $participant["forename"]);
	//$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D$rowCounter", $participant["result"]);//old version without bonus
	$resultWithBonus = $eoDatabase->getExamResultWithBonus($examTerm,$participant["imtLogin"]);
	if($participant["isNT"]==1) $resultWithBonus=gettext("NT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D$rowCounter", $resultWithBonus);
	$rowCounter++;
}

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
?>
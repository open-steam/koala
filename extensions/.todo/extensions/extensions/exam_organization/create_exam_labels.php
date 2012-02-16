<?php
/*
 * create exam labels with barcodes
 *
 * @author Dominik Lüke
 */

require_once(PATH_CLASSES . 'tcpdf/config/lang/eng.php');
require_once(PATH_CLASSES . 'tcpdf/tcpdf.php');

global $y, $pdf, $style, $exam_name, $semester, $date, $IDcounter;

// Extend the TCPDF class to create custom Header and Footer
class ExamLabelsPdf extends TCPDF {
    public function Footer() {
        $this->SetFont('helvetica', 'BI', 10);
    	$this->SetXY(10, -15); // 1.5 cm from bottom
        $this->Cell(0, 12, gettext("required label type: ") . "Avery Zweckform L4744", 0, 0, 'L');
		$this->SetX(100);
    	$this->Cell(0, 12, $this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, 0, 'C');
    }
}

define('LABEL_HEIGHT', 51);
define('X1', 7.7 + 3.5); //plus Offset within Label
define('X2', 106.3 + 3.5); //plus Offset within Label
define('Y', 21 + 3); //plus Offset within Label

$course_id = $course->get_name();
$exam_name = $course->get_attribute("OBJ_DESC"); //TODO: use object method of abstraction layer

$semester = $course->get_semester()->get_name(); // returns e.g. WS0910
$semester = substr($semester, 0, -2) . "/" . substr($semester, -2);
$semester = str_replace("WS", gettext("winter") . " ", $semester);
$semester = str_replace("SS", gettext("summer") . " ", $semester);

$examObject = exam_organization_exam_object_data::getInstance($course);
$day = $examObject->getDateDay($examTerm);
$month = $examObject->getDateMonth($examTerm);
$year = $examObject->getDateYear($examTerm);

//$day = $course->get_attribute("EXAM" . $examTerm . "_exam_date_day");
//$month = $course->get_attribute("EXAM" . $examTerm . "_exam_date_month");
//$year = $course->get_attribute("EXAM" . $examTerm . "_exam_date_year");
$date = sprintf("%02d.%02d.%04d", $day, $month, $year);


// create new PDF document
$pdf = new ExamLabelsPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('KoaLA');
$pdf->SetTitle($exam_name);
$pdf->SetSubject(gettext("exam labels"));
$pdf->SetKeywords(gettext("exam labels") . ", " . $exam_name);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

//set auto page breaks
$pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);

// remove default header
$pdf->setPrintHeader(false);

// ---------------------------------------------------------

$style = array(
    'position' => 'L',
    'border' => false,
    'padding' => 0,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255),
    'text' => true,
    'font' => 'helvetica',
    'fontsize' => 8,
    'stretchtext' => 4
);

$eoDatabase = exam_organization_database::getInstance();
$usedRooms = $eoDatabase->getUsedRooms($course_id, $examTerm);
$IDcounter = 0;

foreach ($usedRooms as $room)
{
	$participantLogins = $eoDatabase->getParticipantsByRoom($course_id, $room["room"], $examTerm);
	$leftLabel = true;
	$counter = 0;
	$pdf->AddPage();
	$y = Y;

	foreach ($participantLogins as $participantLogin)
	{
		$data = $eoDatabase->getParticipantData($participantLogin["imtLogin"]);
		$forenameArray = split(" ", $data["forename"]);
		$forenames = $forenameArray[0];
		
		if (count($forenameArray) > 1) $forenames .= " ";
		
		for ($n = 1 ; $n < count($forenameArray) ; $n++)
		{
			$forenames .= substr($forenameArray[$n], 0, 1);
			
			if ($n < count($forenameArray) - 1) $forenames .= ". ";
			else $forenames .= ".";
		}
		
		if ($leftLabel)
		printLeftLabel(utf8_encode($data["name"]), utf8_encode($forenames), $data["matriculationNumber"], validate($data["room"]), validate($data["place"]));
		else
		printRightLabel(utf8_encode($data["name"]), utf8_encode($forenames), $data["matriculationNumber"], validate($data["room"]), validate($data["place"]));

		$leftLabel = !$leftLabel;
		$counter++;

		if ($counter % 2 == 0) $y += LABEL_HEIGHT;

		if ($counter % 10 == 0)
		{
			$y = Y; //plus Offset
			$pdf->AddPage();
		}
	}
}

//generate filename without umlaute
$umlaute = Array("/�/","/�/","/�/","/�/","/�/","/�/","/�/");
$replace = Array("ae","oe","ue","Ae","Oe","Ue","ss");
$filenameUmlaute = gettext("exam labels") . ' ' . $exam_name . '(' . $examTerm . ').pdf';
$filename = preg_replace($umlaute, $replace, $filenameUmlaute);

$pdf->Output($filename, 'D');

function printLeftLabel($name, $forename, $matrNr, $room, $place)
{
	global $y, $pdf, $style, $exam_name, $semester, $date, $IDcounter;

	$pdf->SetFont('helvetica', '', 12);
	$pdf->MultiCell(90, 5, $exam_name, 0, 'C', 0, 0, X1, $y, true);
	$pdf->MultiCell(90, 5, $date . ' (' . $semester . ')', 0, 'C', 0, 0, X1, $y + 5, true);

	$pdf->SetFont('helvetica', 'B', 12);
	$pdf->MultiCell(90, 5, $name . ', ' . $forename . ' (' . $matrNr . ')', 0, 'C', 0, 0, X1, $y + 13, true);
	$pdf->SetFont('helvetica', '', 10);
	$pdf->MultiCell(90, 5, gettext("room") . ' ' . $room . ', ' . gettext("place") . ' ' . $place, 0, 'C', 0, 0, X1, $y + 18, true);

	$checksum = buildChecksum('00000' . $matrNr);
	$pdf->write1DBarcode('00000' . $matrNr . $checksum, 'EAN13', X1 + 45, $y + 25, 38, 20, 0.4, $style, 'C');
	
	$pdf->SetFont('helvetica', 'B', 20);
	$pdf->MultiCell(20, 5, ++$IDcounter, 0, 'C', 0, 0, X1 + 7, $y + 28, true);
}

function printRightLabel($name, $forename, $matrNr, $room, $place)
{
	global $y, $pdf, $style, $exam_name, $semester, $date, $IDcounter;

	$pdf->SetFont('helvetica', '', 12);
	$pdf->MultiCell(90, 5, $exam_name, 0, 'C', 0, 0, X2, $y, true);
	$pdf->MultiCell(90, 5, $date . ' (' . $semester . ')', 0, 'C', 0, 0, X2, $y + 5, true);

	$pdf->SetFont('helvetica', 'B', 12);
	$pdf->MultiCell(90, 5, $name . ', ' . $forename . ' ('.$matrNr.')', 0, 'C', 0, 0, X2, $y + 13, true);
	$pdf->SetFont('helvetica', '', 10);
	$pdf->MultiCell(90, 5, gettext("room") . ' ' . $room . ', ' . gettext("place") . ' ' . $place, 0, 'C', 0, 0, X2, $y + 18, true);

	$checksum = buildChecksum('00000' . $matrNr);
	$pdf->write1DBarcode('00000' . $matrNr . $checksum, 'EAN13', X2 + 45, $y + 25, 38, 20, 0.4, $style, 'C');
	
	$pdf->SetFont('helvetica', 'B', 20);
	$pdf->MultiCell(20, 5, ++$IDcounter, 0, 'C', 0, 0, X2 + 7, $y + 28, true);
}

function buildChecksum($ean)
{
	$s = preg_replace("/([^\d])/", "", $ean);
	if (strlen($s) != 12) return false;

	$check = 0;
	for ($i = 0 ; $i < 12 ; $i++)
		$check += (($i % 2) * 2 + 1) * $s{$i};
	
	return (10 - ($check % 10)) % 10;
}

function validate($string)
{
	return ($string == "not set") ? "---" : $string;
}
?>
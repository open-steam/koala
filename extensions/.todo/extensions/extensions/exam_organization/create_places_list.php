<?php

/*
 * create participants list for exam supervisors
 *
 * @author Dominik Lüke
 */

require_once(PATH_CLASSES . 'tcpdf/config/lang/eng.php');
require_once(PATH_CLASSES . 'tcpdf/tcpdf.php');

global $exam_name, $date, $pdf, $table, $eoDatabase;

// Extend the TCPDF class to create custom Header and Footer
class PlacesListPdf extends TCPDF {

    public function Header() {
    	global $exam_name, $date;
        $this->ImageEps('../extensions/exam_organization/images/upb_logo_full.ai', 25, 12, 70);
        $this->SetFont('helvetica', 'B', 22);
        $this->MultiCell(70, 10, gettext("seating plan"), 0, 'C', 0, 0, 115, 17);
		$this->SetFont('helvetica', 'B', 14);
        $this->MultiCell(130, 5, $exam_name, 0, 'L', 0, 0, 25, 40);
        $this->MultiCell(26, 5, $date, 0, 'R', 0, 0, 159, 40);
	}
    
    public function Footer() {
        $this->SetY(-16); // 1.6 cm from bottom
        $this->SetFont('helvetica', 'BI', 12);
        $this->Cell(0, 12, $this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, 0, 'C');
    }
}


$exam_name = $course->get_attribute("OBJ_DESC");

$examObject = exam_organization_exam_object_data::getInstance($course);
$day = $examObject->getDateDay($examTerm);
$month = $examObject->getDateMonth($examTerm);
$year = $examObject->getDateYear($examTerm);

//$day = $course->get_attribute("EXAM" . $examTerm . "_exam_date_day"); //TODO: use object method
//$month = $course->get_attribute("EXAM" . $examTerm . "_exam_date_month");  //TODO: use object method
//$year = $course->get_attribute("EXAM" . $examTerm . "_exam_date_year"); //TODO: use object method
$date = sprintf("%02d.%02d.%04d", $day, $month, $year);

// create new PDF document
$pdf = new PlacesListPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('KoaLA');
$pdf->SetTitle($exam_name);
$pdf->SetSubject(gettext("seating plan"));
$pdf->SetKeywords(gettext("seating plan") . ', ' . $exam_name);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

//set margins
$pdf->SetMargins(25, 50, 25);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 19);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);

$pdf->SetFont('helvetica', '', 10);

// ---------------------------------------------------------

$eoDatabase = exam_organization_database::getInstance();
$participants = $eoDatabase->getParticipantsForTerm($examTerm, " ORDER BY matriculationNumber ASC");
$p_count = sizeof($participants);

$leftCol = array();
$rightCol = array();
	
for ($n = 0 ; $n < $p_count ; $n++)
{
	if ($n % 94 < 47)
	$leftCol[] = array( "mnr" => $participants[$n]["matriculationNumber"], "room" => $participants[$n]["room"], "place" => $participants[$n]["place"]);
	else
	$rightCol[] = array( "mnr" => $participants[$n]["matriculationNumber"], "room" => $participants[$n]["room"], "place" => $participants[$n]["place"]);

	if ($n % 94 == 93)
	{
		printPage($leftCol, $rightCol);
		$leftCol = array();
		$rightCol = array();
	}
}
	
if (sizeof($leftCol) != 0) printPage($leftCol, $rightCol);

$course_id = $course->get_name();
$usedRooms = $eoDatabase->getUsedRooms($course_id, $examTerm);

foreach ($usedRooms as $room)
{
	printRoom($room["room"]);
}


//generate filename without umlaute
$umlaute = Array("/�/","/�/","/�/","/�/","/�/","/�/","/�/");
$replace = Array("ae","oe","ue","Ae","Oe","Ue","ss");
$filenameUmlaute = gettext("seating plan") . ' ' . $exam_name . '(' . $examTerm . ').pdf';
$filename = preg_replace($umlaute, $replace, $filenameUmlaute);

$pdf->Output($filename, 'D');

// ---------------------------------------------------------

function printRoom($room)
{
	global $pdf, $eoDatabase;
	
	switch ($room)
	{
		case "AudiMax":
			$x = 22;
			$y = 22;
			$width = 160;
			break;
			
		case "C1":
			$x = 25;
			$y = 50;
			$width = 160;
			break;

		case "C2":
			$x = 40;
			$y = 50;
			$width = 140;
			break;
			
		case "G":
			$x = 45;
			$y = 30;
			$width = 120;
			break;
			
		default:
			// any case where no plan is available -> Sporthalle, "not set"
			return;
	}
	
	$maxSeats = gettext("total seats: ") . $eoDatabase->getNumberOfPlaces($room);
	
	$pdf->setPrintHeader(false);
	$pdf->addPage();
	
	$pdf->SetFont('helvetica', '', 20);
	$pdf->StartTransform();
		$pdf->Rotate(90);
		$pdf->Text(30, 50, gettext("seating"));
		$pdf->Text(-210, 50, gettext("lecture room"));
		$pdf->SetFont('helvetica', 'B', 25);
		$pdf->Text(-210, 60, $room);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->Text(-210, 215, $maxSeats);
		$pdf->setTextColor(204, 0, 0);
		$pdf->Text(-210, 220, gettext("numbered seats = used seats"));
		$pdf->setTextColor(0, 0, 0);
		$pdf->ImageEps('../extensions/exam_organization/images/upb_logo_full.ai', 13, 210, 45);
	$pdf->StopTransform();
	
	$pdf->ImageEps('../extensions/exam_organization/images/' . $room . '.ai', $x, $y, $width);
}

function printPage($leftCol, $rightCol)
{
	global $pdf, $table;
	$pdf->addPage();
	$fill = false;
	
	// Header
	$table = "";
	$table .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
	$table .= "<thead>";
 	$table .= "<tr bgcolor=\"#000000\" color=\"#FFFFFF\">";
	$table .= "<td width=\"60\" align=\"center\"><b>" . gettext("matr. no.") . "</b></td>";
	$table .= "<td width=\"65\" align=\"center\"><b>" . gettext("room") . "</b></td>";
	$table .= "<td width=\"80\" align=\"center\"><b>" . gettext("place") . "</b></td>";
	$table .= "<td width=\"43\" bgcolor=\"#FFFFFF\"></td>";
	
	if (sizeof($rightCol) > 0)
	{
		$table .= "<td width=\"60\" align=\"center\"><b>" . gettext("matr. no.") . "</b></td>";
		$table .= "<td width=\"65\" align=\"center\"><b>" . gettext("room") . "</b></td>";
		$table .= "<td width=\"80\" align=\"center\"><b>" . gettext("place") . "</b></td>";
	}
	else
	{
		$table .= "<td bgcolor=\"#FFFFFF\" width=\"205\" colspan=\"3\"></td>";
	}
	$table .= "</tr>";
	$table .= "</thead>";
	
	//Data
	for ($n = 0 ; $n < sizeof($leftCol) ; $n++)
	{
		$table .= ($fill) ? "<tr bgcolor=\"#DDDDDD\">" : "<tr>";
 
		$table .= "<td width=\"60\" align=\"center\"><b>" . $leftCol[$n]["mnr"] . "</b></td>";
		$table .= "<td width=\"65\" align=\"center\"><b>" . validate($leftCol[$n]["room"]) . "</b></td>";
		$table .= "<td width=\"80\" align=\"center\"><b>" . validate($leftCol[$n]["place"]) . "</b></td>";
		$table .= "<td width=\"43\" bgcolor=\"#FFFFFF\"></td>";
		
		if ($n < sizeof($rightCol))
		{
			$table .= "<td width=\"60\" align=\"center\"><b>" . $rightCol[$n]["mnr"] . "</b></td>";
			$table .= "<td width=\"65\" align=\"center\"><b>" . validate($rightCol[$n]["room"]) . "</b></td>";
			$table .= "<td width=\"80\" align=\"center\"><b>" . validate($rightCol[$n]["place"]) . "</b></td>";
		}
		else
		{
			$table .= "<td bgcolor=\"#FFFFFF\" width=\"205\" colspan=\"3\"></td>";
		}

		$table .= "</tr>";
		$fill = !$fill;
	}
	$table .= "</table>";
	$pdf->writeHTML($table, true, false, false, false, '');
}

function validate($string)
{
	return ($string == "not set") ? "---" : $string;
}
?>
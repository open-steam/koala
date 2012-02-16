<?php

/*
 * create participants list for exam supervisors
 *
 * @author Dominik Lüke
 */

require_once(PATH_CLASSES . 'tcpdf/config/lang/eng.php');
require_once(PATH_CLASSES . 'tcpdf/tcpdf.php');

global $exam_name, $date;

// Extend the TCPDF class to create custom Header and Footer
class ParticipantsListPdfByPlace extends TCPDF {

	public function Header() {
		global $exam_name, $date;
		$this->ImageEps('../extensions/exam_organization/images/upb_logo_full.ai', 25, 12, 70);
		$this->SetFont('helvetica', 'B', 22);
		$this->MultiCell(70, 10, gettext("participant list"), 0, 'C', 0, 0, 115, 17);
		$this->SetTextColor(255,0,0);
		$this->SetFont('helvetica', 'B', 10);
		$this->MultiCell(80, 3, "- " . gettext("FOR INTERNAL USE ONLY") . " -", 0, 'C', 0, 0, 110, 28);
		$this->SetTextColor(0,0,0);
		$this->SetFont('helvetica', '', 14);
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

//$day = $course->get_attribute("EXAM" . $examTerm . "_exam_date_day");
//$month = $course->get_attribute("EXAM" . $examTerm . "_exam_date_month");
//$year = $course->get_attribute("EXAM" . $examTerm . "_exam_date_year");
$date = sprintf("%02d.%02d.%04d", $day, $month, $year);

// create new PDF document
$pdf = new ParticipantsListPdfByPlace(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('KoaLA');
$pdf->SetTitle($exam_name);
$pdf->SetSubject(gettext("participant list"));
$pdf->SetKeywords(gettext("participant list") . ', ' . $exam_name);


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

// ---------------------------------------------------------

$eoDatabase = exam_organization_database::getInstance();
$participants = $eoDatabase->getParticipantsForTerm($examTerm, " ORDER BY room, place ASC");

$pdf->addPage();
$pdf->SetFont('helvetica', '', 10);
$fill = false;

$tbl = "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\">";
$tbl .= "<thead>";
$tbl .= "<tr bgcolor=\"#000000\" color=\"#FFFFFF\">";
$tbl .= "<td width=\"150\"><b>" . gettext("name") . "</b></td>";
$tbl .= "<td width=\"100\"><b>" . gettext("forename") . "</b></td>";
$tbl .= "<td width=\"60\" align=\"center\"><b>" . gettext("matr. no.") . "</b></td>";
$tbl .= "<td width=\"65\" align=\"center\"><b>" . gettext("room") . "</b></td>";
$tbl .= "<td width=\"80\" align=\"center\"><b>" . gettext("place") . "</b></td>";
$tbl .= "</tr>";
$tbl .= "</thead>";

foreach($participants as $p)
{
	$tbl .= ($fill) ? "<tr bgcolor=\"#DDDDDD\">" : "<tr>";
	$tbl .= "<td width=\"150\">" . utf8_encode($p["name"]) . "</td>";
	$tbl .= "<td width=\"100\">" . utf8_encode($p["forename"]) . "</td>";
	$tbl .= "<td width=\"60\" align=\"center\">" . $p["matriculationNumber"] . "</td>";
	$tbl .= "<td width=\"65\" align=\"center\">" . validate($p["room"]) . "</td>";
	$tbl .= "<td width=\"80\" align=\"center\">" . validate($p["place"]) . "</td>";
	$tbl .= "</tr>";
	
	$fill = !$fill;
}

$tbl .=  "</table>";

$pdf->writeHTML($tbl, true, false, false, false, '');


//generate filename without umlaute
$umlaute = Array("/�/","/�/","/�/","/�/","/�/","/�/","/�/");
$replace = Array("ae","oe","ue","Ae","Oe","Ue","ss");
$filenameUmlaute = gettext("participant list") . ' ' . $exam_name . '(' . $examTerm . ').pdf';
$filename = preg_replace($umlaute, $replace, $filenameUmlaute);

$pdf->Output($filename, 'D');


function validate($string)
{
	return ($string == "not set") ? "---" : $string;
}
?>
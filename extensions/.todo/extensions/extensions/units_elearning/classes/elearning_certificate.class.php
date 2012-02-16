<?php
require_once(PATH_CLASSES . 'tcpdf/config/lang/eng.php');
require_once(PATH_CLASSES . 'tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class CERTPDF extends TCPDF {
    //Page header
    public function Header() {
        // full background image
        // store current auto-page-break status
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->AutoPageBreak;
        $this->SetAutoPageBreak(false, 0);
        //$img_file = PATH_EXTENSIONS . "units_elearning/assets/zertifikat.jpg";
        $img_file = PATH_EXTENSIONS . "units_elearning/assets/zertifikat2.jpg";
        $this->Image($img_file, 0, 0, 210, 297, '', '', '', false, 150, '', false, false, 0);
        // restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
    }
}

class elearning_certificate {
	public function create_certificate($login = "lneumann", $name = "Laura Neumann", $points = "51", $max_points = "55", $date = "5. Mai 2010") {
		// create new PDF document
		$pdf = new CERTPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		// set document information
		$pdf->SetCreator("Bäckerei denkt Zukunft");
		$pdf->SetAuthor('');
		$pdf->SetTitle('Zertifikat');
		$pdf->SetSubject('T');
		$pdf->SetKeywords('');
		
		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(0);
		$pdf->SetFooterMargin(0);
		
		// remove default footer
		$pdf->setPrintFooter(false);
		
		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		
		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
		
		//set some language-dependent strings
		//$pdf->setLanguageArray($l); 
		
		// ---------------------------------------------------------
		
		
		
		// add a page
		$pdf->AddPage();
		$pdf->SetFont('helvetica', 'B', 25);
		$pdf->Ln(60);
		$pdf->Cell(0, 10, $name, 0, 1, 'C', 0, '', 0);
		$pdf->Ln(80);
		$pdf->SetFont('helvetica', '', 14);
		$pdf->Cell(0, 5, "hat am " . $date, 0, 1, 'C', 0, '', 0);
		$pdf->Cell(0, 5, "an dem E-Learning-Kurs", 0, 1, 'C', 0, '', 0);
		$pdf->Ln(7);
		$pdf->SetFont('helvetica', 'B', 14);
		$pdf->Cell(0, 5, "»Aktive und umsatzorientierte Verkaufssprache«", 0, 1, 'C', 0, '', 0);
		$pdf->Ln(7);
		$pdf->SetFont('helvetica', '', 14);
		$pdf->Cell(0, 5, "mit Erfolg teilgenommen", 0, 1, 'C', 0, '', 0);
		$pdf->Cell(0, 5, "und $points von $max_points Punkten erreicht.", 0, 1, 'C', 0, '', 0);
		$pdf->Cell(0, 5, "Herzlichen Glückwunsch!", 0, 1, 'C', 0, '', 0);
		
		
		// ---------------------------------------------------------
		
		return $pdf->getPDFData();
	}
}
?>
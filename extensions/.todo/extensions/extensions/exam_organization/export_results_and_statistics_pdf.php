<?php
/*
 * generate pdf file with exam results
 * 
 * @author Dominik LÃ¼ke
 */

error_reporting(E_ALL);

$eoDatabase = exam_organization_database::getInstance();
$eoDatabase->calculateExamResults($course);
$examObject = exam_organization_exam_object_data::getInstance($course);

include PATH_CLASSES . "libchart/classes/libchart.php";
require_once(PATH_CLASSES . 'tcpdf/config/lang/eng.php');
require_once(PATH_CLASSES . 'tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Footer
class MYPDF extends TCPDF {
    public function Footer() {
        $this->SetY(-16); // 1.6 cm from bottom
        $this->SetFont('helvetica', 'BI', 12);
        $this->Cell(0, 12, $this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, 0, 'C');
        $this->Image('../classes/libchart/images/Libchart.png', 10, 285, 20, '', '', '', false, 150);
    }
}

// fetch information from DB
$exam_name = $course->get_attribute("OBJ_DESC");

$semester = $course->get_semester()->get_name(); // returns e.g. WS0910
$semester = substr($semester, 0, -2) . "/" . substr($semester, -2);
$semester = str_replace("WS", gettext("winter") . " ", $semester);
$semester = str_replace("SS", gettext("summer") . " ", $semester);

$day = $examObject->getDateDay($examTerm);
$month = $examObject->getDateMonth($examTerm);
$year = $examObject->getDateYear($examTerm);


$date = sprintf("%02d.%02d.%04d", $day, $month, $year);

$key = session_id();

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('KoaLA');
$pdf->SetTitle($exam_name);
$pdf->SetSubject(gettext("exam results"));
$pdf->SetKeywords(gettext("exam results") . ", " . $exam_name);

// header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);

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

// ---------------------------------------------------------	

// first page
$pdf->AddPage();
$pdf->Line(20,20,190,20);
$pdf->ImageEps('../extensions/exam_organization/images/upb_logo.ai', 30, 30, 13);
$pdf->SetFont('helvetica', '', 16);
$pdf->MultiCell(80, 3, gettext("exam results"), 0, 'C', 0, 0, 75, 25);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->MultiCell(130, 3, $exam_name, 0, 'C', 0, 0, 50, 33);
$pdf->MultiCell(130, 3, $date . " (" . $semester . ")", 0, 'C', 0, 0, 50, 40);
$pdf->SetFont('helvetica', '', 10);
//$pdf->MultiCell(130, 2, "Prof. Dr. Reinhard Keil", 0, 'C', 0, 0, 50, 50);
$pdf->Line(20,60,190,60);

$eoDatabase = exam_organization_database::getInstance();
$eoDatabase->calculateExamResults($course);
$participants = $eoDatabase->getParticipantsForTerm($examTerm);
$notPassed = 0;

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

// ---------------------------------------------------------	

$tbl = "<table border=\"1\" cellpadding=\"1\" cellspacing=\"0\">";
$tbl .= "<thead>";
$tbl .= "<tr bgcolor=\"#FFFFFF\" color=\"#000000\">";
$tbl .= "<td width=\"50\" align=\"center\"><b>" . gettext("mark") . "</b></td>";
$tbl .= "<td width=\"75\" align=\"center\"><b>" . gettext("points") . "</b></td>";
$tbl .= "<td width=\"60\" align=\"center\"><b>" . gettext("# no bonus") . "</b></td>";
$tbl .= "<td width=\"60\" align=\"center\"><b>" . gettext("# bonus") . "</b></td>";
$tbl .= "</tr>";
$tbl .= "</thead>";

foreach($summary as $s)
{
	$tbl .= "<tr align=\"center\">";
	$tbl .= "<td width=\"50\" align=\"center\">" . $s["mark"] . "</td>";
	$tbl .= "<td width=\"75\" align=\"center\">" . $s["from"] . " - " . $s["to"] . "</td>";
	$tbl .= "<td width=\"60\" align=\"center\">" . $s["countNoBonus"] . "</td>";
	$tbl .= "<td width=\"60\" align=\"center\">" . $s["countBonus"] . "</td>";
	$tbl .= "</tr>";
}

$tbl .=  "</table>";
$pdf->setXY(60,65);
$pdf->writeHTML($tbl, true, false, false, false, '');	

// ----------------------------------------------------------------

$chart = new VerticalBarChart(800, 400);

$datasetNoBonus = new XYDataSet();
$datasetNoBonus->addPoint(new Point("1,0", $summary["10"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("1,3", $summary["13"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("1,7", $summary["17"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("2,0", $summary["20"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("2,3", $summary["23"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("2,7", $summary["27"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("3,0", $summary["30"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("3,3", $summary["33"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("3,7", $summary["37"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("4,0", $summary["40"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("5,0", $summary["50"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("NT", $summary["NT"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("BV", $summary["BV"]["countNoBonus"]));
$datasetNoBonus->addPoint(new Point("SICK", $summary["SICK"]["countNoBonus"]));
	
$datasetBonus = new XYDataSet();
$datasetBonus->addPoint(new Point("1,0", $summary["10"]["countBonus"]));
$datasetBonus->addPoint(new Point("1,3", $summary["13"]["countBonus"]));
$datasetBonus->addPoint(new Point("1,7", $summary["17"]["countBonus"]));
$datasetBonus->addPoint(new Point("2,0", $summary["20"]["countBonus"]));
$datasetBonus->addPoint(new Point("2,3", $summary["23"]["countBonus"]));
$datasetBonus->addPoint(new Point("2,7", $summary["27"]["countBonus"]));
$datasetBonus->addPoint(new Point("3,0", $summary["30"]["countBonus"]));
$datasetBonus->addPoint(new Point("3,3", $summary["33"]["countBonus"]));
$datasetBonus->addPoint(new Point("3,7", $summary["37"]["countBonus"]));
$datasetBonus->addPoint(new Point("4,0", $summary["40"]["countBonus"]));
$datasetBonus->addPoint(new Point("5,0", $summary["50"]["countBonus"]));
$datasetBonus->addPoint(new Point("NT", $summary["NT"]["countBonus"]));
$datasetBonus->addPoint(new Point("BV", $summary["BV"]["countBonus"]));
$datasetBonus->addPoint(new Point("SICK", $summary["SICK"]["countBonus"]));
	
$dataSet = new XYSeriesDataSet();
$dataSet->addSerie(gettext("with bonus"), $datasetBonus);
$dataSet->addSerie(gettext("without bonus"), $datasetNoBonus);
$chart->setDataSet($dataSet);	
$chart->setTitle("");
$chart->render(EXAM_ORGANIZATION_TEMP_DIR . $key . "notenspiegel.png");

$pdf->Image(EXAM_ORGANIZATION_TEMP_DIR . $key . 'notenspiegel.png', 47, 145, 180, 90, '', '', '', false, 300);

// ---------------------------------------------------------

$passed = count($participants) - $notPassed - $summary["NT"]["countBonus"] - $summary["BV"]["countBonus"] - $summary["SICK"]["countBonus"];
$passedPercent = number_format($passed / count($participants) * 100,2);
$notPassedPercent = number_format($notPassed / count($participants) * 100 ,2);
$NTpercent = number_format($summary["NT"]["countBonus"] / count($participants) * 100 ,2);
$BVpercent = number_format($summary["BV"]["countBonus"] / count($participants) * 100 ,2);
$SICKpercent = number_format($summary["SICK"]["countBonus"] / count($participants) * 100 ,2);

$tbl = "<table border=\"1\" cellpadding=\"1\" cellspacing=\"0\">";
$tbl .= "<thead>";
$tbl .= "<tr bgcolor=\"#FFFFFF\" color=\"#000000\">";
$tbl .= "<td width=\"75\" align=\"center\"></td>";
$tbl .= "<td width=\"50\" align=\"center\"><b>" . gettext("number") . "</b></td>";
$tbl .= "<td width=\"50\" align=\"center\"><b>" . gettext("in %") . "</b></td>";
$tbl .= "</tr>";
$tbl .= "</thead>";

$tbl .= "<tr align=\"center\">";
$tbl .= "<td width=\"75\" align=\"center\">" . gettext("participants") . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . count($participants) . "</td>";
$tbl .= "<td width=\"50\" align=\"center\"></td>";
$tbl .= "</tr>";

$tbl .= "<tr align=\"center\">";
$tbl .= "<td width=\"75\" align=\"center\">" . gettext("passed") . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . $passed . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . $passedPercent . "% </td>";
$tbl .= "</tr>";

$tbl .= "<tr align=\"center\">";
$tbl .= "<td width=\"75\" align=\"center\">" . gettext("not passed") . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . $notPassed . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . $notPassedPercent . "% </td>";
$tbl .= "</tr>";

$tbl .= "<tr align=\"center\">";
$tbl .= "<td width=\"75\" align=\"center\">" . gettext("NT") . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . $summary["NT"]["countBonus"] . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . $NTpercent . "% </td>";
$tbl .= "</tr>";

$tbl .= "<tr align=\"center\">";
$tbl .= "<td width=\"75\" align=\"center\">" . gettext("BV") . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . $summary["BV"]["countBonus"] . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . $BVpercent . "% </td>";
$tbl .= "</tr>";

$tbl .= "<tr align=\"center\">";
$tbl .= "<td width=\"75\" align=\"center\">" . gettext("SICK") . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . $summary["SICK"]["countBonus"] . "</td>";
$tbl .= "<td width=\"50\" align=\"center\">" . $SICKpercent . "% </td>";
$tbl .= "</tr>";

$tbl .=  "</table>";
$pdf->setXY(72,237);
$pdf->writeHTML($tbl, true, false, false, false, '');

// ----------------------------------------------------------------

$assignments = $course->get_attribute("EXAM" . $examTerm . "_number_of_assignments");
$assignment = $examObject->getAssignmentMaxPoints($examTerm);

for ($i = 1 ; $i <= $assignments ; $i++)
{
	$assignmentPoints = $assignment[$i];
	$summary = array();
	
	for ($n = 0 ; $n < $assignmentPoints ; $n++)
	{
		$summary[$n * 10] = 0;
		$summary[$n * 10 + 5] = 0;
	}
	
	$summary[$assignmentPoints * 10] = 0;
	$statistic = $eoDatabase->getAssignmentStatistics($examTerm, $i);
	
	foreach ($statistic as $index => $subArray)
	{
		$points = $subArray["reachedPoints"];
		$count = $subArray["count"];
		
		switch ($points % 10)
		{
			case 0: break;
			case 1: $points -= 1; break;
			case 2: $points -= 2; break;
			case 3: $points += 2; break;
			case 4: $points += 1; break;
			case 5: break;
			case 6: $points -= 1; break;
			case 7: $points -= 2; break;
			case 8: $points += 2; break;
			case 9: $points += 1; break;
		}
		
		$summary[$points] += $count;
	}
	
	$dataSet = new XYDataSet();
	
	foreach ($summary as $index => $count)
	{
		$dataSet->addPoint(new Point(number_format($index/10,1), $count));
	}
	$chart = new VerticalBarChart(800, 400);
	$chart->setDataSet($dataSet);
	$chart->setTitle(gettext("assignment") . " " . $i);
	$chart->render(EXAM_ORGANIZATION_TEMP_DIR . $key . "assignment" . $i . ".png");
}

for ($i = 1 ; $i <= $assignments ; $i++)
{
	switch (($i - 1) % 3)
	{
		case 0:
			$pdf->AddPage();
			$y = 30;
			break;
			
		case 1:
			$y = 110;
			break;
			
		case 2:
			$y = 190;
			break;
	}
	
	$pdf->Image(EXAM_ORGANIZATION_TEMP_DIR . $key . 'assignment' . $i . '.png', 30, $y, 150, 75, '', '', '', false, 300);
}


//generate filename without umlaute
$umlaute = Array("/Š/","/š/","/Ÿ/","/€/","/…/","/†/","/§/");
$replace = Array("ae","oe","ue","Ae","Oe","Ue","ss");
$filenameUmlaute = gettext("exam statistics") . ' ' . $exam_name . '.pdf';
$filename = preg_replace($umlaute, $replace, $filenameUmlaute);

$pdf->Output($filename, 'D');


for ($i = 1 ; $i <= $assignments ; $i++)
{
	unlink(EXAM_ORGANIZATION_TEMP_DIR . $key . "assignment" . $i . ".png");
}

unlink(EXAM_ORGANIZATION_TEMP_DIR . $key . "notenspiegel.png");
?>
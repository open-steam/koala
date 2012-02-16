<?php
/*
 * page for exam results input
 * a form to enter exam results
 * 
 * @author Marcel Jakoblew
 */

error_reporting(E_ALL);

$value_student = "";
$value_matnr = "";
$NTchecked = "";
$BVchecked = "";
$SICKchecked = "";
$barcodeDisabled = "";
$js_focus = "document.InputForm.input_result_barcode_matnr.select();";
$totalPoints = 0;

//GET data evaluation
if (isset($_GET['mnr']) && !isset($_POST['hidden_action']))
{
	$problems = "";
	$value_matnr = $_GET['mnr'];

	//get Student Name
	$eoDatabase = exam_organization_database::getInstance();
	$imt_login = $eoDatabase->getLogin($value_matnr);
	$data = $eoDatabase->getParticipantData($imt_login);
		
	if ($data) $value_student = utf8_encode($data["forename"] . " " . $data["name"]);
	else $problems = gettext("Matriculation Number not found");
	
	//get Points
	$points = $eoDatabase->getExamPoints($course->get_course_id(), $examTerm, $imt_login);
	$numberOfAssignments = $course->get_attribute("EXAM" . $examTerm . "_number_of_assignments");
	
	$reachedPoints = array();
	
	foreach ($points as $id => $subArray)
	{
		$reachedPoints[] = $subArray["reachedPoints"];
		$totalPoints += $subArray["reachedPoints"];
	}
	
	$js_focus = "document.getElementById('1').select();";
	$barcodeDisabled = 'disabled="true"';
	
	if ($data["isNT"] == "NT") $NTchecked = 'checked="checked"';
	else if ($data["isNT"] == "BV") $BVchecked = 'checked="checked"';
	else if ($data["isNT"] == "SICK") $SICKchecked = 'checked="checked"';
}


//POST data evaluation
if (isset($_POST['hidden_action']))
{	
	$problems = "";
	
	if ($_POST['hidden_action'] == "searchName")
	{	
		$barcode = $_POST["input_result_barcode_matnr"];
		if (strlen($barcode) == 12) $barcode = "0" . $barcode;
		
		switch(strlen($barcode))
		{
			case 13:
				//Barcode
				$checksum = buildChecksum(substr($barcode, 0, 12));
			
				if ($checksum == substr($barcode, -1))
					$matnr = substr($barcode, 5, -1);
				else
					$problems = gettext("Bad barcode checksum");
				break;
			
			case 7:
				$matnr = $_POST["input_result_barcode_matnr"];
				break;
				
			default:
				$problems = gettext("Invalid matriculation number format");
				break;
		}

		if (empty($problems))
		{
			$eoDatabase = exam_organization_database::getInstance();
			$imt_login = $eoDatabase->getLogin($matnr);
			$data = $eoDatabase->getParticipantData($imt_login);
			
			if ($data)
			{
				$value_student = utf8_encode($data["forename"] . " " . $data["name"]);
				$value_matnr = $matnr;
				$js_focus = "document.getElementById('1').select();";
				$barcodeDisabled = 'disabled="true"';
			}
			else $problems = gettext("Matriculation Number not found");
		}
	}
	else if ($_POST['hidden_action'] == "savePoints")
	{
		$matnr = $_POST["hidden_matnr"];
		$eoDatabase = exam_organization_database::getInstance();
		$i=1;
		$points = 0.0;
		$isNT = isset($_POST['checkbox']);
		$assignments = intval($_POST['hidden_counter']);
		
		for ($i = 1 ; $i <= $assignments ; $i++)
		{
			$value = ($isNT) ? 0 : str_replace(",", ".", $_POST['input_exam_results_a'.$i]);
			$eoDatabase->setAssignmentResult($matnr, $i, $value, $examTerm);
			$points += floatval($value);
		}
		
		$NTvalue = ($isNT) ? $_POST['checkbox'] : "NULL";
		$eoDatabase->setNT($examTerm,$matnr, $NTvalue);
		$points = ($isNT) ? $_POST['checkbox'] : number_format($points,1) . " points";
		$points = str_replace(".", ",", $points);
		$ack = gettext("[points] saved for [student]");
		$ack = str_replace("[points]", $points, $ack);
		$ack = str_replace("[student]", $_POST['hidden_student'], $ack);
		$portal->set_confirmation($ack);
	}
}

if (!empty($problems)) $portal->set_problem_description( $problems );

//create site

//create portal
if (!isset($portal)) {
  $portal = lms_portal::get_instance();
  $portal->initialize( GUEST_ALLOWED );
} else $portal->set_guest_allowed( GUEST_ALLOWED );

//get the user object
$current_user = lms_steam::get_current_user();

if (!isset($course)) {echo "<br /> Course not set";exit();}

$html_handler = new koala_html_course( $course );
$html_handler->set_context( "exam_organization" ); //set context for context menu

$content = new HTML_TEMPLATE_IT();

$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization_input_exam_results.template.html" );

$container = $course->get_workroom();

$is_admin = $course->is_admin( $current_user );


if ( $is_admin ) {
	//clipboard:
	$koala_user = new koala_html_user( new koala_user( $current_user ) );
	//$clipboard_menu = $koala_user->get_clipboard_menu( $koala_container ); //error
	
	$content->setCurrentBlock( "BLOCK_CLIPBOARD" );
	//$content->setVariable( "CLIPBOARD_HTML", $clipboard_menu->get_html() );
	$content->parse( "BLOCK_CLIPBOARD" );
}

//get role
$content->setVariable("ROLE_STATUS", gettext("Your current role is student"));
if ($is_admin) $content->setVariable("ROLE_STATUS", gettext("Your current role is admin/staff member"));

if ($is_admin){
	$examChoosen = 0;
	if (isset($examTerm)){$examChoosen = $examTerm;}
	
	$content->setVariable("INFO_EXAM_NUMBER", "Viewing page for Exam term ".$examChoosen);
	if ($examChoosen == 0) $content->setVariable("INFO_EXAM_NUMBER", gettext("Please choose an exam term"));
	
	$numberOfAssignments = $course->get_attribute("EXAM".$examChoosen."_number_of_assignments");
	
	//create table
	$table = "<table>";
	
	$rows = ceil($numberOfAssignments / 20);
	
	// print table-header
	for ($r = 0 ; $r < $rows ; $r++)
	{
		$range = ($r == $rows - 1) ? $numberOfAssignments : ($r + 1) * 20;
		$table .= "<tr>";
		$table .= "<td>" . gettext("Assignment") . "</td>";
		
		for ($i = ($r * 20) + 1 ; $i <= $range ; $i++)
		{
			$table .= "<td align='center'>" . $i . "</td>";
		}
		
		if ($range == $numberOfAssignments && $r > 0)
		{
			$rest = 21 - ($range % 20);
			$table .= "<td colspan=\"" . $rest . "\"></td>";
		}
		
		$table .= "</tr><tr>";
		$table .= "<td>" . gettext("Points") . "</td>";
		
		for ($i = ($r * 20) + 1 ; $i <= $range ; $i++)
		{
			$value = (isset($reachedPoints[$i-1])) ? $reachedPoints[$i-1] : 0;
			$table .= "<td><input tabindex='" . $i . "' class='InputNormal' style='width: 20px;' type='text' onfocus='updateFocus(" . $i . ");' onkeyup='updatePoints(); checkInputOnKeyUp(this)' onblur='checkInput(this);' name='input_exam_results_a" . $i . "' id='" . $i . "' value='" . $value . "'/></td>";
		}
		
		if ($range == $numberOfAssignments && $r > 0)
		{
			$rest = 21 - ($range % 20);
			$table .= "<td colspan=\"" . $rest . "\"></td>";
		}
		
		if ($r < $rows - 1)
		{
			$table .= "<tr height='10'>";
			$table .= "<td colspan='21'></td>";
			$table .= "</tr>";
		}
	}

	$table .= "</tr>";
	$table .= "</table>";
	//end create table
	
	// get max points for each assignment
	$examObject = exam_organization_exam_object_data::getInstance($course);
	$assignments = $examObject->getAssignmentMaxPoints($examTerm);	
	$assignmentPoints = "";
	
	foreach ($assignments as $assignment)
	{	
		$assignmentPoints .= $assignment . ',';
	}
	
	$assignmentPoints = substr($assignmentPoints, 0, -1);
	
	$content->setVariable("ASSIGNMENT_ARRAY", $assignmentPoints);
	$content->setVariable("EXAM_INFORMATION", gettext("exam term:") . " " . $examChoosen);
	
	$content->setVariable("INFO_NAME",'Student');
	$content->setVariable("FIELD_NAME",'<b>' . $value_student . '</b>');
	
	$content->setVariable("INFO_BARCODE", gettext("Barcode / Matriculation number"));
	$content->setVariable("FIELD_BARCODE",'<input tabindex="1" type="text" onblur="submitOnBlur();" onfocus="updateFocus(-1);" ' . $barcodeDisabled . ' name="input_result_barcode_matnr" value="' . $value_matnr . '"/>');
	
	$content->setVariable("CHECKBOX_NT",'<input type="checkbox" onclick="updateTextfieldStatus(this.checked); updateCheckboxes(this);" name="checkbox" value="NT" id="checkbox_nt" ' . $NTchecked . ' /><label for="checkbox_nt">NT</label>');
	$content->setVariable("CHECKBOX_BV",'<input type="checkbox" onclick="updateTextfieldStatus(this.checked); updateCheckboxes(this);" name="checkbox" value="BV" id="checkbox_bv" ' . $BVchecked . ' /><label for="checkbox_bv">'.gettext('attempt to fraud').'</label>');
	$content->setVariable("CHECKBOX_SICK",'<input type="checkbox" onclick="updateTextfieldStatus(this.checked); updateCheckboxes(this);" name="checkbox" value="SICK" id="checkbox_sick" ' . $SICKchecked . ' /><label for="checkbox_sick">'.gettext('sick').'</label>');
	$content->setVariable("TOTAL_POINTS", "<b>" . gettext("total") . ": <span id='total_points'><b>" . $totalPoints . "</b></span>");
		
	$content->setVariable("BUTTON_SAVE",'<input type="submit" onmousedown="updateFocus(0);" name="input_exam_results_save" value="'.gettext("Save result and enter next").'"/>');
	$content->setVariable("BACK_LINK", "<a href=" . $course->get_url() . "exam_organization/>" . gettext( "back to exam organization" ) . "</a>" );		
	
	$content->setVariable("HIDDEN_ACTION",'<input type="hidden" name="hidden_action" value=""/>');
	$content->setVariable("HIDDEN_COUNTER",'<input type="hidden" name="hidden_counter" value="' . $numberOfAssignments . '"/>');
	$content->setVariable("HIDDEN_MATNR",'<input type="hidden" name="hidden_matnr" value="' . $value_matnr . '"/>');
	$content->setVariable("HIDDEN_STUDENT",'<input type="hidden" name="hidden_student" value="' . $value_student . '"/>');
	$content->setVariable("HIDDEN_FOCUS",'<input type="hidden" name="hidden_focus" value="1"/>');
	$content->setVariable("JS_SET_FOCUS", '<script type="text/javascript">' . $js_focus . '</script>');
	
	$content->setVariable("TABLE_INPUT_POINTS",$table);
}

$content->setVariable("VALUE_CONTAINER_DESC", gettext("Enter exam results"));
$content->setVariable("VALUE_CONTAINER_LONG_DESC", gettext("Enter exam results for a term"));
//$content->setCurrentBlock("BLOCK_INFO");
//$content->parse("BLOCK_INFO");

$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->show_html();

function buildChecksum($ean)
{
	$s = preg_replace("/([^\d])/", "", $ean);
	if (strlen($s) != 12) return false;

	$check = 0;
	for ($i = 0 ; $i < 12 ; $i++)
		$check += (($i % 2) * 2 + 1) * $s{$i};
	
	return (10 - ($check % 10)) % 10;
}
?>
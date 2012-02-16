<?php
/*
 * setup basic exam information
 * 
 * - basic information
 * - time and date
 * - exam key
 * - rooms
 * 
 * all set data is saved in the course object
 * 
 * @author Marcel Jakoblew
 */

//start of form
error_reporting(E_ALL);

$eoDatabase = exam_organization_database::getInstance();
$eoDatabase->connect((string) $course->get_name());
$examObject = exam_organization_exam_object_data::getInstance($course);


//create portal
if (!isset($portal)) {
  $portal = lms_portal::get_instance();
  $portal->initialize( GUEST_ALLOWED );
} else $portal->set_guest_allowed( GUEST_ALLOWED );

//get the user object
$current_user = lms_steam::get_current_user();

if (!isset($course)) {echo "<p> Course not set </p>";exit();}

$html_handler = new koala_html_course( $course );
$html_handler->set_context( "exam_organization" ); //set context for context menu

$content = new HTML_TEMPLATE_IT();

$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization_setup_exam_assignments.template.html");

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
$content->setVariable("ROLE_STATUS",gettext("Your current role is student"));
if ($is_admin) $content->setVariable("ROLE_STATUS",gettext("Your current role is admin/staff member"));


//evaluate exam term
$examChoosen = 0;
if (isset($examTerm) && $examTerm == 1 ){$examChoosen = 1;}
if (isset($examTerm) && $examTerm == 2 ){$examChoosen = 2;}
if (isset($examTerm) && $examTerm == 3 ){$examChoosen = 3;}
$content->setVariable("INFO_EXAM_NUMBER",gettext("Viewing page for Exam term")." ".$examChoosen);
if ($examChoosen == 0) $content->setVariable("INFO_EXAM_NUMBER",gettext("Please choose an exam term"));

if (isset($_POST['hidden_action']))
{
	$problems = "";

	if ($_POST['hidden_action'] == "savePoints")
	{
		//call the method to save data to the course object
		//$returnValue = saveDataToServer($_POST,$examChoosen, $course);
		$examObject->setAssignmentMaxPoints($examTerm,$_POST);
		$examObject->setNumberOfAssignments($examTerm,$_POST["number_of_assignments"]);
		$returnValue = True;

		if($returnValue){
			//show confirmation
			$portal->set_confirmation(gettext("Saved changes on assignments"));
			$examObject->setStatus($examTerm,"assignments",TRUE);
			$_SESSION["examorganization_show_confirmation"]="Saved changes on assignments";
			//header("Location: ".$course->get_URL()."exam_organization/");
		} else {
			//show error
			$portal->set_problem_description(gettext("Error while saving assignment changes"));
			$examObject->setStatus($examTerm,"assignments",FALSE);
			$_SESSION["examorganization_show_problem_description"]="Error while saving assignment changes";
			//header("Location: ".$course->get_URL()."exam_organization/");
		}
	}
}

if (!$is_admin){
	$content->setVariable("INFO",gettext("This page is for staff only"));
}

if ($is_admin && $examChoosen!=0){
	//choose an exam term
	
	//get the data for the form from the course object or the database

	//assignments (number of)
	$content->setVariable("INFO_ASSIGNMENTS",gettext('Set the max points for the assignments'));
	$content->setVariable("INFO_ASSIGNMENTS_NUMBER",gettext('add or remove new assignments'));
	$content->setVariable("BUTTON_ADD", "<input type='button' value='+' onClick='addAssignment();'/>");
	$content->setVariable("BUTTON_REMOVE", "<input type='button' value='-' onClick='removeAssignment();'/>" );
	
	//assignments (points per assignment)
	$numberOfAssignments = (int) $examObject->getNumberOfAssignments($examTerm);
	
	$content->setVariable("INFO_ASSIGNMENTS_MAXPOINTS",gettext("the the max points for each assignment"));
	$content->setCurrentBlock( "BLOCK_ASSIGNMENTS" );
	
	//create table
	$assignments = $examObject->getAssignmentMaxPoints($examTerm);
	$points = 0;
	$table = "<table id='AssignmentTable'>";
	
	$rows = ceil($numberOfAssignments / 15);
	
	// print table-header
	for ($r = 0 ; $r < $rows ; $r++)
	{
		$range = ($r == $rows - 1) ? $numberOfAssignments : ($r + 1) * 15;
		$table .= "<tr id='" . ($r + 1) . ":1'>";
		$table .= "<td>" . gettext("Assignment") . "</td>";
		
		for ($i = ($r * 15) + 1 ; $i <= $range ; $i++)
		{
			$table .= "<td align='center'><b>" . $i . "</b></td>";
		}
		
		$table .= "</tr><tr id='" . ($r + 1) . ":2'>";
		$table .= "<td>" . gettext("Points") . "</td>";
		
		for ($i = ($r * 15) + 1 ; $i <= $range ; $i++)
		{
			$table .= "<td><input tabindex='" . $i . "' class='InputNormal' style='width: 30px;' type='text' onfocus='updateFocus(" . $i . ");' onkeyup='updatePoints(); checkInputOnKeyUp(this)' onblur='checkInput(this);' name='field_assignment_max_points_" . $i . "' id='" . $i . "' value='" . $assignments[$i] . "'/></td>";
			$points += $assignments[$i];
		}
		
		if ($r < $rows - 1)
		{
			$table .= "<tr id='" . ($r + 1) . ":1' height='10'>";
			$table .= "<td colspan='21'></td>";
			$table .= "</tr>";
		}
	}

	$table .= "</tr>";
	$table .= "</table>";
	//end create table
	
	$content->setVariable("ASSIGNMENT_TABLE",$table);
	$content->setVariable("TABLE_ROWDESCRIPTON_NOTE",gettext("Note"));
	$content->setVariable("TABLE_ROWDESCRIPTON_MAXPOINTS",gettext("Maxpoints"));
	$content->parse( "BLOCK_ASSIGNMENTS" );
	
	$content->setVariable("TOTAL_POINTS", "<b>" . gettext("total") . ": <span id='total_points'><b>" . $points . "</b></span>");
	
	//save button to save all
	$content->setVariable("BUTTON_SAVE",'<input type="submit" onmousedown="updateFocus(0);" name="save_setup_exam" value="'.gettext('Save changes').'"/>');
	$content->setVariable("BACK_LINK", "<a href=" . $course->get_url() . "exam_organization/>" . gettext( "back to exam organization" ) . "</a>" );
	
	$content->setVariable("HIDDEN_ACTION",'<input type="hidden" name="hidden_action" value=""/>');
	$content->setVariable("HIDDEN_FOCUS",'<input type="hidden" name="hidden_focus" value="1"/>');
	$content->setVariable("NUMBER_OF_ASSIGNMENTS",'<input type="hidden" name="number_of_assignments" value="' . $numberOfAssignments . '"/>');
	$content->setVariable("LAST_ROW", $rows);
}

$content->setVariable("VALUE_CONTAINER_DESC",gettext("Exam organization"));
$content->setVariable("VALUE_CONTAINER_LONG_DESC",gettext("Setup page for an exam"));

$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->show_html();

exit(0);
?>
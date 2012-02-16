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
$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization_setup_exam_date_time.template.html");

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
	
	
//call the method to save data to the course object	
if (isset($_POST["save_setup_exam"])){
	$returnValue1 = $examObject->setDate($examTerm,$_POST["field_exam_date_day"],$_POST["field_exam_date_month"],$_POST["field_exam_date_year"]);
	$returnValue2 = $examObject->setTime($examTerm,$_POST["field_exam_time_start_minute"],$_POST["field_exam_time_start_hour"],$_POST["field_exam_time_end_minute"],$_POST["field_exam_time_end_hour"]);
	
	$returnValue = $returnValue1 && $returnValue2;
	
	if($returnValue){
		//show confirmation
		$portal->set_confirmation(gettext("Saved date and time changes"));
		$examObject->setStatus($examTerm,"date",TRUE);
		$examObject->setStatus($examTerm,"time",TRUE);
		$_SESSION["examorganization_show_confirmation"]=gettext("Saved date and time changes");
		header("Location: ".$course->get_URL()."exam_organization/");
	} else {
		//show error
		$portal->set_problem_description(gettext("Error while saving date and time changes"));
		$examObject->setStatus($examTerm,"date",FALSE);
		$examObject->setStatus($examTerm,"time",FALSE);
		$_SESSION["examorganization_show_problem_description"]=gettext("Error while saving date and time changes");
		header("Location: ".$course->get_URL()."exam_organization/");
	}
}
	
	
if (!$is_admin){
	$content->setVariable("INFO",gettext("This page is for staff only"));
}

if ($is_admin && $examChoosen!=0){
	//start of form
	
	//date
	$content->setVariable("INFO_EXAM_DATE",gettext('Date of the exam (day, month, year)'));
	$content->setVariable("FIELD_EXAM_DATE_DAY",'<input class="InputNormal" style="width: 20px;" type="text" name="field_exam_date_day" onblur="checkInput(this)" onkeyup="checkInputOnKeyUp(this)" value="'.$examObject->getDateDay($examTerm).'"/>');
	$content->setVariable("FIELD_EXAM_DATE_MONTH",'<input class="InputNormal" style="width: 20px;" type="text" name="field_exam_date_month" onblur="checkInput(this)" onkeyup="checkInputOnKeyUp(this)" value="'.$examObject->getDateMonth($examTerm).'"/>');
	$content->setVariable("FIELD_EXAM_DATE_YEAR",'<input class="InputNormal" style="width: 40px;" type="text" name="field_exam_date_year" onblur="checkInput(this)" onkeyup="checkInputOnKeyUp(this)" value="'.$examObject->getDateYear($examTerm).'"/>');
	
	//time
	$content->setVariable("INFO_EXAM_TIME",gettext('Time of the exam (begin-hour, begin-minute, end-hour, end-minute)'));
	$content->setVariable("FIELD_EXAM_TIME_START_MINUTE",'<input class="InputNormal" style="width: 30px;" type="text" name="field_exam_time_start_minute" onblur="checkInput(this)" onkeyup="checkInputOnKeyUp(this)" value="'.$examObject->getTimeStartMinute($examTerm).'"/>');
	$content->setVariable("FIELD_EXAM_TIME_START_HOUR",'<input class="InputNormal" style="width: 30px;" type="text" name="field_exam_time_start_hour" onblur="checkInput(this)" onkeyup="checkInputOnKeyUp(this)" value="'.$examObject->getTimeStartHour($examTerm).'"/>');
	$content->setVariable("FIELD_EXAM_TIME_END_MINUTE",'<input class="InputNormal" style="width: 30px;" type="text" name="field_exam_time_end_minute" onblur="checkInput(this)" onkeyup="checkInputOnKeyUp(this)" value="'.$examObject->getTimeEndMinute($examTerm).'"/>');
	$content->setVariable("FIELD_EXAM_TIME_END_HOUR",'<input class="InputNormal" style="width: 30px;" type="text" name="field_exam_time_end_hour" onblur="checkInput(this)" onkeyup="checkInputOnKeyUp(this)" value="'.$examObject->getTimeEndHour($examTerm).'"/>');
	$content->setVariable("UNTIL",gettext("until"));
		
	//save button to save all
	$content->setVariable("BUTTON_SAVE",'<input type="submit" name="save_setup_exam" value="'.gettext('Save changes').'"/>');
	$content->setVariable("BACK_LINK", "<a href=" . $course->get_url() . "exam_organization/>" . gettext( "back to exam organization" ) . "</a>" );
}

$content->setVariable("VALUE_CONTAINER_DESC",gettext("Exam organization"));
$content->setVariable("VALUE_CONTAINER_LONG_DESC",gettext("Setup page for an exam"));

$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->show_html();

exit(0);
?>
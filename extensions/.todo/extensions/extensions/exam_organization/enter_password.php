<?php
/*
 * save and edit password
 * 
 * @author Marcel Jakoblew
 */

$eoDatabase = exam_organization_database::getInstance();
$eoDatabase->connect((string) $course->get_name());
$examObject = exam_organization_exam_object_data::getInstance($course);

//start of form

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
$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization_enter_password.template.html");

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

//POST DATA
if (isset($_POST["save_password"])){
	$password = $_POST["password"];
	$_SESSION["password"] = $password;
	//$_SESSION["examorganization_show_problem_description"]=gettext("Old password wrong");
	header("Location: ".$course->get_URL()."exam_organization/");
}

	
if (!$is_admin){
	$content->setVariable("INFO",gettext("This page is for staff only"));
}

if ($is_admin && $examChoosen!=0){
	//start of form
	
	//freetext
	$content->setVariable("INFO_EXAM_PASSWORD",gettext('Password'));
	$content->setVariable("INFO_EXAM_PASSWORD_DETAILS",gettext('Please enter the password for the exam organization.'));
	
	$oldFreeText = $examObject->getFreeText($examTerm);
	$content->setVariable("INFO_EXAM_PASSWORD",gettext("Password"));
	$content->setVariable("FIELD_EXAM_PASSWORD",'<input type="password" name="password" value=""> </input>');
		
	//save button to save all
	$content->setVariable("BUTTON_SAVE",'<input type="submit" name="save_password" value="'.gettext('Authenticate for the exam organization').'"/>');
	//$content->setVariable("BACK_LINK", "<a href=\"" . PATH_URL . SEMESTER_URL . "/" . $course->get_semester()->get_name() . "/" . $course->get_course_id() . "/exam_organization/\">" . gettext( "back to exam organization" ) . "</a>" );
}

$content->setVariable("VALUE_CONTAINER_DESC",gettext("Exam organization"));
$content->setVariable("VALUE_CONTAINER_LONG_DESC",gettext("Setup page for an exam"));

$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->show_html();

exit(0);
?>
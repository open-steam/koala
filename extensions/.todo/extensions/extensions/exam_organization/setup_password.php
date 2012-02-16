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
$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization_setup_password.template.html");

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
	//$examObject->clearMasterPassword(); //for testing only
	
	$passwordOld = $_POST["password_old"];
	$passwordNew1 = $_POST["password_new1"];
	$passwordNew2 = $_POST["password_new2"];
	
	//check old password
	if($examObject->checkMasterPassword($passwordOld)){
		if($passwordNew1==$passwordNew2){
			$returnValue = $examObject->setMasterPassword($passwordNew1);
			if($returnValue){
				$_SESSION["examorganization_show_confirmation"]=gettext("Password saved");
				header("Location: ".$course->get_URL()."exam_organization/");
			} else {
				//unknow error
				$_SESSION["examorganization_show_problem_description"]=gettext("Error while saving password");
				header("Location: ".$course->get_URL()."exam_organization/password");
			}
		} else {
			$_SESSION["examorganization_show_problem_description"]=gettext("Entered and retyped password not equal");
			header("Location: ".$course->get_URL()."exam_organization/password");
		}
	} else {
		$_SESSION["examorganization_show_problem_description"]=gettext("Old password wrong");
		header("Location: ".$course->get_URL()."exam_organization/password");
	}
	
}

	
if (!$is_admin){
	$content->setVariable("INFO",gettext("This page is for staff only"));
}

if ($is_admin && $examChoosen!=0){
	//start of form
	
	//freetext
	$content->setVariable("INFO_EXAM_PASSWORD",gettext('Password'));
	$content->setVariable("INFO_EXAM_PASSWORD_DETAILS",gettext('Please set or change the password for the exam organization.'));
	
	$oldFreeText = $examObject->getFreeText($examTerm);
	$content->setVariable("INFO_EXAM_PASSWORD_OLD",gettext("Old password"));
	$content->setVariable("INFO_EXAM_PASSWORD_NEW1",gettext("New password"));
	$content->setVariable("INFO_EXAM_PASSWORD_NEW2",gettext("Retype new password"));
	
	$content->setVariable("FIELD_EXAM_PASSWORD_OLD",'<input type="password" name="password_old" value=""> </input>');
	$content->setVariable("FIELD_EXAM_PASSWORD_NEW1",'<input type="password" name="password_new1" value=""> </input>');
	$content->setVariable("FIELD_EXAM_PASSWORD_NEW2",'<input type="password" name="password_new2" value=""> </input>');
		
	//save button to save all
	$content->setVariable("BUTTON_SAVE",'<input type="submit" name="save_password" value="'.gettext('Save changes').'"/>');
	$content->setVariable("BACK_LINK", "<a href=" . $course->get_url() . "exam_organization/>" . gettext( "back to exam organization" ) . "</a>" );
}

$content->setVariable("VALUE_CONTAINER_DESC",gettext("Exam organization"));
$content->setVariable("VALUE_CONTAINER_LONG_DESC",gettext("Setup page for an exam"));

$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->show_html();

exit(0);
?>
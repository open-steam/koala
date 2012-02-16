<?php
/*
 * roundmail
 * 
 * @author Marcel Jakoblew
 */

function sendRoundMail($sendingUser, $courseObject, $courseId, $term, $subject="", $body=""){
	//get recipeints
	$recipients=array();
	
	$eoDatabase = exam_organization_database::getInstance();
	$participants = $eoDatabase->getParticipantsForTerm($term);
	
	//prefetch mail addresses
	$ldapManager = exam_organization_ldap_manager::getInstance();
	foreach ($participants as $participant){
		$ldapManager->markForPreload($participant["matriculationNumber"]);
	}
	$ldapManager->preload();
	
	$header  = 'MIME-Version: 1.0' . "\r\n";
	
	if($sendingUser->get_full_name()=="Root User"){
		$lecturerName = "Root User";
		$lecturerMail = "elearning@upb.de";
	} else {
		$lecturerMailArray = $sendingUser->get_email_forwarding(); 
		$lecturerMail = $lecturerMailArray[1];
		$lecturerName = $sendingUser->get_full_name();
	}
	
	$header .= "Content-type: text/html; charset=utf-8\r\n"; 
	$header .= 'From: '.$lecturerName.' <'.$lecturerMail.'>' . "\r\n";
	
	//get mail addresses
	foreach ($participants as $participant){
		$mailAdress = $ldapManager->matriculationNumber2mail($participant["matriculationNumber"]);
		//$recipients[]=$mailAdress;
		$header .= "Bcc: ".$mailAdress."\r\n";
	}
	$header .= "Bcc: ".$lecturerMail."\r\n"; //send mail to author
	
	//create recipients string
	$recipientsString = "";
	$first = true;
	foreach ($recipients as $recipient){
		if($first){
			$first = FALSE;
			$recipientsString.=$recipient;
		} else {
			$recipientsString.=", ".$recipient;
		}
	}
	//get staff members subgroup to send mail
	$staffGroup = $courseObject->get_group_staff();
	
	//send mail
	$sendingUser->mail(gettext("Circular").": ".$subject,$body);
	$staffGroup->mail(gettext("Circular").": ".$subject,$body);
	$returnValue = mail($recipientsString, '=?UTF-8?B?'.base64_encode(gettext("Circular").": ".$subject).'?=', $body, $header);
	return returnValue;
	
}


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
$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization_mail.template.html");

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
$content->setVariable("INFO_EXAM_NUMBER",gettext("Exam term")." ".$examChoosen);
if ($examChoosen == 0) $content->setVariable("INFO_EXAM_NUMBER",gettext("Please choose an exam term"));


//POST DATA
if (isset($_POST["send_mail"]) && isset($_POST["mail_body"])){
	$freetext = $_POST["mail_body"];
	$returnValue = sendRoundMail($current_user, $course, $course->get_name(), $examTerm, $_POST["mail_subject"], $_POST["mail_body"]);
	if($returnValue){
		$portal->set_confirmation(gettext("The mail has been sent"));
		$_SESSION["examorganization_show_confirmation"]=gettext("The mail has been sent");
		header("Location: ".$course->get_URL()."exam_organization/");
	} else {
		$portal->set_problem_description(gettext("The mail was not sent"));
		$_SESSION["examorganization_show_problem_description"]=gettext("The mail was not sent");
		header("Location: ".$course->get_URL()."exam_organization/");
	}
}

	
if (!$is_admin){
	$content->setVariable("INFO",gettext("This page is for staff only"));
}

if ($is_admin && $examChoosen!=0){
	//start of form
	
	$content->setVariable("INFO_EXAM_MAIL",gettext('Mail to participants'));
	$content->setVariable("INFO_EXAM_MAIL_RECIPIENTS",gettext('The mail will be sent to all students participating in this exam term and all staff members including yourself.'));
	
	$content->setVariable("INFO_EXAM_MAIL_SUBJECT",gettext("Subject"));
	$content->setVariable("INFO_EXAM_MAIL_BODY",gettext("Content"));
	$content->setVariable("FIELD_EXAM_MAIL_SUBJECT",'<input style="width:370px;" name="mail_subject" value="" cols="50" rows="10">'.'</input>');
	$content->setVariable("FIELD_EXAM_MAIL_BODY",'<textarea name="mail_body" value="" cols="50" rows="10">'.'</textarea>');
		
	//save button to save all
	$content->setVariable("BUTTON_SAVE",'<input type="submit" name="send_mail" value="'.gettext('Send mail').'"/>');
	$content->setVariable("BACK_LINK", "<a href=" . $course->get_url() . "exam_organization/>" . gettext( "back to exam organization" ) . "</a>" );
}

$content->setVariable("VALUE_CONTAINER_DESC",gettext("Exam organization"));
$content->setVariable("VALUE_CONTAINER_LONG_DESC",gettext("Setup page for an exam"));

$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->show_html();

exit(0);
?>
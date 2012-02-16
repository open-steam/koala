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

$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization_setup_exam_room.template.html");

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
	$returnValue = $examObject->setRooms($examTerm,$_POST);
	
	if($returnValue){
		//show confirmation
		$portal->set_confirmation(gettext("Saved room changes"));
		$_SESSION["examorganization_show_confirmation"]=gettext("Saved room changes");
		$examObject->setStatus($examTerm,"room",TRUE);
		$examObject->setStatus($examTerm,"places",FALSE);
		header("Location: ".$course->get_URL()."exam_organization/");
	} else {
		//show error
		$portal->set_problem_description(gettext("Error while saving room changes"));
		$_SESSION["examorganization_show_problem_description"]=gettext("Error while saving room changes");
		$examObject->setStatus($examTerm,"room",FALSE);
		$examObject->setStatus($examTerm,"places",FALSE);
		header("Location: ".$course->get_URL()."exam_organization/");
	}
}
	
	
if (!$is_admin){
	$content->setVariable("INFO",gettext("This page is for staff only"));
}

if ($is_admin && $examChoosen!=0){
	//choose an exam term
	
	//get the data for the form from the course object or the database
	
	//----------- load data from server
	$availableRooms = $eoDatabase->getRoomList();
	
	
	
	//rooms
	$content->setVariable("INFO_ROOMS",gettext('Choose the rooms').":");
		
	foreach ($availableRooms as $room){
		//do some stuff for each room
		$content->setCurrentBlock( "BLOCK_ROOMS" );
		$roomsArray = $examObject->getRooms($examTerm);
		if($room=="other"){
			$content->setVariable("ROOM","<input name='checkbox_room_other"."' type='checkbox' onclick='check();' ".$roomsArray["checkbox_room_other"]."/>" . gettext("Individual"). " (".gettext("automatic place selection disabled").")");
		} else {
			$content->setVariable("ROOM","<input name='checkbox_room_".$room."' type='checkbox' ".$roomsArray["checkbox_room_".$room]."/>" . $room . " (".$eoDatabase->getNumberOfPlaces($room)." ".gettext("places").")");
		}
		$content->parse( "BLOCK_ROOMS" );
	}
		
	//save button to save all
	$content->setVariable("BUTTON_SAVE",'<input type="submit" name="save_setup_exam" value="'.gettext('Save changes').'"/>');
	$content->setVariable("BACK_LINK", "<a href=" . $course->get_url() . "exam_organization/>" . gettext( "back to exam organization" ) . "</a>" );
}

$content->setVariable("INFO_OTHER_ROOMS",gettext('The checkbox "Individual" deactivates the function "Set seats" on the main page. Seats have to be set now manually on the participants overview.'));
$content->setVariable("INFO_NO_PAUL_SYNC",gettext('This function is not synchronized with the room planning of the university and the PAUL system. You have to ensure youself, that the choosen rooms are available for the desired term.'));
$content->setVariable("VALUE_CONTAINER_DESC",gettext("Exam organization"));
$content->setVariable("VALUE_CONTAINER_LONG_DESC",gettext("Setup page for an exam"));

$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->show_html();

exit(0);
?>
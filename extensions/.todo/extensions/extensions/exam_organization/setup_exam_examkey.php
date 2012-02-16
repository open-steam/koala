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

$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization_setup_exam_examkey.template.html");

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
	$examObject = exam_organization_exam_object_data::getInstance($course);
	$retValA = $examObject->setExamKey($examTerm,"10",$_POST["field_exam_key_10"]);
	$retValB = $examObject->setExamKey($examTerm,"13",$_POST["field_exam_key_13"]);
	$retValC = $examObject->setExamKey($examTerm,"17",$_POST["field_exam_key_17"]);
	$retValD = $examObject->setExamKey($examTerm,"20",$_POST["field_exam_key_20"]);
	$retValE = $examObject->setExamKey($examTerm,"23",$_POST["field_exam_key_23"]);
	$retValF = $examObject->setExamKey($examTerm,"27",$_POST["field_exam_key_27"]);
	$retValG = $examObject->setExamKey($examTerm,"30",$_POST["field_exam_key_30"]);
	$retValH = $examObject->setExamKey($examTerm,"33",$_POST["field_exam_key_33"]);
	$retValI = $examObject->setExamKey($examTerm,"37",$_POST["field_exam_key_37"]);
	$retValJ = $examObject->setExamKey($examTerm,"40",$_POST["field_exam_key_40"]);
	
	$returnValue = $retValA && $retValB && $retValC && $retValD && $retValE && $retValF && $retValG && $retValH && $retValI && $retValJ;
	
	if($returnValue){
		//show confirmation
		$portal->set_confirmation(gettext("Saved examkey changes"));
		$examObject->setStatus($examTerm,"examkey",TRUE);
		$_SESSION["examorganization_show_confirmation"]="Saved examkey changes";
		header("Location: ".$course->get_URL()."exam_organization/");
	} else {
		//show error
		$portal->set_problem_description(gettext("Error while saving examkey changes"));
		$examObject->setStatus($examTerm,"examkey",FALSE);
		$_SESSION["examorganization_show_problem_description"]="Error while saving examkey changes";
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
	//$formDataArray = loadDataFromServer($examChoosen, $course);	
	$examObject = exam_organization_exam_object_data::getInstance($course);
	$examKey = $examObject->getExamKey($examTerm);
	//$assignments = $formDataArray["assignments"];
	
	$examKeyPoints10 = $examKey["10"];
	$examKeyPoints13 = $examKey["13"];
	$examKeyPoints17 = $examKey["17"];
	$examKeyPoints20 = $examKey["20"];
	$examKeyPoints23 = $examKey["23"];
	$examKeyPoints27 = $examKey["27"];
	$examKeyPoints30 = $examKey["30"];
	$examKeyPoints33 = $examKey["33"];
	$examKeyPoints37 = $examKey["37"];
	$examKeyPoints40 = $examKey["40"];
	$examKeyMaxPoints = $examObject->getExamKeyMaxPoints($examTerm);
	
	
	//start of form -----------

		
	//exam key
	$content->setVariable("INFO_EXAMKEY",gettext('Exam key'));
	$content->setVariable("INFO_10",'1.0');
	$content->setVariable("INFO_13",'1.3');
	$content->setVariable("INFO_17",'1.7');
	$content->setVariable("INFO_20",'2.0');
	$content->setVariable("INFO_23",'2.3');
	$content->setVariable("INFO_27",'2.7');
	$content->setVariable("INFO_30",'3.0');
	$content->setVariable("INFO_33",'3.3');
	$content->setVariable("INFO_37",'3.7');
	$content->setVariable("INFO_40",'4.0');
	
	$content->setVariable("INFO_MAXPOINTS",gettext('The maximum number of points is'));
	$content->setVariable("FIELD_MAXPOINTS",'<span id="max_points">' . $examKeyMaxPoints . '</span>');
	
	$content->setVariable("FIELD_10",'<input class="InputNormal" style="width: 30px;" type="text" onblur="checkInput(this);" onkeyup="checkInputOnKeyUp(this);" name="field_exam_key_10" value="' . $examKeyPoints10 . '"/>');
	$content->setVariable("FIELD_13",'<input class="InputNormal" style="width: 30px;" type="text" onblur="checkInput(this);" onkeyup="checkInputOnKeyUp(this);" name="field_exam_key_13" value="' . $examKeyPoints13 . '"/>');
	$content->setVariable("FIELD_17",'<input class="InputNormal" style="width: 30px;" type="text" onblur="checkInput(this);" onkeyup="checkInputOnKeyUp(this);" name="field_exam_key_17" value="' . $examKeyPoints17 . '"/>');
	$content->setVariable("FIELD_20",'<input class="InputNormal" style="width: 30px;" type="text" onblur="checkInput(this);" onkeyup="checkInputOnKeyUp(this);" name="field_exam_key_20" value="' . $examKeyPoints20 . '"/>');
	$content->setVariable("FIELD_23",'<input class="InputNormal" style="width: 30px;" type="text" onblur="checkInput(this);" onkeyup="checkInputOnKeyUp(this);" name="field_exam_key_23" value="' . $examKeyPoints23 . '"/>');
	$content->setVariable("FIELD_27",'<input class="InputNormal" style="width: 30px;" type="text" onblur="checkInput(this);" onkeyup="checkInputOnKeyUp(this);" name="field_exam_key_27" value="' . $examKeyPoints27 . '"/>');
	$content->setVariable("FIELD_30",'<input class="InputNormal" style="width: 30px;" type="text" onblur="checkInput(this);" onkeyup="checkInputOnKeyUp(this);" name="field_exam_key_30" value="' . $examKeyPoints30 . '"/>');
	$content->setVariable("FIELD_33",'<input class="InputNormal" style="width: 30px;" type="text" onblur="checkInput(this);" onkeyup="checkInputOnKeyUp(this);" name="field_exam_key_33" value="' . $examKeyPoints33 . '"/>');
	$content->setVariable("FIELD_37",'<input class="InputNormal" style="width: 30px;" type="text" onblur="checkInput(this);" onkeyup="checkInputOnKeyUp(this);" name="field_exam_key_37" value="' . $examKeyPoints37 . '"/>');
	$content->setVariable("FIELD_40",'<input class="InputNormal" style="width: 30px;" type="text" onblur="checkInput(this);" onkeyup="checkInputOnKeyUp(this);" name="field_exam_key_40" value="' . $examKeyPoints40 . '"/>');
	
	//rooms
	$content->setVariable("INFO_ROOMS",gettext('Choose the rooms'));
	
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
<?php
/*
 * main page
 * overview page for students, course admins and staff
 * 
 * @author Marcel Jakoblew
 */

include_once("initialize_portal.php");


//get the user object
$current_user = lms_steam::get_current_user();

if (!isset($course)) {echo "Course not set";exit();}

$html_handler = new koala_html_course( $course );
$html_handler->set_context( "exam_organization" ); //set context for context menu

$content = new HTML_TEMPLATE_IT();

$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization.template.html" );

$container = $course->get_workroom();

$is_admin = $course->is_admin( $current_user );


if ( $is_admin ) {
	//clipboard:
	$koala_user = new koala_html_user( new koala_user( $current_user ) );
	//$clipboard_menu = $koala_user->get_clipboard_menu( $koala_container ); //error
	
	$content->setCurrentBlock( "BLOCK_CLIPBOARD" );
	//$content->setVariable( "CLIPBOARD_HTML", $clipboard_menu->get_html() );
	$content->parse( "BLOCK_CLIPBOARD" );
} else {
	$koala_user = new koala_html_user( new koala_user( $current_user ) );
	$user = lms_steam::get_current_user();
	$koalaUserLogin = $user->get_name();
}


//get role
$content->setVariable("ROLE_STATUS",gettext("Your current role is student"));
if ($is_admin) $content->setVariable("ROLE_STATUS",gettext("Your current role is admin/staff member"));

if (!$is_admin){
	//get data from server
	$examObject = exam_organization_exam_object_data::getInstance($course);
	$exam1VisibleDate = $examObject->getDateAndTimeVisibleStatus("1");
	$exam2VisibleDate = $examObject->getDateAndTimeVisibleStatus("2");
	$exam3VisibleDate = $examObject->getDateAndTimeVisibleStatus("3");
	$exam1VisiblePlaces = $examObject->getRoomsVisibleStatus("1");
	$exam2VisiblePlaces = $examObject->getRoomsVisibleStatus("2");
	$exam3VisiblePlaces = $examObject->getRoomsVisibleStatus("3");
}

//participants view
$nothingToShow = TRUE;
if (!$is_admin){
	if ( ($exam1VisibleDate || $exam1VisiblePlaces) && $eoDatabase->isParticipantForTerm(1,$koalaUserLogin) ){
		
		$content->setVariable("EXAM_1_DESC",gettext("Information on exam 1"));
		$content->setVariable("LABEL_EXAM_1_REGISTRATION_STATUS", gettext("Status:"));		
		$content->setVariable("VALUE_EXAM_1_REGISTRATION_STATUS", gettext("You are registered for exam") . " 1.");

		if ($exam1VisibleDate)
		{
			$examDateDay = $course->get_attribute("EXAM1_exam_date_day");
			$examDateMonth = $course->get_attribute("EXAM1_exam_date_month");
			$examDateYear = $course->get_attribute("EXAM1_exam_date_year");
			
			$examTimeStartHour = $course->get_attribute("EXAM1_exam_time_start_hour");
			$examTimeStartMinute = $course->get_attribute("EXAM1_exam_time_start_minute");
			$examTimeEndHour = $course->get_attribute("EXAM1_exam_time_end_hour");
			$examTimeEndMinute = $course->get_attribute("EXAM1_exam_time_end_minute");

			$content->setCurrentBlock("DATE_TIME_EXAM1");
			$content->setVariable("LABEL_EXAM_1_DATE", gettext("Date:"));
			$content->setVariable("LABEL_EXAM_1_TIME", gettext("Time:"));
			$content->setVariable("VALUE_EXAM_1_DATE", sprintf("%02d.%02d.%04d", $examDateDay, $examDateMonth, $examDateYear));
			$content->setVariable("VALUE_EXAM_1_TIME", "$examTimeStartHour:$examTimeStartMinute " . gettext("til") . " $examTimeEndHour:$examTimeEndMinute " . gettext("o'clock"));
			$content->parseCurrentBlock();
		}
		
		$koalaUserRoomAndPlace = $eoDatabase->getRoomAndPlace(1, $koalaUserLogin);
		$room = $koalaUserRoomAndPlace["room"];
		$place = $koalaUserRoomAndPlace["place"];
		if ($room=="not set") $room = gettext("not set");
		if ($place=="not set") $place = gettext("not set");
		
		if ($exam1VisiblePlaces)
		{
			$content->setCurrentBlock("ROOM_PLACE_EXAM1");
			$content->setVariable("LABEL_EXAM_1_ROOM", gettext("Room:"));
			$content->setVariable("LABEL_EXAM_1_PLACE", gettext("Place:"));			
			$content->setVariable("VALUE_EXAM_1_ROOM", $room);
			$content->setVariable("VALUE_EXAM_1_PLACE", $place);
			$content->parseCurrentBlock();
		}
		
		$examObject = exam_organization_exam_object_data::getInstance($course);
		$freetext = $examObject->getFreetext(1);
		
		if ($freetext != "")
		{
			$content->setCurrentBlock("FREETEXT_EXAM1");
			$content->setVariable("LABEL_EXAM_1_FREETEXT", gettext("Note:"));		
			$content->setVariable("VALUE_EXAM_1_FREETEXT", $freetext);
			$content->parseCurrentBlock();
		}
		
		$nothingToShow = FALSE;
	}
	
	if ( ($exam2VisibleDate || $exam2VisiblePlaces) && $eoDatabase->isParticipantForTerm(2,$koalaUserLogin) ){
		
		$content->setVariable("EXAM_2_DESC",gettext("Information on exam 2"));
		$content->setVariable("LABEL_EXAM_2_REGISTRATION_STATUS", gettext("Status:"));		
		$content->setVariable("VALUE_EXAM_2_REGISTRATION_STATUS", gettext("You are registered for exam") . " 2.");

		if ($exam2VisibleDate)
		{
			$examDateDay = $course->get_attribute("EXAM2_exam_date_day");
			$examDateMonth = $course->get_attribute("EXAM2_exam_date_month");
			$examDateYear = $course->get_attribute("EXAM2_exam_date_year");
			
			$examTimeStartHour = $course->get_attribute("EXAM2_exam_time_start_hour");
			$examTimeStartMinute = $course->get_attribute("EXAM2_exam_time_start_minute");
			$examTimeEndHour = $course->get_attribute("EXAM2_exam_time_end_hour");
			$examTimeEndMinute = $course->get_attribute("EXAM2_exam_time_end_minute");

			$content->setCurrentBlock("DATE_TIME_EXAM2");
			$content->setVariable("LABEL_EXAM_2_DATE", gettext("Date:"));
			$content->setVariable("LABEL_EXAM_2_TIME", gettext("Time:"));
			$content->setVariable("VALUE_EXAM_2_DATE", sprintf("%02d.%02d.%04d", $examDateDay, $examDateMonth, $examDateYear));
			$content->setVariable("VALUE_EXAM_2_TIME", "$examTimeStartHour:$examTimeStartMinute " . gettext("til") . " $examTimeEndHour:$examTimeEndMinute " . gettext("o'clock"));
			$content->parseCurrentBlock();
		}
		
		$koalaUserRoomAndPlace = $eoDatabase->getRoomAndPlace(2, $koalaUserLogin);
		$room = $koalaUserRoomAndPlace["room"];
		$place = $koalaUserRoomAndPlace["place"];
		if ($room=="not set") $room = gettext("not set");
		if ($place=="not set") $place = gettext("not set");
		
		if ($exam2VisiblePlaces)
		{
			$content->setCurrentBlock("ROOM_PLACE_EXAM2");
			$content->setVariable("LABEL_EXAM_2_ROOM", gettext("Room:"));
			$content->setVariable("LABEL_EXAM_2_PLACE", gettext("Place:"));			
			$content->setVariable("VALUE_EXAM_2_ROOM", $room);
			$content->setVariable("VALUE_EXAM_2_PLACE", $place);
			$content->parseCurrentBlock();
		}
		
		$examObject = exam_organization_exam_object_data::getInstance($course);
		$freetext = $examObject->getFreetext(2);
		
		if ($freetext != "")
		{
			$content->setCurrentBlock("FREETEXT_EXAM2");
			$content->setVariable("LABEL_EXAM_2_FREETEXT", gettext("Note:"));		
			$content->setVariable("VALUE_EXAM_2_FREETEXT", $freetext);
			$content->parseCurrentBlock();
		}
		
		$nothingToShow = FALSE;
	}
	
	if ( ($exam3VisibleDate || $exam3VisiblePlaces) && $eoDatabase->isParticipantForTerm(3,$koalaUserLogin) ){
		
		$content->setVariable("EXAM_3_DESC",gettext("Information on exam 3"));
		$content->setVariable("LABEL_EXAM_3_REGISTRATION_STATUS", gettext("Status:"));		
		$content->setVariable("VALUE_EXAM_3_REGISTRATION_STATUS", gettext("You are registered for exam") . " 3.");

		if ($exam3VisibleDate)
		{
			$examDateDay = $course->get_attribute("EXAM3_exam_date_day");
			$examDateMonth = $course->get_attribute("EXAM3_exam_date_month");
			$examDateYear = $course->get_attribute("EXAM3_exam_date_year");
			
			$examTimeStartHour = $course->get_attribute("EXAM3_exam_time_start_hour");
			$examTimeStartMinute = $course->get_attribute("EXAM3_exam_time_start_minute");
			$examTimeEndHour = $course->get_attribute("EXAM3_exam_time_end_hour");
			$examTimeEndMinute = $course->get_attribute("EXAM3_exam_time_end_minute");

			$content->setCurrentBlock("DATE_TIME_EXAM3");
			$content->setVariable("LABEL_EXAM_3_DATE", gettext("Date:"));
			$content->setVariable("LABEL_EXAM_3_TIME", gettext("Time:"));
			$content->setVariable("VALUE_EXAM_3_DATE", sprintf("%02d.%02d.%04d", $examDateDay, $examDateMonth, $examDateYear));
			$content->setVariable("VALUE_EXAM_3_TIME", "$examTimeStartHour:$examTimeStartMinute " . gettext("til") . " $examTimeEndHour:$examTimeEndMinute " . gettext("o'clock"));
			$content->parseCurrentBlock();
		}
		
		$koalaUserRoomAndPlace = $eoDatabase->getRoomAndPlace(3, $koalaUserLogin);
		$room = $koalaUserRoomAndPlace["room"];
		$place = $koalaUserRoomAndPlace["place"];
		if ($room=="not set") $room = gettext("not set");
		if ($place=="not set") $place = gettext("not set");
		
		if ($exam3VisiblePlaces)
		{
			$content->setCurrentBlock("ROOM_PLACE_EXAM3");
			$content->setVariable("LABEL_EXAM_3_ROOM", gettext("Room:"));
			$content->setVariable("LABEL_EXAM_3_PLACE", gettext("Place:"));			
			$content->setVariable("VALUE_EXAM_3_ROOM", $room);
			$content->setVariable("VALUE_EXAM_3_PLACE", $place);
			$content->parseCurrentBlock();
		}
		
		$examObject = exam_organization_exam_object_data::getInstance($course);
		$freetext = $examObject->getFreetext(3);
		
		if ($freetext != "")
		{
			$content->setCurrentBlock("FREETEXT_EXAM3");
			$content->setVariable("LABEL_EXAM_3_FREETEXT", gettext("Note:"));		
			$content->setVariable("VALUE_EXAM_3_FREETEXT", $freetext);
			$content->parseCurrentBlock();
		}
		
		$nothingToShow = FALSE;
	}
}

if (!$is_admin){ //hide empty table
	$content->setCurrentBlock("BLOCK_EXAM1");
	$content->parse("BLOCK_EXAM1");
	$content->setCurrentBlock("BLOCK_EXAM2");
	$content->parse("BLOCK_EXAM2");
	$content->setCurrentBlock("BLOCK_EXAM3");
	$content->parse("BLOCK_EXAM3");
}

$content->setVariable("VALUE_CONTAINER_DESC",gettext("Exam organization") );
$content->setVariable("VALUE_CONTAINER_LONG_DESC",gettext("Information page for exam organization"));

//new admins view

if ($is_admin){
	$examObject = exam_organization_exam_object_data::getInstance($course);
	$eoDatabase->calculateExamResults($course); //calculate exam results for main page
	$courseUrl = $course->get_url();
	
	
	$iconPathRed = $courseUrl . "exam_organization/geticon?image=red";
	$iconPathYellow = $courseUrl . "exam_organization/geticon?image=yellow";
	$iconPathGreen = $courseUrl . "exam_organization/geticon?image=green";
	
	
	//exam 1..3
	for($termCount=1;$termCount<=3;$termCount++){
		//exams
		$content->setCurrentBlock("BLOCK_EXAM$termCount");
		if ($examObject->termIsActivated($termCount)){
			//table header
			$content->setVariable("LABEL_META",gettext("Summary"));
			$content->setVariable("LABEL_PRE_TERM",gettext("Preparation"));
			$content->setVariable("LABEL_POST_TERM",gettext("Postprocessing"));
			$content->setVariable("LABEL_EXCEL",gettext("Completion"));
			
			if ($examObject->getRoomsVisibleStatus($termCount)) {$rVisibleStatusString="checked";} else {$rVisibleStatusString="";}
			if ($examObject->getDateAndTimeVisibleStatus($termCount)) {$dtVisibleStatusString="checked";} else {$dtVisibleStatusString="";}
			
			//show number of participants
			$numberOfParticipants = count($eoDatabase->getParticipantsForTerm($termCount));
			
			$content->setVariable("EXAM_NUMBER",gettext("Term")." $termCount");
			$content->setVariable("EXAM_ROOM","<a href='".$courseUrl."exam_organization/exam$termCount/room'>".gettext("Room").": ".$examObject->getRoomsDescriptionText($termCount)."</a>");
			$content->setVariable("EXAM_DATE","<a href='".$courseUrl."exam_organization/exam$termCount/date'>".$examObject->getDateDescriptionText($termCount)."</a>");
			$content->setVariable("EXAM_TIME","<a href='".$courseUrl."exam_organization/exam$termCount/time'>".$examObject->getTimeDescriptionText($termCount)."</a>");
			$content->setVariable("EXAM_PARTICIPANTS","<a href='".$courseUrl."exam_organization/exam$termCount/show_and_import_participants'>".gettext("Participants").": ".$numberOfParticipants."</a>");
			$content->setVariable("EXAM_PARTICIPANTS_AND_POINTS","<a href='".$courseUrl."exam_organization/exam$termCount/show_participants_and_points'>".gettext("Participant point overview")."</a>");
			$content->setVariable("EXAM_FREETEXT","<a href='".$courseUrl."exam_organization/exam$termCount/freetext'>".gettext("Free text field")."</a>");
			$content->setVariable("EXAM_MAIL","<a href='".$courseUrl."exam_organization/exam$termCount/mail'>".gettext("Mail to participants")."</a>");
			
			
			$content->setVariable("INFO_PDF_EXPORTS",gettext("PDF exports:"));
			
			if($examObject->isIndividualForRooms($termCount)){
				//$content->setVariable("EXAM_SET_PLACES",gettext('Individual seating'));
				$content->setVariable("EXAM_SET_PLACES","<a href='".$courseUrl."exam_organization/exam".$termCount."/resetplaces'>".gettext("Reset seating")."</a>");
			} else {
				$content->setVariable("EXAM_SET_PLACES","<a href='".$courseUrl."exam_organization/exam".$termCount."/setplaces'>".gettext("Set seats")."</a>");
			}
			
			$content->setVariable("EXAM_SHOW_TIMEDATE_INFO",gettext("Time and date visible to participants"));
			$content->setVariable("EXAM_SHOW_ROOM_INFO",gettext("Rooms and places visible to participants"));
			$content->setVariable("EXAM_SHOW_TIMEDATE_BOX","<input id='exam".$termCount."showdatetime' type='checkbox' onClick='emCheckbox(this);'$dtVisibleStatusString>"."</input>");
			$content->setVariable("EXAM_SHOW_ROOM_BOX","<input id='exam".$termCount."showroom' type='checkbox' onClick='emCheckbox(this)'$rVisibleStatusString>"."</input>");
			
			$content->setVariable("EXAM_PDF_PLACELIST","<a href='".$courseUrl."exam_organization/exam".$termCount."/createplaceslist'>".gettext("Seat plan")."</a>");
			$content->setVariable("INFO_PARTICIPANTSLIST",gettext("Participants list").":");
			$content->setVariable("EXAM_PDF_PARTICIPANTSLIST_BY_NAME","<a href='".$courseUrl."exam_organization/exam".$termCount."/createparticipantslistbyname'>".gettext("sorted by name")."</a>");
			$content->setVariable("EXAM_PDF_PARTICIPANTSLIST_BY_PLACE","<a href='".$courseUrl."exam_organization/exam".$termCount."/createparticipantslistbyplace'>".gettext("sorted by place")."</a>");
			$content->setVariable("EXAM_PDF_EXAMLABELS","<a href='".$courseUrl."exam_organization/exam".$termCount."/createlabels'>".gettext("Exam labels")."</a>");
			$content->setVariable("EXAM_BONUS","<a href='".$courseUrl."exam_organization/exam".$termCount."/importbonus'>".gettext("Import bonus from pointlist")."</a>");
			$content->setVariable("EXAM_EXAMKEY","<a href='".$courseUrl."exam_organization/exam".$termCount."/examkey'>".gettext("Configure exam key")."</a>");
			$content->setVariable("EXAM_ENTERPOINTS","<a href='".$courseUrl."exam_organization/exam".$termCount."/enterpoints'>".gettext("Enter exam points")."</a>");
			
			//show number of entered results
			$numberOfResults = $eoDatabase->getNumberOfEnteredResultsForTerm($termCount);
			$content->setVariable("EXAM_NUMBER_OF_ENTEREDPOINTS","<span>".gettext("Results entered") .": ".$numberOfResults."</span>");
			$content->setVariable("EXAM_EXCEL_STATISTICS","<a href='".$courseUrl."exam_organization/exam".$termCount."/excelstatistics'>".gettext("Export results and statistics to excel file")."</a>");
			$content->setVariable("EXAM_PDF_STATISTICS","<a href='".$courseUrl."exam_organization/exam".$termCount."/pdfstatistics'>".gettext("Export results and statistics to PDF file")."</a>");
			$content->setVariable("EXAM_EXCEL_EXAMOFFICE","<a href='".$courseUrl."exam_organization/exam$termCount/excelexamoffice'>".gettext("Export results to excel file for exam office")."</a>");
			$content->setVariable("EXAM_EXCEL_EXAMOFFICE_HISLSF","<a href='".$courseUrl."exam_organization/exam$termCount/excelexamoffice_hislsf'>".gettext("Export results to excel file for exam office (HIS-LSF format)")."</a>");
			
			$content->setVariable("EXAM_DELETE_DATA","<a class='button' onclick='ajaxConfirmDeleteExamData($termCount,this.href);return false;' href='".$courseUrl."exam_organization/exam".$termCount."/deletedata'>".gettext("Delete exam data")."</a>");
			$content->setVariable("EXAM_DELETE_ALL","<a class='button' href='".$courseUrl."exam_organization/exam".$termCount."/deleteterm'>".gettext("Delete term")."</a>");
			$content->setVariable("EXAM_ASSIGNMENTS","<a class='button' href='".$courseUrl."exam_organization/exam".$termCount."/assignments'>".gettext("Configure assignments")."</a>");
			
			//show status
			if ($examObject->getStatus($termCount,"room")){$content->setVariable("EXAM_ROOM_STATUSICON","<img src='$iconPathGreen' alt='Ok'></img>");}else{
				$content->setVariable("EXAM_ROOM_STATUSICON","<img src='$iconPathRed' alt='Ok'></img>");
			}
			if ($examObject->getStatus($termCount,"time")){$content->setVariable("EXAM_TIME_STATUSICON","<img src='$iconPathGreen' alt='Ok'></img>");}else{
				$content->setVariable("EXAM_TIME_STATUSICON","<img src='$iconPathRed' alt='Ok'></img>");
			}
			if ($examObject->getStatus($termCount,"date")){$content->setVariable("EXAM_DATE_STATUSICON","<img src='$iconPathGreen' alt='Ok'></img>");}else{
				$content->setVariable("EXAM_DATE_STATUSICON","<img src='$iconPathRed' alt='Ok'></img>");
			}
			if ($examObject->getStatus($termCount,"assignments")){$content->setVariable("EXAM_ASSIGNMENTS_STATUSICON","<img src='$iconPathGreen' alt='Ok'></img>");}else{
				$content->setVariable("EXAM_ASSIGNMENTS_STATUSICON","<img src='$iconPathRed' alt='Ok'></img>");
			}
			if ($examObject->getStatus($termCount,"places")){$content->setVariable("EXAM_SET_PLACES_STATUSICON","<img src='$iconPathGreen' alt='Ok'></img>");}else{
				$content->setVariable("EXAM_SET_PLACES_STATUSICON","<img src='$iconPathRed' alt='Ok'></img>");
			}
			if ($examObject->getStatus($termCount,"bonus")){$content->setVariable("EXAM_BONUS_STATUSICON","<img src='$iconPathGreen' alt='Ok'></img>");}else{
				$content->setVariable("EXAM_BONUS_STATUSICON","<img src='$iconPathRed' alt='Ok'></img>");
			}
			if ($examObject->getStatus($termCount,"examkey")){$content->setVariable("EXAM_EXAMKEY_STATUSICON","<img src='$iconPathGreen' alt='Ok'></img>");}else{
				$content->setVariable("EXAM_EXAMKEY_STATUSICON","<img src='$iconPathRed' alt='Ok'></img>");
			}
			
			$allPointsEntered = ($numberOfParticipants==$numberOfResults && $numberOfResults!=0);
			if ($allPointsEntered){$content->setVariable("EXAM_ENTERPOINTS_STATUSICON","<img src='$iconPathGreen' alt='Ok'></img>");}else{
				$content->setVariable("EXAM_ENTERPOINTS_STATUSICON","<img src='$iconPathRed' alt='Ok'></img>");
			}
		}
		$content->parse("BLOCK_EXAM");
	}
} //close if admin

$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->add_javascript_code("examorganization_main", file_get_contents("../extensions/exam_organization/javascript/exam_organization.js"));
$portal->show_html();
?>
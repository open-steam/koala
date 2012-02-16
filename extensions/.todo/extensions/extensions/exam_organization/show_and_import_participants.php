<?php
/*
 * show and import participants
 * 
 * @author Marcel Jakoblew
 */

//database intializtion
$eoDatabase = exam_organization_database::getInstance();
$eoDatabase->connect((string) $course->get_name());
$eoDatabase->calculateExamResults($course);
//$participants = $eoDatabase->getParticipantsForTerm($examTerm); //better done below
$current_user = lms_steam::get_current_user();
$examOffice = exam_office_file_handling::getInstance();
$showedParsedTable = false;


//create portal
if (!isset($portal)) {
  $portal = lms_portal::get_instance();
  $portal->initialize( GUEST_ALLOWED );
} else $portal->set_guest_allowed( GUEST_ALLOWED );

$html_handler = new koala_html_course( $course );
$html_handler->set_context( "exam_organization" ); //set context for context menu

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization_show_and_import_participants.template.html" );
$container = $course->get_workroom();
$is_admin = $course->is_admin( $current_user );



//post data
//save to database - case list loaded
if (isset($_POST["import_participants_save"]) || isset($_POST["import_participants_save_replace"])){
	if (isset($_POST["import_participants_save_replace"])){
		$replace=true;
		$eoDatabase = exam_organization_database::getInstance();
		$deleteCourseResult = $eoDatabase->deleteTerm($examTerm);
	} else {
		$replace=false;
	}
	
	//pre caching
	$ldapManager = exam_organization_ldap_manager::getInstance();
	foreach ($_POST as $postKey => $postValue){
		$mnr = (int) substr($postKey,12);
		$ldapManager->markForPreload($mnr);
	}
	$ldapManager->preload();
	
	//data eval
	foreach ($_POST as $postKey => $postValue){
		if (substr($postKey,0,12)=="PARTICIPANT_" && $postValue=="on") {
			$mnr = (int) substr($postKey,12);
			$result = $examOffice->addParticipantToDatabaseFromMatriculationNumber($mnr, $course->get_attribute("EXAM_ORGANIZATION_LAST_TERM_SELECTED"));
			
			if ($result){
				$examObject = exam_organization_exam_object_data::getInstance($course);
				$examObject->setStatus($examTerm,"places",FALSE);
				$examObject->setStatus($examTerm,"bonus",FALSE);
				if (!$replace) $portal->set_confirmation(gettext("Choosen participants saved to database"));
				if ($replace) $portal->set_confirmation(gettext("Replaced participants"));
			} else {
				$portal->set_problem_description(gettext("Choosen participants not saved to database"));
				$examObject = exam_organization_exam_object_data::getInstance($course);
				$examObject->setStatus($examTerm,"places",FALSE); //maybe not required
				$examObject->setStatus($examTerm,"bonus",FALSE); //maybe not required
			}
		}
	}
}

//save to database - case directly entered
if (isset($_POST["import_participants_save"]) && isset($_POST["input_matnr"])){
	$matriculationNumber = (int) $_POST["input_matnr"];
	if($examOffice->isMatriculationNumber($matriculationNumber)){
		$result = $examOffice->addParticipantToDatabaseFromMatriculationNumber($matriculationNumber,$course->get_attribute("EXAM_ORGANIZATION_LAST_TERM_SELECTED"));
		if ($result){
			$examObject = exam_organization_exam_object_data::getInstance($course);
			$examObject->setStatus($examTerm,"places",FALSE);
			$examObject->setStatus($examTerm,"bonus",FALSE);
			$portal->set_confirmation(gettext("Participant saved to database"));
		} else {
			$portal->set_problem_description(gettext("Participants not saved to database"));
			$examObject = exam_organization_exam_object_data::getInstance($course);
			$examObject->setStatus($examTerm,"places",FALSE); //maybe not required
			$examObject->setStatus($examTerm,"bonus",FALSE); //maybe not required
		}
	} else {
		$portal->set_problem_description(gettext("The entered number is not a matriculation number"));
	}
}

//case parse table
if( isset($_FILES['input_excel_file']['name'])){
	$tmpFileDestination = EXAM_ORGANIZATION_TEMP_DIR ."/exam_organization_tablefile_".$_FILES['input_excel_file']['name'];
	if (isset($_POST["import_participants_loadfile"])) {
		if(copy($_FILES['input_excel_file']['tmp_name'], $tmpFileDestination)) {
		    $portal->set_confirmation("The file ".  basename( $_FILES['input_excel_file']['name']). " has been uploaded");
		} else{
		    $portal->set_problem_description(gettext("There was an error uploading the file"));
		}
		
		$matriculationNumbers = $examOffice->tableFile2matriculationNumbersList($tmpFileDestination); //parse table file
		
		$ldapManager = exam_organization_ldap_manager::getInstance();
		$matriculationNumbersTable="<table class='grid' width='100%'>";
		$matriculationNumbersTable.="<tr> <th>".gettext("Last name")." </th> <th>".gettext("First name")." </th> <th> ".gettext("Matriculation number")." </th> <th>".gettext("Add to exam term")."</th> </tr>";
		
		//pre caching
		$ldapManager = exam_organization_ldap_manager::getInstance();
		foreach ($matriculationNumbers as $matriculationNumber){
			$ldapManager->markForPreload($matriculationNumber);
		}
		$ldapManager->preload();
		
		$excelRowArray = array(); //HIS excel
		foreach ($matriculationNumbers as $matriculationNumber){
			//TODO: a memory problem (next line) while importing big excel files, not good 
			//$excelRowArray[$matriculationNumber]=$examOffice->getExcelRowString($tmpFileDestination, $matriculationNumber); //HIS excel
			
			$firstName = $ldapManager->matriculationNumber2firstName($matriculationNumber);
			$lastName = $ldapManager->matriculationNumber2lastName($matriculationNumber);
			//$imtLogin = $ldapManager->matriculationNumber2imtLogin($matriculationNumber);  //removed for speed
			$matriculationNumbersTable.="<tr>";
			$matriculationNumbersTable.="<td>".$lastName."</td>";
			$matriculationNumbersTable.="<td>".$firstName."</td>";
			$matriculationNumbersTable.="<td>".$matriculationNumber."</td>";
			$matriculationNumbersTable.="<td> <input type='checkbox' name='PARTICIPANT_".$matriculationNumber."' checked='checked'> </input> </td>";
			$matriculationNumbersTable.="</tr>";
		}
		
		$excelRowArray = $examOffice->parseFullExcel2rowStringArray($tmpFileDestination);
		$_SESSION["exam_organization_excelRowArray"] = $excelRowArray;//HIS excel
		
		$matriculationNumbersTable.="</table>";
		$content->setVariable("MATRICULATION_NUMBERS_TABLE",$matriculationNumbersTable);
		$content->setVariable("INFO_MATRICULATION_NUMBERS_TABLE",gettext("The following matriculation numbers will be added to the exam organization database"));
		$content->setVariable("BUTTON_SAVE_LIST",'<input type="submit" name="import_participants_save" value="'.gettext('Add choosen participants to exam').'"/>');
		$content->setVariable("BUTTON_SAVE_LIST_REPLACE",'<input type="submit" name="import_participants_save_replace" value="'.gettext('Replace participants for this exam with choosen participants').'"/>');
		unlink($tmpFileDestination); //delete temp file
		$showedParsedTable = true;
	}
}
//end post data


//create site
//some page content
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

//icon path
$courseUrl = $course->get_url();
$iconPathDelete = $courseUrl . "exam_organization/geticon?image=delete";

//create table of all participants in db 
$participantsTable="";
$participantsTable.="<table class='grid' width='100%'>";
$participantsTable.="<tr>";
$participantsTable.="<th>".gettext("Last name")."</th>";
$participantsTable.="<th>".gettext("First name")."</th>";
$participantsTable.="<th>".gettext("Matriculation number")."</th>";
$participantsTable.="<th>".gettext("Room")."</th>";
$participantsTable.="<th>".gettext("Place")."</th>";
$participantsTable.="<th>".gettext("Bonus")."</th>";
$participantsTable.="<th>".gettext("Reached points")."</th>";
$participantsTable.="<th>".gettext("Result")."</th>";
$participantsTable.="<th>".gettext("Result (with bonus)")."</th>";
$participantsTable.="<th>".gettext("Delete")."</th>";
$participantsTable.="</tr>";

$participants = $eoDatabase->getParticipantsForTerm($examTerm," ORDER BY room, name, forename ASC");
$numberOfParticipants = 0;
foreach ($participants as $participant){
	$numberOfParticipants++;
	$resultWithBonus = $eoDatabase->getExamResultWithBonus($examTerm,$participant["imtLogin"]);
	$participantsTable.="<tr id='".$participant["imtLogin"]."' examterm='".$examTerm."' mnr='".$participant["matriculationNumber"]."'>";
	//$participantsTable.="<td onmouseover='editField(this);'>".$participant["name"]. "</td>"; //last layout
	$participantsTable.="<td>".$participant["name"]."</td>";
	$participantsTable.="<td>".$participant["forename"]."</td>";
	$participantsTable.="<td>".$participant["matriculationNumber"]."</td>";
	
	$participantRoomText = $participant["room"];
	$participantPlaceText = $participant["place"];
	if($participantRoomText=="not set") $participantRoomText=gettext("not set");
	if($participantPlaceText=="not set") $participantPlaceText=gettext("not set");
	$participantsTable.="<td><input onfocus='enterField(this);' onkeydown='replaceEnter(this);' onclick='enterField(this);' onblur='editField(this);' class='greyInputField' examterm='".$examTerm."' login='".$participant["imtLogin"]."' eodatatype='room' style='width:50px;' type='text' value='".$participantRoomText. "'></input></td>";
	$participantsTable.="<td><input onfocus='enterField(this);' onkeydown='replaceEnter(this);' onclick='enterField(this);' onblur='editField(this);' class='greyInputField' examterm='".$examTerm."' login='".$participant["imtLogin"]."' eodatatype='place' style='width:60px;' type='text' value='".$participantPlaceText. "'></input></td>";
	
	//bonus selection
	$participantsTable.="<td><select onchange='editBonusField(this);' class='greyInputField' examterm='".$examTerm."' login='".$participant["imtLogin"]."' eodatatype='bonus' style='width:50px;' type='text' value='".str_replace(".",",",$participant["bonus"]). "'>";
	if(str_replace(".",",",$participant["bonus"])=="0") {$participantsTable.="<option selected>0</option>";} else {$participantsTable.="<option>0</option>";}
	if(str_replace(".",",",$participant["bonus"])=="0,3") {$participantsTable.="<option selected>0,3</option>";} else {$participantsTable.="<option>0,3</option>";}
	if(str_replace(".",",",$participant["bonus"])=="0,7") {$participantsTable.="<option selected>0,7</option>";} else {$participantsTable.="<option>0,7</option>";}
	if(str_replace(".",",",$participant["bonus"])=="1") {$participantsTable.="<option selected>1</option>";} else {$participantsTable.="<option>1</option>";}
	$participantsTable.="</select></td>";
	
	//reached points
	if ($participant["reachedPoints"]==-0.1) $participant["reachedPoints"]=gettext("not entered");
	if ($participant["isNT"]=="NT") $participant["reachedPoints"]=gettext("NT");
	if ($participant["isNT"]=="BV") $participant["reachedPoints"]=gettext("attempt to defraud");
	if ($participant["isNT"]=="SICK") $participant["reachedPoints"]=gettext("Sick");
	$participantsTable.='<td style="cursor:pointer;" onclick="setPoints(this);">'.str_replace(".",",",$participant["reachedPoints"]). '</td>';
	
	//result without bonus
	if ($participant["result"]==-1) $participant["result"]=gettext("not entered");
	$participantsTable.="<td>".str_replace(".",",",$participant["result"]). "</td>";
	
	//result with bonus
	if ($resultWithBonus==-1) $resultWithBonus=gettext("not entered");
	$participantsTable.="<td>".str_replace(".",",",$resultWithBonus). "</td>";
	$participantsTable.='<td><img style="cursor:pointer;padding-left:30%;" onclick="deleteRow(this)" src="'.$iconPathDelete.'" login="'.$participant["imtLogin"].'" examterm="'.$examTerm.'" alt="delete icon"></img></td>';
	$participantsTable.="</tr>";
}
$participantsTable.="</table>";
if($numberOfParticipants==0) {$participantsTable="";} //hide empty table

//create buttons and info / create last part of site
if ($is_admin){
	$examChoosen = 0;
	if (isset($examTerm)){$examChoosen = $examTerm;}
	$content->setVariable("INFO_EXAM_NUMBER",gettext("Viewing page for Exam term")." ".$examChoosen);
	if ($examChoosen == 0) $content->setVariable("INFO_EXAM_NUMBER",gettext("Please choose an exam term"));
	$content->setVariable("EXAM_INFORMATION",gettext('Exam term')." ".$examChoosen);
	$content->setVariable("BACK_LINK", "<a href=" . $course->get_url() . "exam_organization/>" . gettext( "back to exam organization" ) . "</a>" );
	$content->setVariable("INFO_TABLE_PARTICIPANTS_IN_DB",gettext("Participants saved in exam organization database"));
	$content->setVariable("TABLE_PARTICIPANTS_IN_DB",$participantsTable);
	
	if(!$showedParsedTable){ //show only buttons when add table is hidden
		$content->setVariable("INFO_IMPORT_LIST",gettext('Import from LSF exported Excel file'));
		$content->setVariable("FIELD_IMPORT_LIST",'<input type="file" name="input_excel_file"/>');
		$content->setVariable("INFO_ADD_PARTICIPANT",gettext('Add matriculation number to exam'));
		$content->setVariable("FIELD_ADD_PARTICIPANT","<input type='text' name='input_matnr' onkeyup='getFullName(this)'/>");
		$content->setVariable("BUTTON_LOAD_FILE",'<input type="submit" name="import_participants_loadfile" value="'.gettext('Read file').'"/>');
		$content->setVariable("BUTTON_SAVE_ONE","<input type='submit' name='import_participants_save' value='".gettext('Save participant')."'/>");
		$content->setVariable("INFO_SEATING",gettext('Click on the room or place values to modify room or place for a participant. To save the modified value click outside the field.'));
	}
}


$content->setVariable("VALUE_CONTAINER_DESC",gettext("Show and import participants"));

//print page
$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->show_html();
exit(0);
?>
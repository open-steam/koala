<?php
/*
 * show participants and points
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
$content->loadTemplateFile( PATH_EXTENSIONS.PATH_TEMPLATES_EXAM_ORGANIZATION . "exam_organization_show_participants_and_points.template.html" );
$container = $course->get_workroom();
$is_admin = $course->is_admin( $current_user );



//post data
//save to database - case list loaded
if (isset($_POST["import_participants_save"])){
	
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
				$portal->set_confirmation(gettext("Choosen participants saved to database"));
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
	$documentRoot = $_SERVER["DOCUMENT_ROOT"];
	$tmpFileDestination = $documentRoot."/exam_organization_tablefile_".$_FILES['input_excel_file']['name'];
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
		
		foreach ($matriculationNumbers as $matriculationNumber){
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
		
		$matriculationNumbersTable.="</table>";
		$content->setVariable("MATRICULATION_NUMBERS_TABLE",$matriculationNumbersTable);
		$content->setVariable("INFO_MATRICULATION_NUMBERS_TABLE",gettext("The following matriculation numbers will be added to the exam organization database"));
		$content->setVariable("BUTTON_SAVE_LIST",'<input type="submit" name="import_participants_save" value="'.gettext('Save choosen participants').'"/>');
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

//get number of assignments
$numberOfAssingments = $examObject->getNumberOfAssignments($examTerm);

//create table of all participants in db 
$participantsTable="";
$participantsTable.="<table class='grid' width='100%'>";
$participantsTable.="<tr>";
$participantsTable.="<th>".gettext("Last name")."</th>";
$participantsTable.="<th>".gettext("First name")."</th>";
$participantsTable.="<th>".gettext("Matriculation number")."</th>";
$participantsTable.="<th>".gettext("Bonus")."</th>";
$participantsTable.="<th width='260'>". gettext("Assignments") ."</th>";

$participantsTable.="<th>".gettext("Reached points")."</th>";
$participantsTable.="<th>".gettext("Result")."</th>";
$participantsTable.="<th>".gettext("Result (with bonus)")."</th>";
$participantsTable.="</tr>";

$participants = $eoDatabase->getParticipantsForTerm($examTerm," ORDER BY room, name, forename ASC");
$numberOfParticipants = 0;
foreach ($participants as $participant){
	$numberOfParticipants++;
	$resultWithBonus = $eoDatabase->getExamResultWithBonus($examTerm,$participant["imtLogin"]);
	$participantsTable.="<tr id='".$participant["imtLogin"]."' examterm='".$examTerm."' mnr='".$participant["matriculationNumber"]."'>";
	$participantsTable.="<td>".$participant["name"]."</td>";
	$participantsTable.="<td>".$participant["forename"]."</td>";
	$participantsTable.="<td>".$participant["matriculationNumber"]."</td>";
	
	//bonus selection
	$participantsTable.="<td><select onchange='editBonusField(this);' class='greyInputField' examterm='".$examTerm."' login='".$participant["imtLogin"]."' eodatatype='bonus' style='width:50px;' type='text' value='".str_replace(".",",",$participant["bonus"]). "'>";
	if(str_replace(".",",",$participant["bonus"])=="0") {$participantsTable.="<option selected>0</option>";} else {$participantsTable.="<option>0</option>";}
	if(str_replace(".",",",$participant["bonus"])=="0,3") {$participantsTable.="<option selected>0,3</option>";} else {$participantsTable.="<option>0,3</option>";}
	if(str_replace(".",",",$participant["bonus"])=="0,7") {$participantsTable.="<option selected>0,7</option>";} else {$participantsTable.="<option>0,7</option>";}
	if(str_replace(".",",",$participant["bonus"])=="1") {$participantsTable.="<option selected>1</option>";} else {$participantsTable.="<option>1</option>";}
	$participantsTable.="</select></td>";
	
	//single assignments
	$participantsTable.='<td width="260" onclick="setPoints(this);" style="cursor: pointer;">';
	for($i=1;$i<=$numberOfAssingments;$i++){
		$pointsValue = $eoDatabase->getExamPointsForAssignment($course->get_name(), $examTerm, $participant["imtLogin"], $i);
		$participantsTable .= '<div align="center" style="background-color: white; padding: 5px; border-color: white; width: 25px; display: table-cell;"><b>' . $i . "</b><br>" . $pointsValue . '</div>';
		if ($i % 6 == 0) $participantsTable .= '<br>';
		else $participantsTable .= '<div style="width: 10px; display: table-cell;"></div>';
	}
	$participantsTable.='</td>';
	
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
	$content->setVariable("BACK_LINK", "<a href=\"" . PATH_URL . SEMESTER_URL . "/" . $course->get_semester()->get_name() . "/" . $course->get_course_id() . "/exam_organization/\">" . gettext( "back to exam organization" ) . "</a>" );
	$content->setVariable("INFO_TABLE_PARTICIPANTS_IN_DB",gettext("Participants saved in exam organization database"));
	$content->setVariable("INFO_MODIFY_POINTS",gettext("To modify the points click on a points value."));
	$content->setVariable("TABLE_PARTICIPANTS_IN_DB",$participantsTable);
}


$content->setVariable("VALUE_CONTAINER_DESC",gettext("Participants and points overview"));

//print page
$html_handler->set_html_left( $content->get());
$portal->set_page_main( $html_handler->get_headline(), $html_handler->get_html() , "" );
$portal->show_html();
exit(0);
?>
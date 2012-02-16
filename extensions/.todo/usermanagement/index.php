<?php

// Configuration file
include_once( "../../etc/koala.conf.php" );


function isAjaxRequest() {
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}

// Instance for accessing data on sTeam- or LDAP server
$GLOBALS["USERMANAGEMENT_DATA_ACCESS"] = new sTeamServerDataAccess();



// Initialize the LMS portal
$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );



// Initialize sTeam structure if necessary
if(!$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isInitialized()) {
	$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->initialize();
}


// Some initialization stuff
$frontController 	= new FrontController($portal);
$request 			= new HttpRequest();
$response 			= new HttpResponse();
$viewHelper 		= new ViewHelper();



// Handle request by calling command with frontcontroller
$result = $frontController->handleRequest($request, $response);



// Is current request is an AJAX-Request, don't generate template code
if (isAjaxRequest()) {
	echo $result;
	die();
}



// Initialize template
$usermanagementHTMLTemplate = new koala_html_usermanagement($request->getParameter("template"));
$content = $usermanagementHTMLTemplate->get_template();



// ID of the current user
$currentUserID = $GLOBALS["STEAM"]->get_current_steam_user()->get_id();



// Set the value of the session variable with the current customer id, if not already done
if (!isset($_SESSION["CURRENT_CUSTOMER_ID"])) {
	
	// load default or if user is admin, set the value to the first found customer is
	if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isAdmin($currentUserID)) {
		$customerIDs = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllCustomers();
		if (defined("STEAM_CURRENT_SEMESTER")) {
			foreach ($customerIDs as $id => $name) {
				if ($name == STEAM_CURRENT_SEMESTER) {
					$_SESSION["CURRENT_CUSTOMER_ID"] = $id;
					$_SESSION["CURRENT_CUSTOMER_NAME"] = $name;
					break;
				}
			}
		}
		if (!isset($_SESSION["CURRENT_CUSTOMER_ID"])) {
			foreach ($customerIDs as $id => $name) {
				$_SESSION["CURRENT_CUSTOMER_ID"] = $id;
				$_SESSION["CURRENT_CUSTOMER_NAME"] = $name;
				break;
			}
		}
	}
	
	// If the user is a customerAdmin, set the value to the customer id the user belongs to
	else if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($currentUserID)) {
		$_SESSION["CURRENT_CUSTOMER_ID"] = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getEmployeeCustomerID($currentUserID);
	}
}



// If user is an admin, show the current customer perspective
if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isAdmin($currentUserID) && isset($_SESSION["CURRENT_CUSTOMER_NAME"])) {
	$content->setVariable("CURRENT_CUSTOMER", "<a href=\"javascript:;\"> Aktives Unternehmen: " . $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjectDesc($_SESSION["CURRENT_CUSTOMER_ID"]) . "</a>");
	$content->setVariable("CURRENT_CUSTOMER_STYLE", "display:block; text-align:right; margin-top:10px; margin-right:10px; margin-bottom:10px;");
}



////////// DEBUG STUFF ///////////////////////////////////////////////////////////////////////////////////////////////////////

function boolString ($boolVal) {
	return ($boolVal) ? "true" : "false";
}
/*
echo "IsLocked: " . 	 	boolString($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isLocked($currentUserID)) .			"<br>";
echo "IsAdmin: " . 		 	boolString($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isAdmin($currentUserID)) . 			"<br>";
echo "IsCustomerAdmin: " .  boolString($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($currentUserID)) . 	"<br>";
echo "IsBranchAdmin: " .  	boolString($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isBranchAdmin($currentUserID, "")) . "<br>"; 
*/

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



// If user did not change it's initial password or has been reset by an admin
if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isGeneratedPassword($currentUserID) && $GLOBALS["STEAM"]->get_current_steam_user()->get_name() != "root") {
	$usermanagementHTMLTemplate = new koala_html_usermanagement("user-password");
	$content = $usermanagementHTMLTemplate->get_template();
	$content->setVariable("INFO_CHANGE_INITIAL_PASSWORD", "Entweder haben Sie ihr ursprüngliches Passwort noch nicht ge&auml;ndert, oder es wurde von einem Administrator neu gesetzt. Bitte setzen Sie ein neues Passwort, um mit diesem System weiter arbeiten zu k&ouml;nnen.");
	$content->setVariable("TITLE_CHANGE_INITIAL_PASSWORD", "Ursprüngliches Passwort gefunden");
	$content->setVariable("TARGET_URL", "/usermanagement/");
	$content->setVariable("VALUE_INITIAL_PASSWORD_CHANGING", "1");
}



// Set data for specific templates
else {
		
	switch ($request->getParameter("template")) {
		
		case "setup" :
			//lms_portal::get_instance()->add_javascript_src("usermanagement", "/styles/standard/javascript/JSON.js");
			
			if ($viewHelper->rootUserOk()) {
				$content->setVariable("ROOT_ITEM", "item-ok");
			} else {
				$content->setVariable("ROOT_ITEM", "item-fail");
			}
			$content->setVariable("CUSTOMER_OPTION", $viewHelper->getCustomersCombobox());
			$content->setVariable("COURSE_OPTION", $viewHelper->getElearningCoursesCombobox());
			$usermanagementHTMLTemplate->set_context("setup");
			break;

		case "user-password" :
			
			$content->setVariable("CSS_DISABLE_INFOBAR", "style=\"display:none;\"");
			$content->setVariable("TARGET_URL", "/usermanagement/");
			
			// Redirect to home, if initial password has changed
			if ($request->getParameter("initialPasswordChanging") == "1") {
				$_SESSION["changedInitialPassword"] = "1";
				header("Location: " . PATH_URL . "home/");
			}
			
			$usermanagementHTMLTemplate->set_context("user");
			
			break;
			

			
		case "user-changeAdminPerspective" : {
			
				$content->setVariable("CUSTOMER_COMBOBOX", $viewHelper->getCustomersCombobox($_SESSION["CURRENT_CUSTOMER_ID"]));
			
				$usermanagementHTMLTemplate->set_context("user");
			
			break;		
		}
			
		

		case "employees-modify" : {
			
			$content->setVariable("HEADLINE", "Benutzer bearbeiten");
			//$content->setVariable("LABEL_FILTER", "Filter ausw&auml;hlen:");
			//$content->setVariable("HEADLINE_FILTER", "Filter");
			
			$content->setVariable("FILTER_COMBOBOX", $viewHelper->getFilterComboBox($_SESSION["CURRENT_CUSTOMER_ID"]));
			$content->setVariable("EMPLOYEES_TABLE", $viewHelper->getEmployeesTable($_SESSION["CURRENT_CUSTOMER_ID"]));
			
			$usermanagementHTMLTemplate->set_context("employees");
			
			break;
		}
			
		
		
		case "employees-create" : {
			
			$content->setVariable("AVAILABLE_BRANCH", $viewHelper->getBranchesAsOptions(true, $request->getParameter("branchID")));
			$content->setVariable("VALUE_FIRSTNAME", $request->getParameter("firstname"));
			$content->setVariable("VALUE_LASTNAME", $request->getParameter("lastname"));
			$content->setVariable("VALUE_EMAIL", $request->getParameter("email"));
			$content->setVariable("VALUE_CUSTOMER_ID", $_SESSION["CURRENT_CUSTOMER_ID"]);
			
			$content->setVariable("LABEL2", "Kurs ausw&auml;hlen:");
			$content->setVariable("COURSE_COMBOBOX", $viewHelper->getCourseCombobox($_SESSION["CURRENT_CUSTOMER_ID"], false));
			
			$usermanagementHTMLTemplate->set_context("employees");
			
			break;		
		}
	
		case "employees-import" : {
			
			$content->setVariable("HEADLINE", "Benutzer aus Excel-Liste importieren");
			$content->setVariable("LABEL1", "Lokale Excel-Datei ausw&auml;hlen:");
			$content->setVariable("LABEL2", "Kurs ausw&auml;hlen:");
			$content->setVariable("BUTTON_IMPORT", "Import starten");
			$content->setVariable("VALUE_CUSTOMER_ID", $_SESSION["CURRENT_CUSTOMER_ID"]);
			
			$content->setVariable("COURSE_COMBOBOX", $viewHelper->getCourseCombobox($_SESSION["CURRENT_CUSTOMER_ID"], false));
			
			$usermanagementHTMLTemplate->set_context("employees");
			
			break;		
		}
		
		case "employees-export" : {
			
			$customerID = "";
			
			if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($currentUserID)) {
				$customerID = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getEmployeeCustomerID($currentUserID);
			} else {
				$customerID = $_SESSION["CURRENT_CUSTOMER_ID"];
			}
			$content->setVariable("CUSTOMERID", $customerID);
			$content->setVariable("COURSE_COMBOBOX", $viewHelper->getExportCourseComboBox($_SESSION["CURRENT_CUSTOMER_ID"]));
			
			$usermanagementHTMLTemplate->set_context("employees");
			
			break;		
		}
		
		case "employees-history" : {
			lms_portal::get_instance()->set_prototype_enabled(false);
			lms_portal::get_instance()->add_javascript_src("usermanagement", "/styles/standard/javascript/jquery-1.4.2.min.js");
			lms_portal::get_instance()->add_javascript_src("usermanagement", "/styles/standard/javascript/collapse.js");
			lms_portal::get_instance()->add_javascript_onload("usermanagement", "animatedcollapse.init();");

			$logFiles = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCustomerCreationLogFiles($_SESSION["CURRENT_CUSTOMER_ID"]);
			foreach ($logFiles as $logfile) {
				lms_portal::get_instance()->add_javascript_code("usermanagement", "animatedcollapse.addDiv('".str_replace(".","_", $logfile["name"])."', 'group=log,hide=1');");
			}
			//lms_portal::get_instance()->add_javascript_code("usermanagement", "animatedcollapse.init();");
			$content->setVariable("USER_HISTORY", $viewHelper->createHistoryEntries($logFiles));
			$usermanagementHTMLTemplate->set_context("employees");
			
			$portal->add_javascript_code("usermanagement_index", $viewHelper->jsCheckAllCheckboxes());
			
			break;		
		}
	
		
		
		case "branches" : 

			// Add customers combobox
			$content->setVariable("CUSTOMERS_COMBOBOX", $viewHelper->getCustomersCombobox());
			
			// Set template placeholders
			$content->setVariable("VALUE_HEADLINE_ADD_BRANCH", 		"Neue Filiale hinzuf&uuml;gen");
			$content->setVariable("VALUE_HEADLINE_MODIFY_BRANCH", 	"Filialen bearbeiten");
			$content->setVariable("LABEL_NAME", 					"Name");
			$content->setVariable("LABEL_CUSTOMER", 				"Kunde");
			$content->setVariable("VALUE_ADD_BUTTON", 				"Filiale anlegen");
			$content->setVariable("TABLE_COL_1", 					"Filiale-ID");
			$content->setVariable("TABLE_COL_2", 					"Kunde");
			$content->setVariable("TABLE_COL_3", 					"Name");
			$content->setVariable("TABLE_COL_4", 					"Aktionen");		
			
			$customerID = "";
			
			if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($currentUserID)) {
				$customerID = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getEmployeeCustomerID($currentUserID);
			}
			
			// Add table rows for available branches to modify
			foreach ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllBranches($customerID) as $branchID => $branchName) {
				$content->setCurrentBlock("BLOCK_BRANCHES");
				$content->setVariable("BRANCH_ENTRY", $viewHelper->getBranchRow($branchID, $branchName));
				$content->parse("BLOCK_BRANCHES");
			}
			
			// Some JavaScript
			$portal->add_javascript_code("usermanagement_index", $viewHelper->jsSetCommandValue());
			
			// Set context
			$usermanagementHTMLTemplate->set_context("branches");
			
			break;		
		
		case "customers" :
	
			// Set template placeholders
			$content->setVariable("VALUE_HEADLINE_ADD_CUSTOMER", 	"Neues Unternehmen hinzuf&uuml;gen");
			$content->setVariable("VALUE_HEADLINE_MODIFY_CUSTOMER", "Unternehmen bearbeiten");
			$content->setVariable("LABEL_NAME", 					"Name*");
			$content->setVariable("LABEL_ID", 					    "ID*");
			$content->setVariable("VALUE_ADD_BUTTON", 				"Unternehmen anlegen");
			$content->setVariable("TABLE_COL_1", 					"ID");
			$content->setVariable("TABLE_COL_2", 					"Name");
			$content->setVariable("TABLE_COL_3", 					"Aktionen");
			
			// Set table rows for all customers
			foreach ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllCustomers() as $customerID => $customerName) {
				$content->setCurrentBlock("BLOCK_CUSTOMERS");
				$content->setVariable("CUSTOMER_ENTRY", $viewHelper->getCustomerRow($customerID, $customerName, $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjectDesc($customerID)));
				$content->parse("BLOCK_CUSTOMERS");
			}
			
			// Add some JavaScript
			$portal->add_javascript_code("usermanagement_index", $viewHelper->jsSetCommandValue());
			
			// Set context
			$usermanagementHTMLTemplate->set_context("customers");
			
			break;			
	

			
		case "courses-overview" :
			
			$counter = 0;	
			
			foreach ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseDataForCustomer($_SESSION["CURRENT_CUSTOMER_ID"]) as $courseID) {
				$content->setCurrentBlock("BLOCK_COURSES_ENTRY");
				$content->setVariable("COURSE_ENTRY", $viewHelper->getCourseRowOverview($courseID));
				$content->parse("BLOCK_COURSES_ENTRY");
				$counter++;
			}
			
			if ($counter == 0) {
				$content->setVariable("FORM_STYLE", "display:none;");
				$content->setVariable("INFO_TITLE", "Keine Kurse verf&uuml;gbar");
				$content->setVariable("INFO_TEXT", "F&uuml;r ihr Unternehmen wurden noch keine Kurse freigeschaltet");
			} 
			
			else {
				$content->setVariable("INFO_STYLE", "display:none;");
				$content->setVariable("HEADLINE", "F&uuml;r ihr Unternehmen verf&uuml;gbare Kurse");
				$content->setVariable("TABLE_COL_1", "Kursname und -informationen");
				$content->setVariable("TABLE_COL_2", "verf&uuml;gbare Lizenzen");				
			}

			$usermanagementHTMLTemplate->set_context("courses");
			
			break;
			
			
			
		case "courses-participants" :
			
			$content->setVariable("HEADLINE1", "Teilnehmer des Kurses");
			$content->setVariable("HEADLINE2", "Teilnehmer zum Kurs hinzuf&uuml;gen");
			$content->setVariable("BUTTON_SELECT_ALL", "alle markieren");
			$content->setVariable("BUTTON_SUBMIT", "Benutzer hinzuf&uuml;gen");
			$content->setVariable("TABLE_COL_1", "Benutzer");
			$content->setVariable("TABLE_COL_2", "Vor- und Nachname");
			$content->setVariable("TABLE_COL_3", "Aktionen");
			$content->setVariable("VALUE_COURSE_ID", $request->getParameter("courseID"));
			
			$participants = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseParticipants($request->getParameter("courseID"));
			
			if (count($participants) == 0) {
				$content->setVariable("FORM1_STYLE", "display:none;");
				$content->setVariable("INFO_TITLE", "Keine Teilnehmer verf&uuml;gbar");
				$content->setVariable("INFO_TEXT", "Diesem Kurs wurden noch keine Teilnehmer hinzugef&uuml;gt");				
			}
			
			else {
				$content->setVariable("INFO_STYLE", "display:none;");
				
				foreach ($participants as $userID => $login) {
					$content->setCurrentBlock("BLOCK_PARTICIPANTS_ENTRY");
					$content->setVariable("PARTICIPANT_ENTRY", $viewHelper->getParticipantRow($userID));
					$content->parse("BLOCK_PARTICIPANTS_ENTRY");
				}
			}
			
			// The selection box with users that are currently not participants of the course
			$content->setVariable("PARTICIPANTS_SELECTION", $viewHelper->getParticipantSelection($_SESSION["CURRENT_CUSTOMER_ID"], $request->getParameter("courseID")));

			$usermanagementHTMLTemplate->set_context("courses");	
			
			break;
			

			
		case "courses-assignCSV" :
			
			$customerID = "";
	
			// If current user is admin
			if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isAdmin($currentUserID)) {
				$customerID = $request->getParameter("customerID");
				$content->setVariable("CUSTOMER_SELECTION", $viewHelper->getCustomersSelection("courses-assignCSV", $customerID));
			}
			
			// If current user is customerAdmin
			else if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($currentUserID)) {
				$customerData = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getEmployeeCustomer($currentUserID);
				$customerID = $customerData["id"];
			}
			
			$content->setVariable("VALUE_CUSTOMER_ID", $customerID);
			
			foreach ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllCourseIDs() as $courseID) {
				
				$data = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseData($courseID);
				
				if ($data["customerID"] == $customerID && $customerID != "") {
					$content->setCurrentBlock("BLOCK_COURSES_ENTRY");
					$content->setVariable("COURSE_ENTRY", $viewHelper->getCourseRowAssignCSV($courseID));
					$content->parse("BLOCK_COURSES_ENTRY");
				}
			}
			$usermanagementHTMLTemplate->set_context("courses");
			
			break;
	
		case "courses-remove" :
			$usermanagementHTMLTemplate = new koala_html_usermanagement("courses-remove");
			$usermanagementHTMLTemplate->set_context("courses");
			$content = $usermanagementHTMLTemplate->get_template();
			
			$customerID = "";
			
			$currentUserID = $GLOBALS["STEAM"]->get_current_steam_user()->get_id();
			
			// If current user is admin
			if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isAdmin($currentUserID)) {
				$customerID = $request->getParameter("customerID");
				$content->setVariable("CUSTOMER_SELECTION", $viewHelper->getCustomersSelection("courses-remove", $customerID));
			}
			
			// If current user is customerAdmin
			else if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($currentUserID)) {
				$customerData = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getEmployeeCustomer($currentUserID);
				$customerID = $customerData["id"];
			}
			
			$content->setVariable("VALUE_CUSTOMER_ID", $customerID);
			
			foreach ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllCourseIDs() as $courseID) {
				
				$data = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseData($courseID);
				
				if ($data["customerID"] == $customerID && $customerID != "") {
					$content->setCurrentBlock("BLOCK_COURSES_ENTRY");
					$content->setVariable("COURSE_ENTRY", $viewHelper->getCourseRowRemove($courseID));
					$content->parse("BLOCK_COURSES_ENTRY");
				}
			}
			break;
			
		case "courses-activate" :
	
			foreach ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllCourseIDs() as $courseID) {
				$content->setCurrentBlock("BLOCK_COURSES_ENTRY");
				$content->setVariable("COURSE_ENTRY", $viewHelper->getCourseRow($courseID));
				$content->parse("BLOCK_COURSES_ENTRY");
			}
			
			$portal->add_javascript_code("usermanagement_index", $viewHelper->jsSetCommandValue());
			$portal->add_javascript_code("usermanagement_index", $viewHelper->jsConfirmAction("confirmDeactivation", "deactivateCourse", "formular", "Den Kurs fuer diesen Unternehmen wirklich loeschen?"));
			
			$usermanagementHTMLTemplate->set_context("courses");
			
			break;
	
//		case "courses-quota" :
//	
//			foreach ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllCourseIDs() as $courseID) {
//				$content->setCurrentBlock("BLOCK_COURSES_ENTRY");
//				$content->setVariable("COURSE_ENTRY", $viewHelper->getCourseRowQuota($courseID));
//				$content->parse("BLOCK_COURSES_ENTRY");
//			}
//			
//			$portal->add_javascript_code("usermanagement_index", $viewHelper->jsHandleComboboxChange());
//	
//			$usermanagementHTMLTemplate->set_context("courses");
//			
//			break;
			
		case "admin" :
			$usermanagementHTMLTemplate->set_context("admin");
			break;
			
		default : {
			
			$usermanagementHTMLTemplate = new koala_html_usermanagement("user-password");
			$usermanagementHTMLTemplate->set_context("user");
			
			$content = $usermanagementHTMLTemplate->get_template();
			
			$content->setVariable("CSS_DISABLE_INFOBAR", "style=\"display:none;\"");
			$content->setVariable("TARGET_URL", "/usermanagement/");
		}
	}
}


//$portal->set_page_title(gettext("usermanagement_titel"));
$portal->set_page_title("Benutzerverwaltung");









$portal->set_page_main(
	array(
		array("link" => "javascript:history.back()",
			  "name" => "zurück"),
		array( "link" => "",
			"name" => "Benutzerverwaltung")
	),
	$usermanagementHTMLTemplate->get_html(),
	""
);

$portal->show_html();

if ($request->issetParameter("forceLogout")) {
	$portal->logout();
}

?>
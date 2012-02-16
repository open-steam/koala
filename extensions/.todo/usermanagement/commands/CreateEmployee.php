<?php
mb_internal_encoding("UTF-8"); 

class CreateEmployee implements Command {
	
	private $commandHelper;
	
	public function execute (Request $request, Response $response) {
		
		$this->commandHelper = new CommandHelper();
		
		// Check values
		$firstname = $request->getParameter("firstname");
		$lastname  = $request->getParameter("lastname");
		$email     = $request->getParameter("email");
		$branchID  = $request->getParameter("branchID");
		$customerID  = $request->getParameter("customerID");
		$courseID  = $request->getParameter("courseID");
		
		if ($courseID != 0) {
			$elearning_course_id = elearning_mediathek::get_elearning_unit_id($courseID);
	    	$customer_name = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjectName($customerID);
			$numberOfLicenses 	= licensemanager::get_instance()->get_registered_license_seats(array(new elearning_context($elearning_course_id), new unternehmens_context($customer_name)));
			$remainingLicenses  = licensemanager::get_instance()->get_available_license_seats(array(new elearning_context($elearning_course_id), new unternehmens_context($customer_name)));
		}
		
		if ($firstname == "") {
			throw new UsermanagementException("Keinen Vornamen angegeben", "Bitte gebe einen Vornamen ein.");
		} else if ($lastname == "") {
			throw new UsermanagementException("Keinen Nachnamen angegeben", "Bitte gebe einen Nachnamen ein.");
		} else if ($courseID != 0 && $remainingLicenses == 0) {
			throw new UsermanagementException("Keine Lizenz für diesen Kurs verfügbar.", "Bitte installieren sie weiter Lizenzen oder wählen sie einen anderen Kurs aus.");
		}

		//else if ($branchID == "0") {
		//	throw new UsermanagementException("Keine Filiale angegeben", "Bitte eine Filiale angeben, bzw. zuvor eine erstellen");
		//}
		
		// If values are correct
		else {
			
//			// A list with all user
//			$userlist = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserList();
//			
//			// The initial password for employee
//			$password = $this->commandHelper->createRandomPassword(8);
//			
//			// A login candidate
//			$loginCandidate = strtolower($firstname[0] . str_replace(" ", "", $lastname));
//			
//			// Add a suffix, if login already exists
//			if (array_search($loginCandidate, $userlist) != false) {
//				for ($i=1; $i<=count($userlist)+1; $i++) {
//					if (array_search($loginCandidate . $i, $userlist) == false) {
//						$loginCandidate .= $i;
//						break;
//					}
//				}
//			}

			// create new user
			$loginCandidate = $this->commandHelper->createValidLogin($firstname, $lastname);
			$password = $this->commandHelper->createRandomPassword(8);
			
			$activationCode = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->createEmployee($loginCandidate, $password, $email, $firstname, $lastname, $branchID, $customerID);
			$xmlContent = "<users>\n";
			$xmlContent .= "\t<user firstname=\"" . $firstname . "\" lastname=\"" . $lastname . "\" login=\"" . $loginCandidate . "\" password=\"" . $password . "\" state=\"ok\" />\n";
			$xmlContent .= "</users>";
			$xmlFileName = time() . ".xml";
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->saveCustomerCreationLogFile($_SESSION["CURRENT_CUSTOMER_ID"], $xmlFileName, $xmlContent);	
			
			$text = "";
			if ($courseID != 0 && $remainingLicenses > 0) {
				$elearning_course_id = elearning_mediathek::get_elearning_unit_id($courseID);
	    		$customer_name = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjectName($customerID);
	    		$userID = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->login2ID($loginCandidate);
				licensemanager::get_instance()->register_user($userID, array(new elearning_context($elearning_course_id), new unternehmens_context($customer_name)));
				$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->addParticipant($userID, $courseID);
				$text = " und zum Kurs hinzugefügt";
			}
			
			return "Neuer Benutzer wurde erfolgreich angelegt$text.<br>Login: " . $loginCandidate . " und Passwort: " . $password;
		}
		
	}
	
}

?>
<?php
mb_internal_encoding("UTF-8"); 

include_once "../../classes/phpexcel/Classes/PHPExcel/IOFactory.php";

class ImportExcelFile implements Command {
	
	private $commandHelper;
	
	public function execute (Request $request, Response $response) {
		
		$this->commandHelper = new CommandHelper();

		$customerID = $request->getParameter("customerID");
		
		// The id of the course, the new created user will be added to
		$courseID = $request->getParameter("courseID");
		
		if ($request->getParameter("courseID") != "0") {
			$elearning_course_id = elearning_mediathek::get_elearning_unit_id($courseID);
			$customer_name = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjectName($customerID);
			$numberOfLicenses 	= licensemanager::get_instance()->get_registered_license_seats(array(new elearning_context($elearning_course_id), new unternehmens_context($customer_name)));
			$remainingLicenses  = licensemanager::get_instance()->get_available_license_seats(array(new elearning_context($elearning_course_id), new unternehmens_context($customer_name)));
			$remainingLicensesBak = $remainingLicenses;
		}
		
		$xlsFilePath = PATH_TEMP . $_FILES["csvFile"]["name"];
		
		move_uploaded_file($_FILES["csvFile"]["tmp_name"], $xlsFilePath);
		
		// Get wrapper object for xls file
		$PHPExcelWrapper = PHPExcel_IOFactory::load($xlsFilePath);
		
		// Get the first spreadsheet of xls file
		$spreadsheet = $PHPExcelWrapper->getSheet();
		
		// Iterates over rows
		$rowIterator = $spreadsheet->getRowIterator();
		
		// Counts the number of created users
		$newUserCounter = 0;

		$xmlContent = "<users>\n";
		
		while ($rowIterator->valid()) {
			
			// Get current row
			$row = $rowIterator->current();

			// Iterates over cells
			$cellIterator = $row->getCellIterator();
			
			// Data for new user
			$firstname 	= "";
			$lastname 	= "";
			$email 		= "";
			$branchID 	= "";
			$password 	= $this->commandHelper->createRandomPassword(8);
			
			while ($cellIterator->valid()) {
				
				// Get current cell
				$cell = $cellIterator->current();
				
				$colIndex = $cell->getColumn();
				$rowIndex = $cell->getRow();
				$value = $cell->getValue();
				
				switch ($colIndex) {
					case "A" :
						$firstname = $this->fixEncoding($value);
						break;
					case "B" :
						$lastname = $this->fixEncoding($value);
						break;
					case "C" :
						$email = $this->fixEncoding($value);
						break;
					case "D" :
						$branchID = $this->fixEncoding($value);
						break;
				}

				$cellIterator->next();
			}
	
			if ($firstname != "" || $lastname != "") {
				
				// create new user
				$login = $this->commandHelper->createValidLogin($firstname, $lastname);

				try {
					$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->createEmployee($login, $password, $email, $firstname, $lastname, "", $customerID);
					$creationState = "ok";
					$newUserCounter++;
				}
				catch (Exception $exception) {
					$creationState = "fail";
				}
				
				$xmlContent .= "\t<user firstname=\"" . $firstname . "\" lastname=\"" . $lastname . "\" login=\"" . $login . "\" password=\"" . $password . "\" state=\"" . $creationState . "\" />\n";
				
				// Add new user to course, if a course was selected
				if ($creationState == "ok" && $request->getParameter("courseID") != "0") {
					
					if ($remainingLicenses > 0) {
						$elearning_course_id = elearning_mediathek::get_elearning_unit_id($courseID);
	    				$customer_name = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getObjectName($customerID);
						$userID = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->login2ID($login);
						licensemanager::get_instance()->register_user($userID, array(new elearning_context($elearning_course_id), new unternehmens_context($customer_name)));
						$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->addParticipant($userID, $courseID);
						$remainingLicenses--;
					}
					
				}
			}
			
			$rowIterator->next();
		}
		
		$xmlContent .= "</users>";
		
		$xmlFileName = time() . ".xml";
		
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->saveCustomerCreationLogFile($_SESSION["CURRENT_CUSTOMER_ID"], $xmlFileName, $xmlContent);

		
		// Throw exception, if to less licenses were available
		if ($request->getParameter("courseID") != "0" && $remainingLicensesBak < $newUserCounter) {
			throw new UsermanagementException("Aufgrund zu weniger freier Lizenzen konnten nicht alle angelegten Benutzer dem Kurs hinzugef&uuml;gt werden.", "Erweitern sie ihr Lizenz-Kontingent.");
		}
		
		return "Excel-Liste wurde importiert. Siehe unter 'Verlauf' f&uuml;r Einzelheiten der angelegten Benutzer.";
		
	}
	
	
	
	// Encodes a string to utf-8 format
	private function fixEncoding($string){ 
		if (mb_detect_encoding($string)=='UTF-8'){ 
			return $string; 
		} 
		else { 
			return utf8_encode($string); 
		} 
	} 
}
?>
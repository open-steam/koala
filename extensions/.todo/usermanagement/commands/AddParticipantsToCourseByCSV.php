<?php

class AddParticipantsToCourseByCSV implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$selectedCourses = $request->getParameter("select");
		
		$filePath = $_FILES["csvFile"]["tmp_name"];
		
		if (!is_array($selectedCourses) || count($selectedCourses) == 0) {
			throw new UsermanagementException("Keinen Kurs ausgew&auml;hlt.", "");
		}
		
		else if ($filePath == "") {
			throw new UsermanagementException("Keine CSV-Datei ausgew&auml;hlt.", "");
		}
		
		else {
			
			$file = file($filePath);
			
			$errorMsg = "";
			
			foreach ($selectedCourses as $courseID => $value) {
				
				$numberOfLicenses 	= (int) $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCountCourseLicenses($courseID);
				$participants 	  	= $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseParticipants($courseID);
				$remainingLicenses  = $numberOfLicenses - count($participants);
				$courseData			= $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseData($courseID);
				
				if ($remainingLicenses < count($file)) {
					$errorMsg .= (($errorMsg == "") ? "" : "<br>") . "Nicht mehr ausreichend freie Lizenzen f&uuml;r den Kurs \"" . $courseData["name"] . "\" vorhanden.";
				}
				
				else {
					
					foreach ($file as $line) {
						$login = str_replace("\"", "", $line);
						$login = rtrim($login);
						$login = ltrim($login);

						$userID = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->login2ID($login);
						
						if ($userID != "-1") {
							$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->addParticipant($userID, $courseID);
						}
						
						else {
							$errorMsg .= (($errorMsg == "") ? "" : "<br>") . "Der Mitarbeiter \"" . $login . "\" konnte dem Kurs \"" . $courseData["name"] . "\" nicht hinzugef&uuml;gt werden, da dieser Benutzer nicht existiert.";
						}
					}
					
				}
				
			}
			
			if ($errorMsg != "") {
				throw new UsermanagementException($errorMsg, "");
			}
			
			else {
				return "Alle Mitarbeiter aus der CSV-Liste wurden den selektierten Kursen hinzugef&uuml;gt.";
			}
		}

		
	}
	
}

?>
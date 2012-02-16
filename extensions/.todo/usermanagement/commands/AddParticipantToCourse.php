<?php

class AddParticipantToCourse implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$userIDs = $request->getParameter("userIDs");
		$courseID = $request->getParameter("courseID");
		
		$numberOfLicenses 	= (int) $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCountCourseLicenses($courseID);
		$participants 	  	= $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseParticipants($courseID);
		$remainingLicenses  = $numberOfLicenses - count($participants);
		
		if ($remainingLicenses == 0) {
			throw new UsermanagementException("Keine verf&uuml;gbare Lizenzen f&uuml;r diesen Kurs vorhanden", "Entfernen Sie andere Teilnehmer aus dem Kurs oder erweitern Sie ihr Lizenz-Kontingent.");
		}
		
		foreach ($userIDs as $userID) {
			
			if ($remainingLicenses == 0) {
				throw new UsermanagementException("Nicht alle markierten Teilnehmer konnten dem Kurs hinzugef&uuml;gt werden.", "Entfernen Sie andere Teilnehmer aus dem Kurs oder erweitern Sie ihr Lizenz-Kontingent.");
			}
			
			else {
				$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->addParticipant($userID, $courseID);
			}
			
			$remainingLicenses--;
			
		}
		
		return "Alle markierten Mitarbeiter wurden dem Kurs hinzugef&uuml;gt";
		
	}
}

?>
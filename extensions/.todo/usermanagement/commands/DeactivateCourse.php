<?php

class DeactivateCourse implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$deleteKeys  		= array_keys($request->getParameter("delete"));
		$customerIDs 		= $request->getParameter("customerID");
		
		$courseID 			= $deleteKeys[0];
		$customerID 		= $customerIDs[$courseID];

		echo "course id: " . $courseID . "<br>";
		echo "customer id: " . $customerID . "<br>";
		
		// Remove all participants from course
		$participants = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseParticipants($courseID);
		
		foreach ($participants as $participantID => $participantName) {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeParticipant($participantID, $courseID);
		}
		
		// Deactivate course for customer
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->deactivateCourseForCustomer($courseID, $customerID);
		
		return "Kurs f&uuml;r Kunden gel&ouml;scht.";
		
	}
	
}

?>
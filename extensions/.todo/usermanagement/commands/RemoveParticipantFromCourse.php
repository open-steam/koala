<?php

class RemoveParticipantFromCourse implements Command {
	
	public function execute (Request $request, Response $response) {
		

		$userIDs = array_keys($request->getParameter("userID"));
		$userID = $userIDs[0];
		
		$courseID = $request->getParameter("courseID");

		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeParticipant($userID, $courseID);
		
		return "Benutzer aus dem Kurs entfernt";
		
	}
	
}

?>
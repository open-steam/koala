<?php

class GetParticipants implements Command {

	public function execute (Request $request, Response $response) {
		
		$courseID = $request->getParameter("courseID");

		// Result data for AJAX response
		$result = array("command" => "getParticipants", "participants" => array());
		
		if ($courseID != "noCourses" && $courseID != "all") {
		
			$participants = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseParticipants($courseID);
	
			$result["state"] = "ok";
			
			foreach ($participants as $id => $login) {
				$result["participants"][] = $id;
			}
		
		}
		
		else if ($courseID == "noCourses") {
			
			$currentCustomerID = $_SESSION["CURRENT_CUSTOMER_ID"];
			
			$users = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllEmployees($currentCustomerID);
			
			$courseIDs = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllCourseIDs();
			
			foreach ($courseIDs as $courseID) {
				
				$participants = $GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getCourseParticipants($courseID);
				
				foreach ($participants as $id => $login) {
					$users[$id] = false;
				}
				
			}
			
			foreach ($users as $id => $login) {
				if ($login != false) {
					$result["participants"][] = $id;
				}
			}
			
			$result["state"] = "ok";
			
		}
			
		return $result;


	}
	
}

?>
<?php

class DeleteEmployeeAJAX implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$userID = $request->getParameter("userID");
		
		$result = array("id" => $request->getParameter("senderID"), "command" => "deleteEmployeeAJAX");
		
		// Remove employee from branch before removing it
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeUserFromAllGroups($userID);
		
		// Delete user
		if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeUser($userID)) {
			$result["state"] = "ok";
		}
		
		else {
			$result["state"] = "fail"; 
		}
		
		return $result;
		
	}
	
}

?>
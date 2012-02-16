<?php

class DeleteEmployee implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$employeeID = $request->getParameter("employeeID");
		
		// Remove employee from branch before removing it
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeUserFromAllGroups($employeeID);
		
		// Delete user
		if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeUser($employeeID)) {
			return "Der Mitarbeiter wurde gel&ouml;scht";
		}
		
		else {
			throw new UsermanagementException("Der Mitarbeiter konnte nicht gel&ouml;scht werden", ""); 
		}
		
	}
	
}

?>
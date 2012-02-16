<?php

class DeleteCustomer implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$deleteKeys  = array_keys($request->getParameter("delete"));		
		$customerID = $deleteKeys[0];
		
		
		// Remove all employees from branch
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeAllEmployeesFromCustomer($customerID);
		
		// Delete branch
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->deleteCustomer($customerID);

		return "Der Kunde wurde gel&ouml;scht.";
		
	}
	
}

?>
<?php

class DeleteBranch implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$deleteKeys  = array_keys($request->getParameter("delete"));		
		$branchID = $deleteKeys[0];
		
		// Remove all employees from branch
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeAllEmployeesFromBranch($branchID);
		
		// Delete branch
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->deleteBranch($branchID);
		
		return "Die Filiale wurde gel&ouml;scht";

		
	}
	
}

?>
<?php

class ModifyEmployee implements Command {
	
	public function execute (Request $request, Response $response) {

		$userID	= $request->getParameter("userID");
		
		$result = array("id" => $request->getParameter("senderID"), "command" => "modifyEmployee");

		// Set new firstname
		if ($request->issetParameter("firstname") ) { //&& $request->getParameter("firstname") != "") {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setUserFirstName($userID, $request->getParameter("firstname"));
			$result["value"] = $request->getParameter("firstname");
		}
		
		// Set new lastname
		if ($request->issetParameter("lastname") ) { //&& $request->getParameter("lastname") != "") {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setUserLastName($userID, $request->getParameter("lastname"));
			$result["value"] = $request->getParameter("lastname");
		}
		
		// Set new email
		if ($request->issetParameter("email") ) { //&& $request->getParameter("email") != "") {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setUserEmail($userID, $request->getParameter("email"));
			$result["value"] = $request->getParameter("email");
		}
		
		
		/*
		// Add to new branch and remove from old branch
		if ($branchID != $branchIDOld) {
			
			$this->removeEmployeeFromAllAdminGroups($employeeID);
			
			if ($branchIDOld != "0" && $branchIDOld != "") {
				$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeEmployeeFromBranch($branchIDOld, $employeeID);
			}			
			
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->addEmployeeToBranch($branchID, $employeeID);

		}
		*/
		
		// Set new role for user
		if ($request->issetParameter("role") && $request->getParameter("role") != "") {
			
			// first remove old rights
			$this->removeEmployeeFromAllAdminGroups($userID);

			// Set new rights
			switch ((int) $request->getParameter("role")) {
				case 1 : 
					#$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setBranchAdminRights($userID);
					break;
				case 2 : 
					$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setCustomerAdminRights($userID);
					break;
				case 3 : 
					$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setAdminRights($userID);
					break;
			}

		}
		
		$result["state"] = "ok";
		return $result;
				
	}
	
	
	
	// Removes an employee from all admin groups
	private function removeEmployeeFromAllAdminGroups ($employeeID) {
		
		if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isAdmin($employeeID)) {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeAdminRights($employeeID);	
		}
		if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isCustomerAdmin($employeeID)) {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeCustomerAdminRights($employeeID);	
		}
		if ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->isBranchAdmin($employeeID)) {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->removeBranchAdminRights($employeeID);	
		}
		
	} 
	
}

?>
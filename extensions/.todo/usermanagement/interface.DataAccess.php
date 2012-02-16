<?php
	// Interface for accessing data
	
	interface DataAccess{
		/*
		// Creates a new employee
		public function createEmployee ();
		
		// Deletes an employee
		public function deleteEmployee ($employeeID);
		
		// Disables an employee
		public function disableEmployee ($employeeID);
		
		// Enables an employee
		public function enableEmployee ($employeeID);
		
		// Changes the name of an employee
		public function changeEmployeeName ($employeeID, $firstname, $lastname);
		
		// Changes the branch of an employee
		public function changeBranch ($employeeID, $branchID);
		
		// Assigns a course to an employee
		public function addEmployeeToCourse ($employeeID, $courseID);
		
		// Disable an exam
		public function disableExam ($examID);
		
		// Enables an exam
		public function enableExam ($examID);
		
		
		// _______________________________________________________________
		//														 branches
		*/
		public function createBranch ($name);
		/*
		public function deleteBranch ($branchID);
		
		public function renameBranch ($branchID, $name);
		
		public function setBranchAdmin ($branchID, $employeeID);
		
		// _______________________________________________________________
		//

		
		
		
		// _______________________________________________________________
		//														 customer
		
		public function createCustomer ($firstname, $lastname);
		
		public function deleteCustomer ($customerID);
		
		// _______________________________________________________________
		//		
		*/
		
		
		
		// _______________________________________________________________
		//														 	 user
		
		public function changePassword ($userID, $password);
		
		// _______________________________________________________________
		//	
	}
?>
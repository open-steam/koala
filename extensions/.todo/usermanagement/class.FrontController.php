<?php 

class FrontController_old {
	
	private $portal;
	
	
	// Constructor
	public function __construct ($portal) {
		$this->portal = $portal;
	}
	
	
	
	// Handles a user request
	public function handleRequest (Request $request, Response $response) {
				
		//$this->printParams($request);

		// Execute command
		switch ($request->getParameter("command")) {
			case "changePassword" :
				$command = new ChangePassword(); 
				$command->setPortal($this->portal);
				break;
			case "resetPassword" :
				$command = new ResetPassword(); 
				break;
			case "lockUnlockUser" :
				$command = new LockUnlockUser(); 
				break;
			case "trashRestoreUser" :
				$command = new TrashRestoreUser(); 
				break;
			case "createEmployee" :
				$command = new CreateEmployee(); 
				break;
			case "modifyEmployee" :
				$command = new ModifyEmployee(); 
				break;
			case "deleteEmployee" :
				$command = new DeleteEmployee(); 
				break;
			case "deleteEmployeeAJAX" :
				$command = new DeleteEmployeeAJAX(); 
				break;
			case "deleteMultipleEmployees" :
				$command = new DeleteMultipleEmployees(); 
				break;
			case "importCSVFile" :
				$command = new ImportExcelFile(); 
				break;
			case "deleteHistory" :
				$command = new DeleteHistory(); 
				break;
			case "exportEmployees" :
				$command = new ExportEmployees();
				break;
			case "createBranch" :
				$command = new CreateBranch(); 
				break;
			case "modifyBranch" :
				$command = new ModifyBranch(); 
				break;
			case "deleteBranch" :
				$command = new DeleteBranch(); 
				break;
			case "createCustomer" :
				$command = new CreateCustomer(); 
				break;
			case "modifyCustomer" :
				$command = new ModifyCustomer(); 
				break;
			case "deleteCustomer" :
				$command = new DeleteCustomer(); 
				break;
			case "assignEmployeeToCourse" :
				$command = new AddParticipantToCourse(); 
				break;
			case "assignEmployeesToCourseByCSV" :
				$command = new AddParticipantsToCourseByCSV(); 
				break;
			case "removeEmployeeFromCourse" :
				$command = new RemoveParticipantFromCourse(); 
				break;
			case "activateCourse" :
				$command = new ActivateCourse(); 
				break;
			case "deactivateCourse" :
				$command = new DeactivateCourse(); 
				break;
			case "changeCourseQuota" :
				$command = new ChangeCourseQuota(); 
				break;
			case "changeAdminPerspective" :
				$command = new ChangeAdminPerspective(); 
				break;
			case "getParticipants" :
				$command = new GetParticipants(); 
				break;
			case "showCourseDialog" :
				$command = new ShowCourseDialog();
				break;
			case "generateLicense" :
				$command = new GenerateLicense();
				break;
			case "getEncryptKey" :
				$command = new GetEncryptKey();
				break;
			case "setEncryptKey" :
				$command = new SetEncryptKey();
				break;
			case "installLicense" :
				$command = new InstallLicense();
				break;
			case "createCourse" :
				$command = new CreateCourse();
				break;
			case "toggleCustomerAdmin" :
				$command = new ToggleCustomerAdmin();
				break;
			case "toggleSystemAdmin" :
				$command = new ToggleSystemAdmin();
				break;
			case "changeCourseRole" :
				$command = new ChangeCourseRole();
				break;
			default :
				$command = new Dummy();
		}
		
		try {
			
			$result = $command->execute($request, $response);
			
			if (is_array($result)) {
				return json_encode($result);
			}
			
			else {
				$this->portal->set_confirmation($result);
			}

		}
		
		catch (UsermanagementException $exception) {
			$this->portal->set_problem_description($exception->getProblem(), $exception->getHint());
			
		}
	}
	
	private function printParams ($request) {
		echo "Found Parameters: </br>";	
		foreach ($request->getParameterNames() as $key) {
			if (is_array($request->getParameter($key)))
				echo $key . " -> " . print_r($request->getParameter($key)) . "</br>";
			else
				echo $key . " -> " . $request->getParameter($key) . "</br>";
		}	
	}
	
}

?>
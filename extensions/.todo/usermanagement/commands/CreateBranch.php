<?php

class CreateBranch implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$name = $request->getParameter("branchName");
		$customerID = $request->getParameter("customerID");
		
		if ($name == "") {
			throw new UsermanagementException("Keinen Filialnamen angegeben", "Bitte gebe einen Namen ein.");
		}
		
		else {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->createBranch($name, $customerID);
		}
		
		return "Filiale \"" . $name . "\" wurde angelegt";
	}
	
}

?>
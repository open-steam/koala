<?php

class ModifyBranch implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$saveKeys  = array_keys($request->getParameter("save"));
		$valueKeys = $request->getParameter("branchName");
		
		$branchID = $saveKeys[0];
		$branchName = $valueKeys[$branchID];

		// Set new branch name
		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->setBranchName($branchID, $branchName);

		
		return "Die &Auml;nderungen wurden gesepichert";
				
	}
	
}

?>
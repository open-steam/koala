<?php

class ModifyCustomer implements Command {
	
	public function execute (Request $request, Response $response) {
		
		// New values
		$saveKeys  = array_keys($request->getParameter("save"));
		$valueKeys = $request->getParameter("customerName");
		
		$customerID = $saveKeys[0];
		$customerName = $valueKeys[$customerID];

		// Set new firstname
		if (true) {
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->renameCustomer($customerID, $customerName);
		}

		return "Die &Auml;nderungen wurden gesepichert";
				
	}
	
}

?>
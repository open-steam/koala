<?php

class ChangeAdminPerspective implements Command {
	
	
	public function execute (Request $request, Response $response) {
		
		// ID of the new customer perspective
		$newCustomerID = $request->getParameter("customerID");
		
		
		
		foreach ($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getAllCustomers() as $customerID => $customerName) {
			
			if ($customerID == $newCustomerID) {
				
				$_SESSION["CURRENT_CUSTOMER_ID"] = $customerID;
				$_SESSION["CURRENT_CUSTOMER_NAME"] = $customerName;
				
				return "Neue Kunden-Perspektive wurde gesetzt";
				
			}
			
		}
		
		throw new UsermanagementException("Kunden-Perspektive konnte nicht neu gesetzt werden", "");
		
	}
	
}

?>
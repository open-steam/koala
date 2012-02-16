<?php

class CreateCustomer implements Command {
	
	
	public function execute (Request $request, Response $response) {
		
		$name = $request->getParameter("name");
		$id = $request->getParameter("id");
		
		if ($name == "") {
			throw new UsermanagementException("Keinen Namen angegeben", "Bitte gebe einen Namen ein.");
		}
		if ($id == "") {
			throw new UsermanagementException("Keinen ID angegeben", "Bitte gebe eine ID ein.");
		}
		
		// TODO: Check if customer name already exists
		else if (false) {
			// 
		}
		
		// If values are correct
		else {
			
			// Create new customer
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->createCustomer($name, $id);
			
			return "Neues Unternehmen wurde angelegt";
		}
	}
}

?>
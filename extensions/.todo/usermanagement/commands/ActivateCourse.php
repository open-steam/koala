<?php

class ActivateCourse implements Command {
	
	public function execute (Request $request, Response $response) {
		
		$activateKeys  		= array_keys($request->getParameter("activate"));
		$customerIDs 		= $request->getParameter("customerID");
		$numbersOfLicenses 	= $request->getParameter("numberOfLicenses");
		
		$courseID 			= $activateKeys[0];
		$customerID 		= $customerIDs[$courseID];
		$numberOfLicenses 	= (int) $numbersOfLicenses[$courseID];
		
		if ($numberOfLicenses == "") {
			throw new UsermanagementException ("Keine Anzahl f&uuml;r Lizenzen angegeben", "");
		}
		
		else {
			
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->activateCourseForCustomer($courseID, $customerID, $numberOfLicenses);
			
			return "Kurs f&uuml;r Kunden freigeschaltet.";
			
		}
		
	}
	
}

?>
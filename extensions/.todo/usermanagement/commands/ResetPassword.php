<?php

class ResetPassword implements Command {

	public function execute (Request $request, Response $response) {
		
		$userID = $request->getParameter("userID");
		
		$helper = new CommandHelper();
		
		$password = $helper->createRandomPassword(8);
		
		// Result data for AJAX response
		$result = array("id" => $request->getParameter("senderID"), "command" => "resetPassword");
		

		$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->changePassword($userID, $password);

		$result["state"] = "ok";
		$result["password"] = $password;
			
		return $result;


	}
	
}

?>
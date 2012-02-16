<?php

class LockUnlockUser implements Command {
	
	public function execute (Request $request, Response $response) {
		
		// The user to (un)lock
		$userID = $request->getParameter("userID");
		
		// Check if to lock or unlock
		$lockState = $request->getParameter("lockState");
		
		$result = array("id" => $request->getParameter("senderID"), "command" => "lockUnlockUser");
		
		// Lock user
		if ($lockState == "lock") {

			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->lockUser($userID);

			
		}
		
		// Unlock user
		else {
				
			$GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->unlockUser($userID);
			
			
			
		}
		
		$result["status"] = usermanangement::get_instance()->get_user_status_html($GLOBALS["USERMANAGEMENT_DATA_ACCESS"]->getUserLogin($userID));
		$result["state"] = "ok";
		
		return $result;
		
	}
	
}

?>